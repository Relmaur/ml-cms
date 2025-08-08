<?php

namespace Core\Http;

use Core\Http\Response;

class RedirectResponse extends Response
{
    public function __construct(string $url)
    {
        // A redirect response  has no content and a 302 statud code
        parent::__construct(null, 302, ['Location' => $url]);
    }
}
