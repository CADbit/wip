<?php

declare(strict_types=1);

namespace App\Reservation\Domain\Entity;

use App\Reservation\Domain\ValueObject\DateTimeRange;
use App\Reservation\Infrastructure\Doctrine\ReservationRepository;
use App\Resource\Domain\Entity\Resource;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservation')]
class Reservation
{
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

    public function __construct(
        Uuid $id,
        Resource $resource,
        string $reservedBy,
        DateTimeRange $period
    )
    {
        $this->id = $id;
        $this->resource = $resource;
        $this->reservedBy = $reservedBy;
        $this->period = $period;
        $this->createdAt = new DateTimeImmutable();
    }
}
