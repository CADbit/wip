<?php

declare(strict_types=1);

namespace App\Resource\Domain\Enum;

enum ResourceStatus: string
{
    case ACTIVE = 'active';
    case DISABLED = 'disabled';
}
