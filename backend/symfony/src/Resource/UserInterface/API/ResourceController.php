<?php

declare(strict_types=1);

namespace App\Resource\UserInterface\API;

use App\Resource\Application\CreateResource\CreateResourceCommand;
use App\Resource\Application\DeleteResource\DeleteResourceCommand;
use App\Resource\Application\UpdateResource\UpdateResourceCommand;
use App\Resource\Application\GetActiveConferenceRooms\GetActiveConferenceRoomsQuery;
use App\Resource\Application\GetActiveConferenceRooms\GetActiveConferenceRoomsQueryHandler;
use App\Resource\Application\GetResource\GetResourceQuery;
use App\Resource\Application\GetResource\GetResourceQueryHandler;
use App\Resource\Application\GetResourceList\GetResourceListQuery;
use App\Resource\Application\GetResourceList\GetResourceListQueryHandler;
use App\Resource\Domain\Entity\Resource;
use App\Resource\Domain\Enum\ResourceStatus;
use App\Resource\Domain\Enum\ResourceType;
use App\Resource\Domain\Enum\ResourceUnavailability;
use App\UserInterface\API\ApiResponseHelper;
use App\UserInterface\API\RequestValidator;
use InvalidArgumentException;
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
        private readonly GetResourceListQueryHandler $getResourceListQueryHandler,
        private readonly GetResourceQueryHandler $getResourceQueryHandler,
        private readonly GetActiveConferenceRoomsQueryHandler $getActiveConferenceRoomsQueryHandler,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $type = $request->query->get('type');
        $status = $request->query->get('status');

        $resourceType = null;
        $resourceStatus = null;

        if ($type) {
            try {
                $resourceType = ResourceType::from($type);
            } catch (ValueError $e) {
                return ApiResponseHelper::validationError('Nieprawidłowy typ zasobu', [
                    'type' => 'Nieprawidłowy typ zasobu'
                ]);
            }
        }

        if ($status) {
            try {
                $resourceStatus = ResourceStatus::from($status);
            } catch (ValueError $e) {
                return ApiResponseHelper::validationError('Nieprawidłowy status zasobu', [
                    'status' => 'Nieprawidłowy status zasobu'
                ]);
            }
        }

        $query = new GetResourceListQuery($resourceType, $resourceStatus);
        $resources = ($this->getResourceListQueryHandler)($query);

        return ApiResponseHelper::success(
            array_map(fn(Resource $resource) => $this->serializeResource($resource), $resources)
        );
    }

    #[Route('/conference-rooms', name: 'conference_rooms', methods: ['GET'])]
    public function getActiveConferenceRooms(): JsonResponse
    {
        $query = new GetActiveConferenceRoomsQuery();
        $resources = ($this->getActiveConferenceRoomsQueryHandler)($query);

        return ApiResponseHelper::success(
            array_map(fn(Resource $resource) => $this->serializeResource($resource), $resources)
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        try {
            $uuid = Uuid::fromString($id);
        } catch (InvalidArgumentException $e) {
            return ApiResponseHelper::validationError('Nieprawidłowy format UUID', [
                'id' => 'Nieprawidłowy format UUID'
            ]);
        }

        $query = new GetResourceQuery($uuid);
        $resource = ($this->getResourceQueryHandler)($query);

        if (!$resource) {
            return ApiResponseHelper::error('Zasób nie został znaleziony', [], Response::HTTP_NOT_FOUND);
        }

        return ApiResponseHelper::success($this->serializeResource($resource));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = RequestValidator::parseJsonRequest($request);

        if (!$data) {
            return ApiResponseHelper::error('Nieprawidłowy format JSON', [], Response::HTTP_BAD_REQUEST);
        }

        // Walidacja wymaganych pól
        $requiredFields = ['type', 'name', 'status'];
        $missingFields = RequestValidator::validateRequiredFields($data, $requiredFields);

        if ($missingFields) {
            return ApiResponseHelper::validationError('Brakuje wymaganych pól', $missingFields);
        }

        try {
            $type = ResourceType::from($data['type']);
            $status = ResourceStatus::from($data['status']);
            $unavailability = isset($data['unavailability']) && $data['unavailability'] !== null
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
        } catch (InvalidArgumentException $e) {
            return ApiResponseHelper::validationError('Nieprawidłowy format UUID', [
                'id' => 'Nieprawidłowy format UUID'
            ]);
        }

        // Sprawdzenie czy zasób istnieje
        $query = new GetResourceQuery($uuid);
        $resource = ($this->getResourceQueryHandler)($query);

        if (!$resource) {
            return ApiResponseHelper::error('Zasób nie został znaleziony', [], Response::HTTP_NOT_FOUND);
        }

        $data = RequestValidator::parseJsonRequest($request);

        if (!$data) {
            return ApiResponseHelper::error('Nieprawidłowy format JSON', [], Response::HTTP_BAD_REQUEST);
        }

        $errors = [];
        $status = null;
        $unavailability = null;

        if (isset($data['status'])) {
            try {
                $status = ResourceStatus::from($data['status']);
            } catch (ValueError $e) {
                $errors['status'] = 'Nieprawidłowy status zasobu';
            }
        }

        if (isset($data['unavailability'])) {
            try {
                $unavailability = $data['unavailability'] !== null
                    ? ResourceUnavailability::from($data['unavailability'])
                    : null;
            } catch (ValueError $e) {
                $errors['unavailability'] = 'Nieprawidłowa wartość niedostępności';
            }
        }

        if (!empty($errors)) {
            return ApiResponseHelper::validationError('Błędy walidacji', $errors);
        }

        $command = new UpdateResourceCommand(
            id: $uuid,
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            status: $status,
            unavailability: $unavailability
        );

        try {
            $this->messageBus->dispatch($command);
        } catch (DomainException $e) {
            return ApiResponseHelper::error($e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }

        // Pobranie zaktualizowanego zasobu
        $updatedResource = ($this->getResourceQueryHandler)($query);
        return ApiResponseHelper::success(
            $this->serializeResource($updatedResource),
            'Zasób został zaktualizowany pomyślnie'
        );
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

        $command = new DeleteResourceCommand($uuid);

        try {
            $this->messageBus->dispatch($command);
        } catch (\DomainException $e) {
            return ApiResponseHelper::error($e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }

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

