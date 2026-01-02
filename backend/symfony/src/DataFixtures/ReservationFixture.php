<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Reservation\Domain\Entity\Reservation;
use App\Reservation\Domain\ValueObject\DateTimeRange;
use App\Resource\Domain\Entity\Resource;
use App\Resource\Domain\Enum\ResourceStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Uid\Uuid;

class ReservationFixture extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('pl_PL');
    }

    public function load(ObjectManager $manager): void
    {
        /** @var EntityManagerInterface $em */
        $em = $manager;

        // Pobierz wszystkie aktywne zasoby
        $activeResources = $em->getRepository(Resource::class)
            ->createQueryBuilder('r')
            ->where('r.status = :status')
            ->setParameter('status', ResourceStatus::ACTIVE)
            ->getQuery()
            ->getResult();

        if (empty($activeResources)) {
            return; // Brak aktywnych zasobów, nie można utworzyć rezerwacji
        }

        // Przykładowe imiona i nazwiska dla rezerwujących
        $reservedByNames = [
            'Jan Kowalski',
            'Anna Nowak',
            'Piotr Wiśniewski',
            'Maria Wójcik',
            'Tomasz Kowalczyk',
            'Katarzyna Zielińska',
            'Marcin Szymański',
            'Agnieszka Woźniak',
            'Paweł Kozłowski',
            'Magdalena Jankowska',
            'Michał Mazur',
            'Ewa Krawczyk',
            'Krzysztof Piotrowski',
            'Joanna Grabowski',
            'Robert Nowakowski',
        ];

        // Generuj 25 losowych rezerwacji dla aktywnych zasobów
        for ($i = 0; $i < 25; $i++) {
            $resource = $this->faker->randomElement($activeResources);
            $reservedBy = $this->faker->randomElement($reservedByNames);

            // Generuj okres rezerwacji
            // 40% szans na przeszłe rezerwacje, 30% na aktualne, 30% na przyszłe
            $now = new \DateTimeImmutable();
            $randomChoice = $this->faker->numberBetween(1, 10);

            if ($randomChoice <= 4) {
                // Przyszłe rezerwacje (od teraz do 3 miesięcy w przyszłość)
                $start = $now->modify('+' . $this->faker->numberBetween(1, 90) . ' days')
                    ->setTime($this->faker->numberBetween(8, 18), $this->faker->randomElement([0, 30]), 0);
                $durationHours = $this->faker->numberBetween(1, 8);
                $end = $start->modify("+{$durationHours} hours");
            } elseif ($randomChoice <= 7) {
                // Aktualne rezerwacje (od 1 dnia wstecz do 1 dnia w przód)
                $start = $now->modify('-' . $this->faker->numberBetween(0, 1) . ' days')
                    ->setTime($this->faker->numberBetween(8, 18), $this->faker->randomElement([0, 30]), 0);
                $durationHours = $this->faker->numberBetween(1, 8);
                $end = $start->modify("+{$durationHours} hours");
            } else {
                // Przeszłe rezerwacje (od 3 miesięcy wstecz do 1 dnia wstecz)
                $start = $now->modify('-' . $this->faker->numberBetween(1, 90) . ' days')
                    ->setTime($this->faker->numberBetween(8, 18), $this->faker->randomElement([0, 30]), 0);
                $durationHours = $this->faker->numberBetween(1, 8);
                $end = $start->modify("+{$durationHours} hours");
            }

            $period = new DateTimeRange($start, $end);

            $reservation = new Reservation(
                id: Uuid::v4(),
                resource: $resource,
                reservedBy: $reservedBy,
                period: $period
            );

            $manager->persist($reservation);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ResourceFixture::class,
        ];
    }
}

