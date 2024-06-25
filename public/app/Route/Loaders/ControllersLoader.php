<?php

declare(strict_types=1);

namespace App\Route\Loaders;

use App\Route\Attributes\DomainKeyAttribute;
use App\Route\Entities\ControllerEntity;
use ReflectionClass;
use ReflectionException;

class ControllersLoader
{
    const DOMAIN_KEY_SPECIAL = 'special';
    const DOMAIN_KEY_GENERAL = 'general';


    private static function getControllerFiles(string $folder): array
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
                        self::getControllerFiles($filePath)
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
    private static function getControllersEntities(string $folder): array
    {
        $controllersEntities = [];

        $files = self::getControllerFiles($folder);

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
    public static function loadControllers(string $folder): array
    {
        $controllersEntities = self::getControllersEntities($folder);

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
}
