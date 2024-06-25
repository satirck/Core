<?php

declare(strict_types=1);

namespace App\Route\Request;

use App\Route\Entities\MethodParam;
use App\Route\Entities\RouteEntity;
use ReflectionMethod;

class ParamResolver
{
    private static function getMethodParams(RouteEntity $routeEntity): array
    {
        $methodParams = [];

        $reflectionMethod = new ReflectionMethod($routeEntity->controller, $routeEntity->action);
        $reflectionParams = $reflectionMethod->getParameters();

        foreach ($reflectionParams as $reflectionParam) {
            $type = $reflectionParam->getType();

            if ($type !== null) {
                $methodParams[] = new MethodParam(
                    $type->getName(),
                    $reflectionParam->isOptional(),
                    $reflectionParam->getName()
                );
            }

        }

        return $methodParams;
    }

    public static function resolve(
        RequestInterface     $request,
        RouteEntity $routeEntity,
    ): array
    {
        $methodParams = self::getMethodParams($routeEntity);

        if ($methodParams === []){
            return [];
        }

        //TODO returning required params
        return [];
    }
}
