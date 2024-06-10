<?php

declare(strict_types=1);

namespace App\repository;

use App\models\User;

class UserRepository extends AbstractEntityRepository
{
    public function __construct()
    {
        parent::__construct('app/storage/users.json', User::class);
    }
}