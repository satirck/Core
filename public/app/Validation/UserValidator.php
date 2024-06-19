<?php

declare(strict_types=1);

namespace App\Validation;

use App\Models\User;
use App\Validation\AbstractValidator;

class UserValidator extends AbstractValidator
{
    protected User $user;

    public function setUser(User $user): void
    {
        $this->messages = [];
        $this->isValid = true;
        $this->user = $user;
    }

    public function validate(): bool
    {
        $this->lengthStringCheck(3, 20,
            $this->user->getName(),
            'name'
        );

        $this->lengthStringCheck(3, 20,
            $this->user->getEmail(),
            'email'
        );

        $this->emailValueCheck(
            $this->user->getEmail(),
            'email'
        );

        return $this->isValid;
    }
}