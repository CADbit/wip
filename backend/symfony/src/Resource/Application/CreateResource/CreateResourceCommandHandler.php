<?php

declare(strict_types=1);

namespace App\Resource\Application\CreateResource;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateResourceCommandHandler
{
    public function __invoke(CreateResourceCommand $command): void
    {

    }
}
