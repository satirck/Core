<?php

namespace App\Response;

class JsonResponse implements ResponseInterface
{
    public static function View(string $content_view, array $data): void
    {
        //TODO: make json response view
        echo 'In future';
    }
}