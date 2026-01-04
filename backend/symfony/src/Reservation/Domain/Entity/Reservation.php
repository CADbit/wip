<?php

declare(strict_types=1);

namespace App\Reservation\Domain\Entity;

use App\Reservation\Domain\Entity\Traits\DomainEventsTrait;
use App\Reservation\Domain\Event\ReservationCreated;
use App\Reservation\Domain\ValueObject\DateTimeRange;
use App\Reservation\Infrastructure\Doctrine\ReservationRepository;
use App\Resource\Domain\Entity\Resource;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Encja domenowa reprezentująca rezerwację zasobu.
 *
 * Rezerwacja łączy zasób (np. salę konferencyjną) z osobą rezerwującą
 * i określonym zakresem czasu. Przy utworzeniu automatycznie publikuje
 * zdarzenie domenowe ReservationCreated.
 */
#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservation')]
class Reservation
{
    use DomainEventsTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    public Uuid $id;

    #[ORM\ManyToOne(targetEntity: Resource::class, inversedBy: 'reservations')]
    public Resource $resource;

    #[ORM\Column(type: 'string')]
    public string $reservedBy;

    #[ORM\Column(type: 'datetime_range')]
    public DateTimeRange $period;

    #[ORM\Column(type: 'datetime_immutable')]
    public DateTimeImmutable $createdAt;

    /**
     * Tworzy nową rezerwację.
     *
     * @param Uuid $id Unikalny identyfikator rezerwacji
     * @param Resource $resource Zasób, dla którego tworzona jest rezerwacja
     * @param string $reservedBy Nazwa osoby rezerwującej
     * @param DateTimeRange $period Zakres dat i czasu rezerwacji
     */
    public function __construct(
        Uuid $id,
        Resource $resource,
        string $reservedBy,
        DateTimeRange $period
    ) {
        $this->id = $id;
        $this->resource = $resource;
        $this->reservedBy = $reservedBy;
        $this->period = $period;
        $this->createdAt = new DateTimeImmutable();

        $this->recordEvent(new ReservationCreated(
            reservationId: $this->id,
            resource: $this->resource,
            reservedBy: $this->reservedBy,
            period: $this->period,
            createdAt: $this->createdAt
        ));
    }
}
