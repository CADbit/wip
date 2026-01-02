<?php

declare(strict_types=1);

namespace App\Resource\UserInterface\API;

use App\Resource\Application\CreateResource\CreateResourceCommand;
use App\Resource\Domain\Entity\Resource;
use App\Resource\Domain\Enum\ResourceStatus;
use App\Resource\Domain\Enum\ResourceType;
use App\Resource\Domain\Enum\ResourceUnavailability;
use App\Resource\Domain\Repository\ResourceRepositoryInterface;
use App\UserInterface\API\ApiResponseHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use ValueError;

#[Route('/api/resources', name: 'api_resources_')]
class ResourceController extends AbstractController
{
    public function __construct(
        private readonly ResourceRepositoryInterface $resourceRepository,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $type = $request->query->get('type');
        $status = $request->query->get('status');

        if ($type && $status === ResourceStatus::ACTIVE->value) {
            try {
                $resourceType = ResourceType::from($type);
                $resources = $this->resourceRepository->findAllActiveByType($resourceType);
            } catch (ValueError $e) {
                return ApiResponseHelper::validationError('Nieprawidłowy typ zasobu', [
                    'type' => 'Nieprawidłowy typ zasobu'
                ]);
            }
        } elseif ($status === ResourceStatus::ACTIVE->value) {
            $resources = $this->resourceRepository->findAllActive();
        } else {
            $resources = $this->resourceRepository->findAll();
        }

        return ApiResponseHelper::success(
            array_map(fn(Resource $resource) => $this->serializeResource($resource), $resources)
        );
    }

    #[Route('/conference-rooms', name: 'conference_rooms', methods: ['GET'])]
    public function getActiveConferenceRooms(): JsonResponse
    {
        $resources = $this->resourceRepository->findAllActiveByType(ResourceType::CONFERENCE_ROOM);

        return ApiResponseHelper::success(
            array_map(fn(Resource $resource) => $this->serializeResource($resource), $resources)
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        try {
            $uuid = Uuid::fromString($id);
        } catch (\InvalidArgumentException $e) {
            return ApiResponseHelper::validationError('Nieprawidłowy format UUID', [
                'id' => 'Nieprawidłowy format UUID'
            ]);
        }

        $resource = $this->resourceRepository->findById($uuid->toString());

        if (!$resource) {
            return ApiResponseHelper::error('Zasób nie został znaleziony', [], Response::HTTP_NOT_FOUND);
        }

        return ApiResponseHelper::success($this->serializeResource($resource));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return ApiResponseHelper::error('Nieprawidłowy format JSON', [], Response::HTTP_BAD_REQUEST);
        }

        // Walidacja wymaganych pól
        $requiredFields = ['type', 'name', 'status'];
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $missingFields[$field] = "Pole '$field' jest wymagane";
            }
        }
        
        if (!empty($missingFields)) {
            return ApiResponseHelper::validationError('Brakuje wymaganych pól', $missingFields);
        }

        try {
            $type = ResourceType::from($data['type']);
            $status = ResourceStatus::from($data['status']);
            $unavailability = isset($data['unavailability'])
                ? ResourceUnavailability::from($data['unavailability'])
                : null;
        } catch (ValueError $e) {
            return ApiResponseHelper::validationError('Nieprawidłowa wartość enum', [
                'type' => 'Nieprawidłowy typ zasobu',
                'status' => 'Nieprawidłowy status zasobu',
                'unavailability' => 'Nieprawidłowa wartość niedostępności'
            ]);
        }

        $command = new CreateResourceCommand(
            id: Uuid::v4(),
            type: $type,
            name: $data['name'],
            description: $data['description'] ?? null,
            status: $status,
            unavailability: $unavailability
        );

        $this->messageBus->dispatch($command);

        return ApiResponseHelper::success([
            'id' => $command->id->toString(),
            'type' => $command->type->value,
            'name' => $command->name,
        ], 'Zasób został utworzony pomyślnie', Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(string $id, Request $request): JsonResponse
    {
        try {
            $uuid = Uuid::fromString($id);
        } catch (\InvalidArgumentException $e) {
            return ApiResponseHelper::validationError('Nieprawidłowy format UUID', [
                'id' => 'Nieprawidłowy format UUID'
            ]);
        }

        $resource = $this->resourceRepository->findById($uuid->toString());

        if (!$resource) {
            return ApiResponseHelper::error('Zasób nie został znaleziony', [], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return ApiResponseHelper::error('Nieprawidłowy format JSON', [], Response::HTTP_BAD_REQUEST);
        }

        $errors = [];

        // Aktualizacja pól
        if (isset($data['name'])) {
            $resource->name = $data['name'];
        }

        if (isset($data['description'])) {
            $resource->description = $data['description'];
        }

        if (isset($data['status'])) {
            try {
                $resource->status = ResourceStatus::from($data['status']);
            } catch (ValueError $e) {
                $errors['status'] = 'Nieprawidłowy status zasobu';
            }
        }

        if (isset($data['unavailability'])) {
            try {
                $resource->unavailability = $data['unavailability'] !== null
                    ? ResourceUnavailability::from($data['unavailability'])
                    : null;
            } catch (ValueError $e) {
                $errors['unavailability'] = 'Nieprawidłowa wartość niedostępności';
            }
        }

        if (!empty($errors)) {
            return ApiResponseHelper::validationError('Błędy walidacji', $errors);
        }

        $this->resourceRepository->save($resource);
        $this->resourceRepository->flush();

        return ApiResponseHelper::success($this->serializeResource($resource), 'Zasób został zaktualizowany pomyślnie');
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        try {
            $uuid = Uuid::fromString($id);
        } catch (\InvalidArgumentException $e) {
            return ApiResponseHelper::validationError('Nieprawidłowy format UUID', [
                'id' => 'Nieprawidłowy format UUID'
            ]);
        }

        $resource = $this->resourceRepository->findById($uuid->toString());

        if (!$resource) {
            return ApiResponseHelper::error('Zasób nie został znaleziony', [], Response::HTTP_NOT_FOUND);
        }

        $this->resourceRepository->remove($resource);
        $this->resourceRepository->flush();

        return ApiResponseHelper::success(null, 'Zasób został usunięty pomyślnie');
    }

    private function serializeResource(Resource $resource): array
    {
        return [
            'id' => $resource->id->toString(),
            'type' => $resource->type->value,
            'name' => $resource->name,
            'description' => $resource->description,
            'status' => $resource->status->value,
            'unavailability' => $resource->unavailability?->value,
            'createdAt' => $resource->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}

