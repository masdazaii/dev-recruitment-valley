<?php

namespace Route;

use Global\LoginService;
use Global\RegistrationService;

class CompanyEndpoint
{
    private $endpoint = [];

    public function __construct()
    {
        $this->endpoint = $this->companyEndpoints();
    }

    public function companyEndpoints()
    {
        $loginService = new LoginService;
        $RegistrationService = new RegistrationService;

        $endpoint = [
            'path'  => 'company',
            'endpoints' => [
                // 'welcome_company' => [
                //     'url'                   =>  'welcome-company',
                //     'methods'               =>  'GET',
                //     'permission_callback'   => '__return_true',
                //     'callback'              =>  [$RegistrationService, 'register']
                // ],
            ]
        ];

        return $endpoint;
    }

    public function get()
    {
        return $this->endpoint;
    }
}
