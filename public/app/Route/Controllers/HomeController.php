<?php

declare(strict_types=1);

namespace App\Route\Controllers;

use App\Response\HtmlResponse;
use App\Response\ResponseInterface;
use App\Route\Attributes\{DomainKeyAttribute, MethodRouteAttribute};

#[DomainKeyAttribute('/')]
class HomeController implements RouteControllerInterface
{
    public function __construct(
        protected ResponseInterface $response,
    )
    {
    }

    #[MethodRouteAttribute('GET', '/')]
    public function index(): void
    {
        $this->response->view( 'home', [], [
            ResponseInterface::HTTP_STATUS_CODE => 200,
            ResponseInterface::HTTP_MESSAGE_TEXT => 'Hello at home page',
        ]);
    }

}
