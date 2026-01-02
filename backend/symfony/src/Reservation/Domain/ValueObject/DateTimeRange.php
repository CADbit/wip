<?php

declare(strict_types=1);

namespace App\Reservation\Domain\ValueObject;

use DateTimeImmutable;
use Exception;

class DateTimeRange
{
    private DateTimeImmutable $start;
    private DateTimeImmutable $end;

    public function __construct(
        DateTimeImmutable $start,
        DateTimeImmutable $end
    ) {
        if ($end <= $start) {
            throw new Exception();
        }

        $this->start = $start;
        $this->end = $end;
    }

    public function start(): DateTimeImmutable
    {
        return $this->start;
    }

    public function end(): DateTimeImmutable
    {
        return $this->end;
    }

    public function overlaps(self $other): bool
    {
        return $this->start < $other->end
            && $this->end > $other->start;
    }
}
