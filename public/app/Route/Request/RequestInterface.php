<?php

declare(strict_types=1);

namespace App\Route\Request;
interface RequestInterface
{
    public function getMethod(): string;

    public function getUri(): string;

    public function getHeaders(): array;

    public function getHeader(string $name): string;

    public function getParams(): array;

    public function getParam(string $name);
}
