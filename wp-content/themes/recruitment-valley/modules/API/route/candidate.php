<?php

namespace Route;

use Global\LoginService;
use Global\RegistrationService;
use Middleware\AuthMiddleware;

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
        $registrationService = new RegistrationService;
        $authMiddleware = new AuthMiddleware;

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
                    'callback'              =>  [$registrationService, 'register']
                ],
                'validate-otp' => [
                    'url'                   => 'validate-otp',
                    'methods'               => 'POST',
                    'permission_calback'    => '__return_true',
                    'callback'              => [$registrationService, 'validateOTP']
                ],
                'login' => [
                    'url'                   =>  'login',
                    'methods'               =>  'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              =>  [$loginService, 'login']
                ],
                'logout' => [
                    'url'                   =>  'logout',
                    'methods'               =>  'POST',
                    // 'permission_callback'   => '__return_true',
                    'permission_callback'   => [$authMiddleware, 'check_token'],
                    'callback'              => [$loginService, 'logout']
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
