<?php

namespace App\Events;

class UserRegistered
{
    public $user;

    public function __construct(object $user)
    {
        $this->user = $user;
    }
}
