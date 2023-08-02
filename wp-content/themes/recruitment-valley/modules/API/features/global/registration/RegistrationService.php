<?php

namespace Global;

use ResponseHelper;

class RegistrationService
{
    private $registrationController;

    public function __construct()
    {
        $this->registrationController = new RegistrationController;
    }

    public function registration()
    {
        $response = $this->registrationController->registration();
        return ResponseHelper::build($response);
    }
}
