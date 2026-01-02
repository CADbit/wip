<?php

declare(strict_types=1);

namespace App\Resource\Application\UpdateResource;

use App\Resource\Domain\Enum\ResourceStatus;
use App\Resource\Domain\Enum\ResourceUnavailability;
use Symfony\Component\Uid\Uuid;

class UpdateResourceCommand
{
    public function __construct(
        public Uuid $id,
        public ?string $name = null,
        public ?string $description = null,
        public ?ResourceStatus $status = null,
        public ?ResourceUnavailability $unavailability = null,
    ) {
    }
}

