<?php

declare(strict_types=1);

namespace App\Route;

use App\Response\HtmlResponse;
use App\Response\ResponseInterface;

use App\Route\Attributes\MethodRouteAttribute;
use App\Route\Entities\ActionEntity;
use App\Route\Entities\RouteEntity;
use App\Route\Exceptions\StatusErrorException;
use App\Route\Loaders\ControllersLoader;
use App\Route\Request\ParamResolver;
use App\Route\Request\RequestInterface;
use App\Route\Request\Request;

use Monolog\Handler\StreamHandler;

use Monolog\Logger;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class RouteMapper
{
    private array $routesControllers;
    private ResponseInterface $response;
    private Logger $logger;
    private RequestInterface $request;
    private const DOMAIN_KEY_SPECIAL = 'special';
    private const DOMAIN_KEY_GENERAL = 'general';

    private function setupLogger(): void
    {
        $this->logger = new Logger('logger');

        $this->logger->pushHandler(
            new StreamHandler('logs/debug.log', 'debug', false)
        );
        $this->logger->pushHandler(
            new StreamHandler('logs/info.log', 'info', false)
        );
        $this->logger->pushHandler(
            new StreamHandler('logs/warnings.log', 'warning', false)
        );
        $this->logger->pushHandler(
            new StreamHandler('logs/errors.log', 'error', false)
        );
    }

    private function setResponseEntity(): void
    {
        $this->response = new HtmlResponse();
    }

    /**
     * @throws ReflectionException
     */
    public function __construct(
        protected string $controllersFolder
    )
    {
        $this->setupLogger();

        $this->routesControllers = ControllersLoader::loadControllers($this->controllersFolder);

        $this->request = new Request();
        $this->setResponseEntity();
    }

    private function getUriDomainKey(string $uri): string
    {
        preg_match('#^(/[^/]*)(/[^/]*)?#', $uri, $matches);

        return $matches[1];
    }

    private function createRegular(string $pattern): string
    {
        $regex = preg_replace('/\{(\w+)}/', '(?P<\1>\d+)', $pattern);
        $regex = str_replace('/', '\/', $regex);

        return sprintf('/^%s$/', $regex);
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
    private function getRouteEntity(): RouteEntity
    {
        $uri = $this->request->getUri();
        $method = $this->request->getMethod();
        $domainKey = $this->getUriDomainKey($uri);

        if (isset($this->routesControllers[self::DOMAIN_KEY_SPECIAL][$domainKey])) {
            $entity = $this->getRouteEntityFromControllersArray(
                $this->routesControllers[self::DOMAIN_KEY_SPECIAL][$domainKey],
                $uri,
                $method
            );

            if ($entity !== null) {
                $this->logger->info(
                    sprintf(
                        'Find [%s] handler in special with key =[%s]', $uri, $domainKey
                    ),
                    [
                        'controller' => $entity
                    ]
                );

                return $entity;
            }
        }

        $entity = $this->getRouteEntityFromControllersArray(
            $this->routesControllers[self::DOMAIN_KEY_GENERAL],
            $uri,
            $method
        );

        if ($entity !== null) {
            $this->logger->info(
                sprintf(
                    'Find [%s] handler in general', $uri
                ),
                [
                    'controller' => $entity
                ]
            );

            return $entity;
        }

        throw new StatusErrorException(
            sprintf(
                'Url [%s] not found', $uri
            ),
            404
        );
    }

    /**
     * @throws ReflectionException|StatusErrorException
     */
    private function dispatch(): void
    {
        $routeEntity = $this->getRouteEntity();
        $params = ParamResolver::resolve(
            $this->request,
            $routeEntity
        );

        $controllerInst = new $routeEntity->controller();
        $action = $routeEntity->action;
        $controllerInst->$action(...$params);
    }

    public function run(): void
    {
        try {
            $this->dispatch();

        } catch (ReflectionException|StatusErrorException $exception) {
            echo $exception->getMessage();

            $this->logger->debug($exception->getMessage(), [
                'exception' => $exception
            ]);
        }catch (\Throwable $exception) {
            echo $exception->getMessage();
        }
        echo PHP_EOL;
    }
}
