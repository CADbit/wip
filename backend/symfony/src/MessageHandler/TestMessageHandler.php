<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\MessageTest;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TestMessageHandler
{
    public function __invoke(MessageTest $message): void
    {
        dump('Message consumed: ' . $message->content);
    }
}
