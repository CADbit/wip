<?php

declare(strict_types=1);

namespace App\Resource\Domain\Entity;

use App\Resource\Domain\Enum\ResourceStatus;
use App\Resource\Domain\Enum\ResourceType;
use App\Resource\Domain\Enum\ResourceUnavailability;
use App\Resource\Infrastructure\Doctrine\ResourceRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ResourceRepository::class)]
#[ORM\Table(name: 'resource')]
class Resource
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    public Uuid $id;

    #[ORM\Column(type: 'resource_type')]
    public ResourceType $type;

    #[ORM\Column(type: 'string')]
    public string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $description = null;

    #[ORM\Column(type: 'resource_status')]
    public ResourceStatus $status;

    #[ORM\Column(type: 'resource_unavailability', nullable: true)]
    public ?ResourceUnavailability $unavailability = null;

    #[ORM\Column(type: 'datetime_immutable')]
    public DateTimeImmutable $createdAt;

    public function __construct(
        Uuid $id,
        ResourceType $type,
        string $name,
        ?string $description = null,
        ResourceStatus $status,
        ?ResourceUnavailability $unavailability = null,
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->name = $name;
        $this->description = $description;
        $this->status = $status;
        $this->unavailability = $unavailability;
        $this->createdAt = new DateTimeImmutable();
    }
}
