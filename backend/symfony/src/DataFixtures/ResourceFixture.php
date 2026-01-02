<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Resource\Domain\Entity\Resource;
use App\Resource\Domain\Enum\ResourceStatus;
use App\Resource\Domain\Enum\ResourceType;
use App\Resource\Domain\Enum\ResourceUnavailability;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Uid\Uuid;

class ResourceFixture extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('pl_PL');
    }

    public function load(ObjectManager $manager): void
    {
        $resourceTypes = ResourceType::cases();
        $resourceStatuses = ResourceStatus::cases();

        // Przykładowe nazwy dla różnych typów zasobów
        $namesByType = [
            ResourceType::CONFERENCE_ROOM->value => [
                'Sala Konferencyjna A',
                'Sala Konferencyjna B',
                'Sala Spotkań Warszawa',
                'Sala Spotkań Kraków',
                'Sala Rady',
                'Sala Prezentacyjna',
                'Sala Szkoleniowa',
            ],
            ResourceType::CAR->value => [
                'Toyota Corolla',
                'Volkswagen Golf',
                'Ford Focus',
                'Opel Astra',
                'BMW 320d',
                'Audi A4',
                'Skoda Octavia',
            ],
            ResourceType::PROJECTOR->value => [
                'Projektor Epson',
                'Projektor BenQ',
                'Projektor Optoma',
                'Projektor Sony',
                'Projektor LG',
            ],
        ];

        // Generuj 15 losowych zasobów
        for ($i = 0; $i < 15; $i++) {
            $type = $this->faker->randomElement($resourceTypes);
            $status = $this->faker->randomElement($resourceStatuses);

            // 70% szans na ACTIVE, 30% na DISABLED
            if ($this->faker->boolean(70)) {
                $status = ResourceStatus::ACTIVE;
            } else {
                $status = ResourceStatus::DISABLED;
            }

            // Jeśli ACTIVE, 20% szans na unavailability
            $unavailability = null;
            if ($status === ResourceStatus::ACTIVE && $this->faker->boolean(20)) {
                $unavailability = $this->faker->randomElement([
                    ResourceUnavailability::MAINTENANCE,
                    ResourceUnavailability::ADMIN_BLOCK,
                ]);
            }

            // Wybierz nazwę z listy dla danego typu lub wygeneruj losową
            $name = $this->faker->randomElement(
                $namesByType[$type->value] ?? [$this->faker->words(2, true)]
            );

            // Generuj opis (50% szans)
            $description = $this->faker->boolean(50)
                ? $this->faker->sentence(10)
                : null;

            $resource = new Resource(
                id: Uuid::v4(),
                type: $type,
                name: $name,
                description: $description,
                status: $status,
                unavailability: $unavailability
            );

            $manager->persist($resource);
        }

        $manager->flush();
    }
}

