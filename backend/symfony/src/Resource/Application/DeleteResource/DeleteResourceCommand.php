<?php

declare(strict_types=1);

namespace App\Resource\Application\DeleteResource;

use Symfony\Component\Uid\Uuid;

class DeleteResourceCommand
{
    public function __construct(
        public Uuid $id,
    ) {
    }
}

