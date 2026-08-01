<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : Response.php
| Autor   : Roberto + ChatGPT
| Versión : 0.1.0
|--------------------------------------------------------------------------
*/

namespace App\Core;

final class Response
{
    private int $statusCode = 200;

    private array $headers = [];

    private string $body = '';

    public function status(int $status): self
    {
        $this->statusCode = $status;

        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function body(string $content): self
    {
        $this->body = $content;

        return $this;
    }

    public function json(array $data): self
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        $this->body = $json;
        $this->headers['Content-Type'] = 'application/json';

        return $this;
    }

    public function redirect(string $url, int $status = 302): self
    {
        $this->statusCode = $status;
        $this->headers['Location'] = $url;

        return $this;
    }

    public function download(string $file, ?string $filename = null): self
    {
        $this->statusCode = 200;
        $this->headers['Content-Type'] = 'application/octet-stream';
        $this->headers['Content-Disposition'] = 'attachment; filename="' . ($filename ?? basename($file)) . '"';

        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->body;
    }

    public function getStatus(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
