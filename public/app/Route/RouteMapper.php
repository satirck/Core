<?php

declare(strict_types=1);

namespace App\Route;

use App\Response\HtmlResponse;
use App\Route\Exceptions\{
    InvalidRouteArgumentException,
    MissingRouteArgumentException,
    StatusErrorException
};

use App\Route\Attributes\{
    DomainKeyAttribute,
    MethodRouteAttribute
};

use App\Route\Entities\{
    ActionEntity,
    ControllerEntity,
    RouteEntity,
    MethodParam
};

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class RouteMapper
{
    private array $routesControllers;
    private const REQUEST_INDEX = 'request';
    private const DOMAIN_KEY_SPECIAL = 'special';
    private const DOMAIN_KEY_GENERAL = 'general';

    /**
     * @throws ReflectionException
     */
    public function __construct(
        protected string $controllersFolder
    )
    {
        $this->routesControllers = $this->getControllers();
    }

    private function getControllerFiles(string $folder): array
    {
        $returnFiles = [];

        if (is_dir($folder)) {
            $files = scandir($folder);

            foreach ($files as $file) {
                $filePath = $folder . DIRECTORY_SEPARATOR . $file;

                if (is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
                    $returnFiles[] = $filePath;
                    continue;
                }

                if (is_dir($filePath) && $file != '.' && $file != '..') {
                    $returnFiles = array_merge(
                        $returnFiles,
                        $this->getControllerFiles($filePath)
                    );
                }
            }
        }

        return $returnFiles;
    }

    /**
     * @return array of ControllerEntity
     * @throws ReflectionException
     */
    private function getControllersEntities(): array
    {
        $controllersEntities = [];
        $files = $this->getControllerFiles($this->controllersFolder);

        foreach ($files as $file) {
            require_once $file;
            $classes = get_declared_classes();

            foreach ($classes as $class) {
                $reflector = new ReflectionClass($class);
                if ($reflector->isInstantiable() && $reflector->getFileName() === realpath($file)) {
                    $controllerName = $reflector->getName();
                    $domainKeyAttribute = $reflector->getAttributes(DomainKeyAttribute::class);

                    if (!isset($domainKeyAttribute)) {
                        continue;
                    }

                    $domainKeyInst = $domainKeyAttribute[0]->newInstance();

                    $controllersEntities[] = new ControllerEntity(
                        $reflector->getName(),
                        $domainKeyInst->domainKey
                    );
                }
            }
        }

        return $controllersEntities;
    }

    /**
     * @throws ReflectionException
     */
    private function getControllers(): array
    {
        $controllersEntities = $this->getControllersEntities();
        $controllers = [
            self::DOMAIN_KEY_SPECIAL => [],
            self::DOMAIN_KEY_GENERAL => []
        ];

        foreach ($controllersEntities as $controllerEntity) {
            $controllers[self::DOMAIN_KEY_SPECIAL][$controllerEntity->domainKey][] = $controllerEntity->controller;
            $controllers[self::DOMAIN_KEY_GENERAL][] = $controllerEntity->controller;
        }

        return $controllers;
    }


    private function createRegular(string $pattern): string
    {
        $regex = preg_replace('/\{(\w+)}/', '(?P<\1>\d+)', $pattern);
        $regex = str_replace('/', '\/', $regex);

        return sprintf('/^%s$/', $regex);
    }

    private function getUrlDomainKey(string $url): string
    {
        preg_match('#^(/[^/]*)(/[^/]*)?#', $url, $matches);

        return $matches[1];
    }

    /**
     * @throws ReflectionException
     */
    private function getRouteEntityFromControllersArray(array $controllers, string $path, string $method): ?RouteEntity
    {
        foreach ($controllers as $controller) {
            $reflectionClass = new ReflectionClass($controller);
            $actionAndRegular = $this->getActionAndRegular($reflectionClass, $method, $path);

            if ($actionAndRegular !== null) {
                return new RouteEntity($controller, $actionAndRegular->action, $actionAndRegular->urlPattern);
            }
        }

        return null;
    }

    /**
     * @throws ReflectionException
     * @throws StatusErrorException
     */
    private function getRouteEntity(string $path, string $method): RouteEntity
    {
        $firstKey = $this->getUrlDomainKey($path);

        if (isset($this->routesControllers[self::DOMAIN_KEY_SPECIAL][$firstKey])) {
            $routeEntity = $this->getRouteEntityFromControllersArray(
                $this->routesControllers[self::DOMAIN_KEY_SPECIAL][$firstKey],
                $path,
                $method
            );

            if ($routeEntity !== null) {
                return $routeEntity;
            }
        }

        $routeEntity = $this->getRouteEntityFromControllersArray(
            $this->routesControllers[self::DOMAIN_KEY_GENERAL],
            $path,
            $method
        );

        if ($routeEntity !== null) {
            return $routeEntity;
        }

        throw new StatusErrorException(
            sprintf('Url %s not found...', $path),
            404
        );
    }

    private function getActionAndRegular(
        ReflectionClass $reflectionClass,
        string          $reqMethod,
        string          $path
    ): ?ActionEntity
    {
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $attributes = $method->getAttributes(MethodRouteAttribute::class);
            if (empty($attributes)) {
                continue;
            }

            $attribute = $attributes[0]->newInstance();

            $httpMethod = $attribute->getHttpMethod();
            $httpMethodReg = sprintf('/^%s$/', $httpMethod);

            if (!preg_match($httpMethodReg, $reqMethod)) {
                continue;
            }

            $urlPath = $attribute->getUrlPattern();
            $urlPattern = $this->createRegular($urlPath);

            if (!preg_match($urlPattern, $path)) {
                continue;
            }

            return new ActionEntity($method->getName(), $urlPattern);
        }

        return null;
    }

    private function getParams(string $url, string $regex): array
    {
        if (preg_match($regex, $url, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return [];
    }

    /**
     * @throws ReflectionException
     */
    private function getMethodParams(RouteEntity $routeEntity): array
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

    /**
     * @param array $params -> params from request
     * @param MethodParam[] $methodParams
     *
     * @throws MissingRouteArgumentException
     */
    private function checkParamSet(array $params, array $methodParams): void
    {
        foreach ($methodParams as $methodParam) {
            $name = $methodParam->name;
            $isOptional = $methodParam->optional;

            if (!isset($params[$name]) && !isset($params[self::REQUEST_INDEX][$name])) {
                if ($isOptional) {
                    continue;
                }

                throw new MissingRouteArgumentException(
                    sprintf('Missing parameter: %s', $name),
                    404
                );
            }
        }
    }

    /**
     * @param array $params
     * @param MethodParam[] $reqMethodParams
     * @return array
     *
     * @throws InvalidRouteArgumentException
     */
    private function castParams(array $params, array $reqMethodParams): array
    {
        $newParams = [];

        foreach ($reqMethodParams as $methodParams) {
            $name = $methodParams->name;
            $typeName = $methodParams->typename;
            $value = $params[$name] ?? $params[self::REQUEST_INDEX][$name];

            //TODO make prettier
            if ($typeName !== 'int' && $typeName !== 'string' && !class_exists($typeName)) {
                throw new InvalidRouteArgumentException(
                    sprintf('Type: %s was not casting', $typeName)
                );
            }

            if (class_exists($typeName) && method_exists($typeName, 'fromJson')) {
                $newParams[$name] = $typeName::fromJson($value);

                continue;
            }

            $newParams[$name] = $typeName === 'int' ? (int)$value : $value;
        }

        return $newParams;
    }

    /**
     * @throws ReflectionException
     * @throws InvalidRouteArgumentException
     * @throws StatusErrorException
     *
     */
    private function dispatch(string $url, string $method): void
    {
        $routeEntity = $this->getRouteEntity($url, $method);
        $params = $this->getParams($url, $routeEntity->urlPattern);

        $methodParams = $this->getMethodParams($routeEntity);

        if ($method === 'POST') {
            $params[self::REQUEST_INDEX] = $_POST;
        }

        try {
            $this->checkParamSet($params, $methodParams);
        } catch (MissingRouteArgumentException $e) {
            throw new StatusErrorException($e->getMessage(), 404, $e);
        }

        try {
            $finalParams = $this->castParams(
                $params,
                $methodParams
            );
        } catch (InvalidRouteArgumentException $e) {
            throw new StatusErrorException($e->getMessage(), 404, $e);
        }

        $controllerInstance = new $routeEntity->controller();
        $action = $routeEntity->action;

        $controllerInstance->$action(...$finalParams);
    }

    public function run(): void
    {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        $data = [];

        try {
            $this->dispatch($url, $method);
        } catch (StatusErrorException|InvalidRouteArgumentException|ReflectionException $e) {
            http_response_code($e->getCode());
            $data['code'] = $e->getCode();
            $data['message'] = $e->getMessage();

            HtmlResponse::View('404', $data);
        }

    }
}
