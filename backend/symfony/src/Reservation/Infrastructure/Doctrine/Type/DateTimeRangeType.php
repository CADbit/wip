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

        // PostgreSQL może zwrócić tstzrange jako tablicę [start, end] lub jako string "[start, end)"
        if (is_array($value)) {
            // Obsłuż różne formaty tablic
            if (count($value) === 2) {
                // Format tablicowy: ["2026-01-02 18:30:00+00", "2026-01-02 21:30:00+00")
                $startStr = is_string($value[0]) ? $value[0] : (string)$value[0];
                $endStr = is_string($value[1]) ? $value[1] : (string)$value[1];
                
                // Usuń zamykający nawias jeśli jest w stringu
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
            
            // Może być tablica asocjacyjna z kluczami 'lower' i 'upper'
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

        // PostgreSQL range format jako string: [start, end) lub (start, end)
        if (is_string($value)) {
            $value = trim($value);

            // Sprawdź czy string wygląda jak tablica PHP (np. z serializacji)
            // Próbuj zparsować jako JSON jeśli wygląda jak tablica
            if (str_starts_with($value, '[') && str_ends_with($value, ')')) {
                // Może to być string reprezentujący tablicę, spróbuj zparsować
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

        throw new InvalidArgumentException('Expected array or string value for PostgreSQL range, got: ' . gettype($value));
    }

    private function parseDateTime(string $dateString): DateTimeImmutable|false
    {
        // Normalizuj format timezone - PostgreSQL może zwrócić +00 zamiast +00:00
        // Format +00 nie jest rozpoznawany przez createFromFormat z P, więc normalizujmy
        $normalized = $dateString;
        
        // Zamień +00 na końcu na +00:00
        if (preg_match('/^(.+?)([+-]\d{2})$/u', $dateString, $matches)) {
            $base = $matches[1];
            $tz = $matches[2];
            // Jeśli timezone nie ma :, dodaj :00
            if (!str_contains($tz, ':')) {
                $normalized = $base . $tz . ':00';
            }
        }

        // Spróbuj najpierw przez konstruktor - DateTimeImmutable jest bardzo elastyczny
        try {
            return new DateTimeImmutable($normalized);
        } catch (Exception $e) {
            // Jeśli konstruktor nie zadziała, spróbuj przez createFromFormat
        }

        // Fallback do createFromFormat z różnymi formatami
        $formats = [
            'Y-m-d H:i:s.uP',  // Z mikrosekundami i timezone
            'Y-m-d H:i:sP',    // Bez mikrosekund z timezone
            'Y-m-d H:i:s.u',   // Z mikrosekundami bez timezone
            'Y-m-d H:i:s',     // Bez mikrosekund i timezone
            DateTimeInterface::ATOM, // ISO 8601
            DateTimeInterface::RFC3339,
        ];

        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $normalized);
            if ($date !== false) {
                return $date;
            }
        }

        // Ostatnia próba z oryginalnym stringiem
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

