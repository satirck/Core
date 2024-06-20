<?php

declare(strict_types=1);

namespace App\Route\Controllers;

use App\Response\HtmlResponse;
use App\Response\HttpHeaders;
use App\Response\ResponseInterface;
use App\Route\Attributes\{DomainKeyAttribute, MethodRouteAttribute};
use Monolog\Logger;

#[DomainKeyAttribute('/')]
class HomeController implements RouteControllerInterface
{
    public function __construct(
        protected ResponseInterface $response,
        protected Logger            $logger,
    )
    {
    }

    #[MethodRouteAttribute('GET', '/')]
    public function index(): void
    {
        $this->logger->info('get / path at HomeController');

        $this->response->view(
            'home',
            [],
            new HttpHeaders(
                'Get Home Page',
                200
            )
        );
    }

}
