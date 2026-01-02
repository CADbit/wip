<?php

declare(strict_types=1);

namespace App\Reservation\Domain\Entity\Traits;

trait DomainEventsTrait
{
    /**
     * @var object[]
     */
    private array $domainEvents = [];

    /**
     * @return object[]
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    protected function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }
}
