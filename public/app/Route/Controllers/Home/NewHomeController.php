<?php

declare(strict_types=1);

namespace App\Route\Controllers\Home;

use App\Route\Attributes\{
    DomainKeyAttribute,
    MethodRouteAttribute
};

use App\Route\Controllers\RouteControllerInterface;

#[DomainKeyAttribute('/')]
class NewHomeController implements RouteControllerInterface
{
    #[MethodRouteAttribute('GET', '/2')]
    public function index(): void
    {
        echo 'Hello world at Home 2';
    }
}
