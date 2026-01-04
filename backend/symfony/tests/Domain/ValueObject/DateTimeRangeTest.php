<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObject;

use App\Reservation\Domain\ValueObject\DateTimeRange;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Testy jednostkowe dla klasy DateTimeRange.
 *
 * Testuje logikę walidacji i operacji na zakresach dat i czasu.
 */
class DateTimeRangeTest extends TestCase
{
    /**
     * Test tworzenia poprawnego zakresu dat.
     */
    public function testCreateValidRange(): void
    {
        $start = new DateTimeImmutable('2024-01-01 10:00:00');
        $end = new DateTimeImmutable('2024-01-01 12:00:00');

        $range = new DateTimeRange($start, $end);

        $this->assertSame($start, $range->start());
        $this->assertSame($end, $range->end());
    }

    /**
     * Test, że nie można utworzyć zakresu z datą zakończenia równą dacie rozpoczęcia.
     */
    public function testCannotCreateRangeWithEqualDates(): void
    {
        $date = new DateTimeImmutable('2024-01-01 10:00:00');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data zakończenia musi być późniejsza niż data rozpoczęcia');

        new DateTimeRange($date, $date);
    }

    /**
     * Test, że nie można utworzyć zakresu z datą zakończenia wcześniejszą niż data rozpoczęcia.
     */
    public function testCannotCreateRangeWithEndBeforeStart(): void
    {
        $start = new DateTimeImmutable('2024-01-01 12:00:00');
        $end = new DateTimeImmutable('2024-01-01 10:00:00');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data zakończenia musi być późniejsza niż data rozpoczęcia');

        new DateTimeRange($start, $end);
    }

    /**
     * Test sprawdzania nakładania się zakresów - zakresy się nakładają.
     */
    public function testOverlapsWhenRangesOverlap(): void
    {
        $range1 = new DateTimeRange(
            new DateTimeImmutable('2024-01-01 10:00:00'),
            new DateTimeImmutable('2024-01-01 12:00:00')
        );

        $range2 = new DateTimeRange(
            new DateTimeImmutable('2024-01-01 11:00:00'),
            new DateTimeImmutable('2024-01-01 13:00:00')
        );

        $this->assertTrue($range1->overlaps($range2));
        $this->assertTrue($range2->overlaps($range1));
    }

    /**
     * Test sprawdzania nakładania się zakresów - zakresy się nie nakładają.
     */
    public function testOverlapsWhenRangesDoNotOverlap(): void
    {
        $range1 = new DateTimeRange(
            new DateTimeImmutable('2024-01-01 10:00:00'),
            new DateTimeImmutable('2024-01-01 12:00:00')
        );

        $range2 = new DateTimeRange(
            new DateTimeImmutable('2024-01-01 13:00:00'),
            new DateTimeImmutable('2024-01-01 15:00:00')
        );

        $this->assertFalse($range1->overlaps($range2));
        $this->assertFalse($range2->overlaps($range1));
    }

    /**
     * Test sprawdzania nakładania się zakresów - zakresy są styczne (nie nakładają się).
     */
    public function testOverlapsWhenRangesAreAdjacent(): void
    {
        $range1 = new DateTimeRange(
            new DateTimeImmutable('2024-01-01 10:00:00'),
            new DateTimeImmutable('2024-01-01 12:00:00')
        );

        $range2 = new DateTimeRange(
            new DateTimeImmutable('2024-01-01 12:00:00'),
            new DateTimeImmutable('2024-01-01 14:00:00')
        );

        $this->assertFalse($range1->overlaps($range2));
        $this->assertFalse($range2->overlaps($range1));
    }

    /**
     * Test sprawdzania nakładania się zakresów - jeden zakres zawiera się w drugim.
     */
    public function testOverlapsWhenOneRangeContainsAnother(): void
    {
        $outerRange = new DateTimeRange(
            new DateTimeImmutable('2024-01-01 10:00:00'),
            new DateTimeImmutable('2024-01-01 15:00:00')
        );

        $innerRange = new DateTimeRange(
            new DateTimeImmutable('2024-01-01 11:00:00'),
            new DateTimeImmutable('2024-01-01 14:00:00')
        );

        $this->assertTrue($outerRange->overlaps($innerRange));
        $this->assertTrue($innerRange->overlaps($outerRange));
    }

    /**
     * Test sprawdzania nakładania się zakresów - zakresy zaczynają się w tym samym momencie.
     */
    public function testOverlapsWhenRangesStartAtSameTime(): void
    {
        $range1 = new DateTimeRange(
            new DateTimeImmutable('2024-01-01 10:00:00'),
            new DateTimeImmutable('2024-01-01 12:00:00')
        );

        $range2 = new DateTimeRange(
            new DateTimeImmutable('2024-01-01 10:00:00'),
            new DateTimeImmutable('2024-01-01 13:00:00')
        );

        $this->assertTrue($range1->overlaps($range2));
        $this->assertTrue($range2->overlaps($range1));
    }
}
