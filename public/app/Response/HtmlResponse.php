<?php

declare(strict_types=1);

namespace App\Response;

class HtmlResponse implements ResponseInterface
{
    private const DEFAULT_GENERAL_VIEWS_PATH = 'app/Views/HTML/general_view.php';

    public function __construct(
        protected bool $onlyContent = false
    )
    {
    }

    public function getContentType(): string
    {
        return 'text/html';
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

        header('message: ' . $headers[self::HTTP_MESSAGE_TEXT]);

        http_response_code($headers[self::HTTP_STATUS_CODE]);
    }

    public function view(string $content_view, array $options, array $headers): void
    {
        self::makeHTTPHeaders($headers);

        $content_view = sprintf('app/Views/HTML/%s_view.php', $content_view);

        if (file_exists($content_view)) {
            if ($options != []) {
                extract($options, EXTR_PREFIX_SAME, 'data_');
            }

            require_once $this->onlyContent ? $content_view : self::DEFAULT_GENERAL_VIEWS_PATH;
        }
    }
}
