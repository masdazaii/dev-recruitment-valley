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
        $this->loginService = new LoginService;
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
                'welcome' => [
                    'url'                   =>  'welcome',
                    'methods'               =>  'GET',
                    'permission_callback'   =>  [$authMiddleware, 'check_token'],
                    'callback'              =>  [$loginService, 'login']
                ],
                'registration' => [
                    'url'                   =>  'registration',
                    'methods'               =>  'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              =>  [$registrationService, 'registration', 'test']
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
