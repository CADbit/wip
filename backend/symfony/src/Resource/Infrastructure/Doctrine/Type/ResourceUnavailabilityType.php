<?php

declare(strict_types=1);

namespace App\Resource\Infrastructure\Doctrine\Type;

use App\Resource\Domain\Enum\ResourceUnavailability;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class ResourceUnavailabilityType extends Type
{
    public const NAME = 'resource_unavailability';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof ResourceUnavailability) {
            return $value->value;
        }

        return (string) $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?ResourceUnavailability
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof ResourceUnavailability) {
            return $value;
        }

        return ResourceUnavailability::from((string) $value);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}

