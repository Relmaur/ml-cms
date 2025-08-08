<?php

namespace Core\Http;

class Response
{
    public function __construct(
        public ?string $content = '',
        public int $status = 200,
        public array $headers = []
    ) {}

    public function send()
    {
        // Set status code
        http_response_code($this->status);

        // Set headers
        foreach ($this->headers as $key => $value) {
            header("${key}: {$value}");
        }

        // Echo content
        echo $this->content;
    }
}
