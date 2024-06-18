<?php

declare(strict_types=1);

namespace App\Route\Controllers;

use App\Response\HtmlResponse;
use App\Route\Attributes\{DomainKeyAttribute, MethodRouteAttribute};

#[DomainKeyAttribute('/')]
class HomeController implements RouteControllerInterface
{
    #[MethodRouteAttribute('GET', '/')]
    public function index(): void
    {
        HtmlResponse::View( 'home', []);
    }

}
