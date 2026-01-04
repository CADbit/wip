<?php

declare(strict_types=1);

namespace App\Reservation\Domain\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Value Object reprezentujący zakres dat i czasu.
 *
 * Klasa zapewnia niezmienny (immutable) zakres czasu z walidacją,
 * że data zakończenia jest późniejsza niż data rozpoczęcia.
 * Używana do reprezentowania okresu rezerwacji zasobu.
 */
class DateTimeRange
{
    private DateTimeImmutable $start;
    private DateTimeImmutable $end;

    /**
     * Tworzy nowy zakres dat i czasu.
     *
     * @param DateTimeImmutable $start Data i godzina rozpoczęcia zakresu
     * @param DateTimeImmutable $end Data i godzina zakończenia zakresu
     *
     * @throws InvalidArgumentException Gdy data zakończenia jest wcześniejsza
     *                                  lub równa dacie rozpoczęcia
     */
    public function __construct(
        DateTimeImmutable $start,
        DateTimeImmutable $end
    ) {
        if ($end <= $start) {
            throw new InvalidArgumentException(
                'Data zakończenia musi być późniejsza niż data rozpoczęcia'
            );
        }

        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Zwraca datę i godzinę rozpoczęcia zakresu.
     *
     * @return DateTimeImmutable Data i godzina rozpoczęcia
     */
    public function start(): DateTimeImmutable
    {
        return $this->start;
    }

    /**
     * Zwraca datę i godzinę zakończenia zakresu.
     *
     * @return DateTimeImmutable Data i godzina zakończenia
     */
    public function end(): DateTimeImmutable
    {
        return $this->end;
    }

    /**
     * Sprawdza, czy ten zakres nakłada się z innym zakresem.
     *
     * Dwa zakresy nakładają się, jeśli:
     * - start pierwszego < end drugiego
     * - end pierwszego > start drugiego
     *
     * @param self $other Inny zakres dat do porównania
     *
     * @return bool True, jeśli zakresy się nakładają, false w przeciwnym razie
     */
    public function overlaps(self $other): bool
    {
        return $this->start < $other->end
            && $this->end > $other->start;
    }
}
