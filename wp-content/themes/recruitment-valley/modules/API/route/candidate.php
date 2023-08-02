<?php

namespace Route;

use Global\LoginService;
use Global\RegistrationService;

class CandidateEndpoint
{
    private $endpoint = [];

    public $loginService;

    public function __construct()
    {
        $this->endpoint = $this->candidateEndpoints();
    }

    public function candidateEndpoints(): array
    {
        $loginService = new LoginService;
        $RegistrationService = new RegistrationService;

        $endpoint = [
            'path' => 'candidate',
            'endpoints' =>
            [
                // 'welcome' => [
                //     'url'                   =>  'welcome',
                //     'methods'               =>  'GET',
                //     'permission_callback'   => '__return_true',
                //     'callback'              =>  [$loginService, 'login']
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
