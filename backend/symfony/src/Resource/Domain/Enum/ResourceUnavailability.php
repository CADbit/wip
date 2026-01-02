<?php

declare(strict_types=1);

namespace App\Resource\Domain\Enum;

enum ResourceUnavailability: string
{
    case MAINTENANCE = 'maintenance';
    case ADMIN_BLOCK = 'admin_block';
}
