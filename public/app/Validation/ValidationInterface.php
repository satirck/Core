<?php

declare(strict_types=1);

namespace App\Validation;

interface ValidationInterface
{
    public function getMessages(): array;

    public function validate(): bool;
}
