<?php

declare(strict_types=1);

namespace App\Resource\Domain\Repository;

use App\Resource\Domain\Entity\Resource;

interface ResourceRepositoryInterface
{
    public function save(Resource $resource): void;
    public function remove(Resource $resource): void;
    public function flush(): void;
    public function findById(string $id): ?Resource;

    /** @return Resource[] */
    public function findAllActive(): array;
}
