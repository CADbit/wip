<?php

declare(strict_types=1);

namespace App\Controller;

use App\Message\MessageTest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

final class TestMessageController extends AbstractController
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    #[Route('/test/message', name: 'app_test_message')]
    public function index(): JsonResponse
    {
        $this->bus->dispatch(
            new MessageTest('Hello from controller!')
        );

        return new JsonResponse('Message dispatched!');
    }
}
