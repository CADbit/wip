<?php

declare(strict_types=1);

namespace App\Resource\Domain\Enum;

enum ResourceType: string
{
    case CONFERENCE_ROOM = 'conference_room';
    case CAR = 'car';
    case PROJECTOR = 'projector';
}
