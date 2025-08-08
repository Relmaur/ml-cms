<?php

namespace Core\Http;

class HtmlResponse extends Response
{

    public function __construct(string $content, int $status = 200)
    {
        parent::__construct($content, $status, ['Content-Type' => 'text/html']);
    }
}
