<?php

declare(strict_types=1);

namespace App\Route\Request;

abstract class AbstractRequest implements RequestInterface
{
    protected array $headers;
    protected array $params;
    protected string $method;
    protected string $uri;

    public function __construct()
    {
        $this->setHeaders();
        $this->params = $_REQUEST;

        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = $_SERVER['REQUEST_URI'];

        if (isset($this->headers['print-params'])){
            echo json_encode($this->params);
        }
    }


    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): string
    {
        return $this->headers[$name] ?? '';
    }

    public function getParams(): array
    {
        return $this->headers;
    }

    public function getParam(string $name): string
    {
        return $this->params[$name] ?? '';
    }

    private function setHeaders(): void
    {
        $this->headers = array_change_key_case(apache_request_headers());

        if (!isset($this->headers['accept'])) {
            $this->headers['accept'] = 'text/html';
        }

        if (!isset($this->headers['content-type'])) {
            $this->headers['content-type'] = 'application/json';
        }
    }

}
