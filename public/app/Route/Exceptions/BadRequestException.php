<?php

declare(strict_types=1);

namespace App\Route\Exceptions;

use Exception;

class BadRequestException extends Exception
{
    public function __construct()
    {
        parent::__construct();

        $this->code = 400;
    }
}
