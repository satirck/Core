<?php

declare(strict_types=1);

namespace App\Validation;

abstract class AbstractValidator implements ValidationInterface
{
    protected array $messages = [];

    protected bool $isValid = true;

    public function lengthStringCheck(int $min, int $max, string $str, string $associateName): void
    {
        $len = strlen($str);

        if ($len <= $max && $len >= $min)
            return;

        if ($len < $min) {
            $this->messages[$associateName][] =
                sprintf('Length of given %s is less than %d minimal', $str, $min);
        } else {
            $this->messages[$associateName][] =
                sprintf('Length of given %s is more than %d maximal', $str, $max);
        }

        $this->isValid = false;
    }

    public function rangeCheck(int $min, int $max, int $value, string $associateName): void
    {
        if ($value <= $max && $value >= $min)
            return;

        if ($value < $min) {
            $this->messages[$associateName][] =
                sprintf('Value of given %d is less than %d minimal', $value, $min);
        } else {
            $this->messages[$associateName][] =
                sprintf('Value of given %d is more than %d maximal', $value, $max);
        }

        $this->isValid = false;
    }

    public function emailValueCheck(string $email, string $associateName): void
    {
        if (preg_match('/(\w+)@(\w+).(\w+)/', $email)) {
            return;
        }

        $this->messages[$associateName][] =
            sprintf('Email %s in incorrect', $email);

        $this->isValid = false;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function validate(): bool{
        return $this->isValid;
    }
}
