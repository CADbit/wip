<?php

declare(strict_types=1);

namespace App\Reservation\Infrastructure\Doctrine\Type;

use App\Reservation\Domain\ValueObject\DateTimeRange;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Exception;
use InvalidArgumentException;

class DateTimeRangeType extends Type
{
    public const NAME = 'datetime_range';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'tstzrange';
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof DateTimeRange) {
            throw new InvalidArgumentException('Expected DateTimeRange instance');
        }

        $start = $value->start()->format('Y-m-d H:i:s.uP');
        $end = $value->end()->format('Y-m-d H:i:s.uP');

        // PostgreSQL range format: [start, end)
        return sprintf('[%s,%s)', $start, $end);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?DateTimeRange
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeRange) {
            return $value;
        }

        // PostgreSQL range format: [start, end) lub (start, end)
        if (!is_string($value)) {
            throw new InvalidArgumentException('Expected string value for PostgreSQL range');
        }

        $value = trim($value);

        // Parsuj format PostgreSQL range: [2024-01-01 10:00:00+00,2024-01-01 12:00:00+00)
        if (preg_match('/^[\[\(](.+?),\s*(.+?)[\]\)]$/', $value, $matches)) {
            $startStr = trim($matches[1]);
            $endStr = trim($matches[2]);

            // Spróbuj różne formaty daty
            $start = $this->parseDateTime($startStr);
            $end = $this->parseDateTime($endStr);

            if ($start === false || $end === false) {
                throw new \InvalidArgumentException('Invalid date range format: ' . $value);
            }

            return new DateTimeRange($start, $end);
        }

        throw new \InvalidArgumentException('Invalid PostgreSQL range format: ' . $value);
    }

    private function parseDateTime(string $dateString): DateTimeImmutable|false
    {
        $formats = [
            'Y-m-d H:i:s.uP',  // Z mikrosekundami i timezone
            'Y-m-d H:i:s.u',   // Z mikrosekundami bez timezone
            'Y-m-d H:i:sP',    // Bez mikrosekund z timezone
            'Y-m-d H:i:s',     // Bez mikrosekund i timezone
            DateTimeInterface::ATOM, // ISO 8601
            DateTimeInterface::RFC3339,
        ];

        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date;
            }
        }

        try {
            return new DateTimeImmutable($dateString);
        } catch (Exception $e) {
            return false;
        }
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

