<?php

declare(strict_types=1);

namespace App\Message;

class MessageTest
{
    public function __construct(public string $content = 'Hello from TestMessage!')
    {
    }
}
