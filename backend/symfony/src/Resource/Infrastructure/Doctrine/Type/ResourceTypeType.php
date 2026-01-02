<?php

declare(strict_types=1);

namespace App\Resource\Infrastructure\Doctrine\Type;

use App\Resource\Domain\Enum\ResourceType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

class ResourceTypeType extends Type
{
    public const NAME = 'resource_type';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    /**
     * @param mixed $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof ResourceType) {
            return $value->value;
        }

        if (!is_string($value) && !is_scalar($value)) {
            throw new InvalidArgumentException('Value must be a string or scalar');
        }

        return (string) $value;
    }

    /**
     * @param mixed $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?ResourceType
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof ResourceType) {
            return $value;
        }

        if (!is_string($value) && !is_scalar($value)) {
            throw new InvalidArgumentException('Value must be a string or scalar');
        }

        return ResourceType::from((string) $value);
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

