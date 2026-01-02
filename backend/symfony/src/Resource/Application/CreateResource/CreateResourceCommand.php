<?php

declare(strict_types=1);

namespace App\Resource\Application\CreateResource;

use App\Resource\Domain\Enum\ResourceStatus;
use App\Resource\Domain\Enum\ResourceType;
use App\Resource\Domain\Enum\ResourceUnavailability;
use Symfony\Component\Uid\Uuid;

class CreateResourceCommand
{
    public function __construct(
        public Uuid $id,
        public ResourceType $type,
        public string $name,
        public ResourceStatus $status,
        public ?ResourceUnavailability $unavailability,
        public ?string $description = null,
    ) { }
}
