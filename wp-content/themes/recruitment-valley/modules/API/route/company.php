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
                'register' => [
                    'url'                   =>  'register',
                    'methods'               =>  'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              =>  [$RegistrationService, 'register']
                ],
                'validate-otp' => [
                    'url'                   => 'validate-otp',
                    'methods'               => 'POST',
                    'permission_calback'    => '__return_true',
                    'callback'              => [$RegistrationService, 'validateOTP']
                ],
                'login' => [
                    'url'                   =>  'login',
                    'methods'               =>  'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              =>  [$loginService, 'login']
                ]
            ]
        ];

        return $endpoint;
    }

    public function get()
    {
        return $this->endpoint;
    }
}
