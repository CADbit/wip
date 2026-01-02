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

        if (is_array($value)) {
            if (count($value) === 2) {
                $startStr = is_string($value[0]) ? $value[0] : (string)$value[0];
                $endStr = is_string($value[1]) ? $value[1] : (string)$value[1];
                
                $endStr = rtrim($endStr, ')');

                $start = $this->parseDateTime($startStr);
                $end = $this->parseDateTime($endStr);

                if ($start === false || $end === false) {
                    throw new \InvalidArgumentException(
                        'Invalid date range format. Start: ' . var_export($startStr, true) . 
                        ', End: ' . var_export($endStr, true) . 
                        ', Value: ' . json_encode($value)
                    );
                }

                return new DateTimeRange($start, $end);
            }
            
            if (isset($value['lower']) && isset($value['upper'])) {
                $startStr = is_string($value['lower']) ? $value['lower'] : (string)$value['lower'];
                $endStr = is_string($value['upper']) ? $value['upper'] : (string)$value['upper'];
                
                $start = $this->parseDateTime($startStr);
                $end = $this->parseDateTime($endStr);

                if ($start === false || $end === false) {
                    throw new \InvalidArgumentException('Invalid date range format: ' . json_encode($value));
                }

                return new DateTimeRange($start, $end);
            }
            
            throw new \InvalidArgumentException('Invalid array format for date range: ' . json_encode($value));
        }

        if (is_string($value)) {
            $value = trim($value);

            if (str_starts_with($value, '[') && str_ends_with($value, ')')) {
                $jsonValue = rtrim($value, ')') . ']';
                $decoded = json_decode($jsonValue, true);
                if (is_array($decoded) && count($decoded) === 2) {
                    $startStr = $decoded[0];
                    $endStr = $decoded[1];

                    $start = $this->parseDateTime($startStr);
                    $end = $this->parseDateTime($endStr);

                    if ($start !== false && $end !== false) {
                        return new DateTimeRange($start, $end);
                    }
                }
            }

            if (preg_match('/^[\[\(](.+?),\s*(.+?)[\]\)]$/', $value, $matches)) {
                $startStr = trim($matches[1]);
                $endStr = trim($matches[2]);

                $start = $this->parseDateTime($startStr);
                $end = $this->parseDateTime($endStr);

                if ($start === false || $end === false) {
                    throw new \InvalidArgumentException('Invalid date range format: ' . $value);
                }

                return new DateTimeRange($start, $end);
            }

            throw new \InvalidArgumentException('Invalid PostgreSQL range format: ' . $value);
        }

        throw new InvalidArgumentException('Expected array or string value for PostgreSQL range, got: ' . gettype($value));
    }

    private function parseDateTime(string $dateString): DateTimeImmutable|false
    {
        $normalized = $dateString;
        
        if (preg_match('/^(.+?)([+-]\d{2})$/u', $dateString, $matches)) {
            $base = $matches[1];
            $tz = $matches[2];
            if (!str_contains($tz, ':')) {
                $normalized = $base . $tz . ':00';
            }
        }

        try {
            return new DateTimeImmutable($normalized);
        } catch (Exception $e) {
        }

        $formats = [
            'Y-m-d H:i:s.uP',
            'Y-m-d H:i:sP',
            'Y-m-d H:i:s.u',
            'Y-m-d H:i:s',
            DateTimeInterface::ATOM,
            DateTimeInterface::RFC3339,
        ];

        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $normalized);
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

