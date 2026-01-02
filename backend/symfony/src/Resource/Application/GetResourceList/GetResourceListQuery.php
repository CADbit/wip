<?php

declare(strict_types=1);

namespace App\Resource\Application\GetResourceList;

use App\Resource\Domain\Enum\ResourceStatus;
use App\Resource\Domain\Enum\ResourceType;

class GetResourceListQuery
{
    public function __construct(
        public ?ResourceType $type = null,
        public ?ResourceStatus $status = null,
    ) {
    }
}
