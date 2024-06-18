<?php

namespace App\Response;

interface ResponseInterface
{
    static function View(string $content_view, array $data): void;
}