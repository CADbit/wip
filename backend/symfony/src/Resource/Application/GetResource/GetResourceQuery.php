<?php

declare(strict_types=1);

namespace App\Resource\Application\GetResource;

use Symfony\Component\Uid\Uuid;

class GetResourceQuery
{
    public function __construct(
        public Uuid $id,
    ) {
    }
}
