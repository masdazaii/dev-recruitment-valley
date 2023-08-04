<?php

namespace Route;

use Global\LoginService;
use Global\RegistrationService;
use Global\ContactService;
use Middleware\AuthMiddleware;
use RefreshToken\RefreshTokenService;

class GlobalEndpoint
{
    private $endpoint = [];

    public $loginService;

    public function __construct()
    {
        $this->endpoint = $this->globalEndpoints();
    }

    /** Anggit goes here */
    // public function globalEndpoints(): array
    // {
    //     $loginService = new LoginService;
    //     $registrationService = new RegistrationService;
    //     $refreshTokenService = new RefreshTokenService;
    //     $authMiddleware = new AuthMiddleware;

    //     $endpoint = [
    //         'path' => 'auth',
    //         'endpoints' =>
    //         [
    //             'welcome' => [
    //                 'url'                   => 'welcome',
    //                 'methods'               => 'GET',
    //                 'permission_callback'   => [$authMiddleware, 'check_token'],
    //                 'callback'              => [$loginService, 'login']
    //             ],
    //             'register' => [
    //                 'url'                   => 'register',
    //                 'methods'               => 'POST',
    //                 'permission_callback'   => '__return_true',
    //                 'callback'              => [$registrationService, 'register']
    //             ],
    //             'validate-otp' => [
    //                 'url'                   => 'validate-otp',
    //                 'methods'               => 'POST',
    //                 'permission_calback'    => '__return_true',
    //                 'callback'              => [$registrationService, 'validateOTP']
    //             ],
    //             'login' => [
    //                 'url'                   => 'login',
    //                 'methods'               => 'POST',
    //                 'permission_callback'   => '__return_true',
    //                 'callback'              => [$loginService, 'login']
    //             ],
    //             'logout' => [
    //                 'url'                   => 'logout',
    //                 'methods'               => 'POST',
    //                 'permission_callback'   => [$authMiddleware, "check_token"],
    //                 'callback'              => [$loginService, 'logout']
    //             ],
    //             'forgot-password' => [
    //                 'url'                   => 'forgot-password',
    //                 'methods'               => 'POST',
    //                 'permission_callback'   => '__return_true',
    //                 'callback'              => [$loginService, 'forgotPassword']
    //             ],
    //             'reset-password' => [
    //                 'url'                   => 'reset-password',
    //                 'methods'               => 'POST',
    //                 'permission_callback'   => '__return_true',
    //                 'callback'              => [$loginService, 'resetPassword']
    //             ],
    //             'refresh-token' => [
    //                 'url'                   => 'refresh-token',
    //                 'methods'               => 'POST',
    //                 'permission_callback'   => '__return_true',
    //                 'callback'              => [$refreshTokenService, 'refresh']
    //             ]
    //         ]

    //     ];

    //     return $endpoint;
    // }

    /** Changes comes from here */
    public function globalEndpoints(): array
    {
        $loginService = new LoginService;
        $contactService = new ContactService;
        $registrationService = new RegistrationService;
        $refreshTokenService = new RefreshTokenService;
        $authMiddleware = new AuthMiddleware;

        $endpoint = [
            'path' => '',
            'endpoints' =>
            [
                'welcome' => [
                    'url'                   => 'welcome',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'check_token'],
                    'callback'              => [$loginService, 'login']
                ],
                'contactEmployer' => [
                    'url'                   => '/contact/employer',
                    'methods'               => 'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$contactService, 'sendContactEmployer']
                ],
                'contactJobSeeker' => [
                    'url'                   => '/contact/job-seeker',
                    'methods'               => 'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$contactService, 'sendContactJobSeeker']
                ],
            ]

        ];

        return $endpoint;
    }

    public function get()
    {
        return $this->endpoint;
    }
}
