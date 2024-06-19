<?php

declare(strict_types=1);

namespace App\Response;

interface ResponseInterface
{
    public const HTTP_STATUS_CODE = 'CODE';
    public const HTTP_MESSAGE_TEXT = 'MESSAGE';

    public const HTTP_DEFAULT_CONTENT_TYPE = 'text/html';

    public function getContentType(): string;
    public function view(string $content_view, array $options, array $headers): void;

}
