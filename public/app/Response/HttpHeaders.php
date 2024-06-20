<?php

declare(strict_types=1);

namespace App\Response;

class HttpHeaders
{
    public function __construct(
        public readonly string $status,
        public readonly int $code,
        public readonly array $messages = []
    )
    {
    }
}
