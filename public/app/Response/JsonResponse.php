<?php

declare(strict_types=1);

namespace App\Response;

class JsonResponse implements ResponseInterface
{

    public function getContentType(): string
    {
        return 'application/json';
    }

    private function makeHTTPHeaders(array $headers): void
    {
        http_response_code($headers[self::HTTP_STATUS_CODE]);

        header(
            sprintf(
                'Content-Type: %s; charset=utf-8',
                $this->getContentType()
            )
        );

        header('message: ' . $headers[self::MESSAGES]);

        http_response_code($headers[self::HTTP_STATUS_CODE]);
    }

    public function view(string $content_view, array $options, array $headers): void
    {
        $this->makeHTTPHeaders($headers);
        //TODO: make json response view
        echo 'In future';
    }
}
