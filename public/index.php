<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use App\Route\RouteMapper;

$routeMapper = new RouteMapper('app/Route/Controllers');
$routeMapper->run();
