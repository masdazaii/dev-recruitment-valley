<?php

namespace Global;

class LoginController
{
    public function login()
    {
        return [
            "success" => true,
            "message" => "heloo world",
            "data" => [],
            "statusCode" => 200
        ];
    }
}

new LoginController();