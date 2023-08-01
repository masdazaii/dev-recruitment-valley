<?php

namespace Global;

use ResponseHelper;

class LoginService
{
    private $loginController;

    public function __construct()
    {
        $this->loginController = new LoginController;
    }

    public function login()
    {
        $response = $this->loginController->login(); 
        return ResponseHelper::build($response);
    }
}