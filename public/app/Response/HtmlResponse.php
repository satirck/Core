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

    private function makeHeader(string $key, string $value, string $sub = ''): void
    {
        header(
            sprintf(
                '%s: %s%s', $key, $value, $sub
            )
        );
    }

    private function makeHTTPHeaders(HttpHeaders $headers): void
    {
        $this->makeHeader('X-Action-Result', $headers->status);
        $this->makeHeader('Content-Type', $this->getContentType(), '; charset=utf-8');

        if (isset($headers->messages)){
            $this->makeHeader('X-Action-Messages', json_encode($headers->messages));
        }

        http_response_code($headers->code);
    }

    public function view(string $content_view, array $options, HttpHeaders $headers): void
    {
        $cur_resp_headers = apache_request_headers();

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
