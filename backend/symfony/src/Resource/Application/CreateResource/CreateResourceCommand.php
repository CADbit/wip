<?php

declare(strict_types=1);

namespace App\Resource\Application\CreateResource;

use App\Resource\Domain\Enum\ResourceStatus;
use App\Resource\Domain\Enum\ResourceType;
use App\Resource\Domain\Enum\ResourceUnavailability;
use Cassandra\Uuid;

class CreateResourceCommand
{
    public function __construct(
        public UUid $id,
        public ResourceType $type,
        public string $name,
        public ?string $description = null,
        public ResourceStatus $status,
        public ?ResourceUnavailability $unavailability,
    ) { }
}
