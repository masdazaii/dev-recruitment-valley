<?php

namespace Route;

use Global\LoginService;
use Global\RegistrationService;
use Global\User\UserService;
use Candidate\Profile\ProfileService;
use Middleware\AuthMiddleware;
use RefreshToken\RefreshTokenService;

class AuthEndpoint
{
    private $endpoint = [];

    public $loginService;

    public function __construct()
    {
        $this->endpoint = $this->authEndpoints();
    }

    public function authEndpoints(): array
    {
        $loginService = new LoginService;
        $registrationService = new RegistrationService;
        $refreshTokenService = new RefreshTokenService;
        $userService = new UserService;
        $candidateProfileService = new ProfileService;
        $authMiddleware = new AuthMiddleware;

        $endpoint = [
            'path' => 'auth',
            'endpoints' =>
            [
                'welcome' => [
                    'url'                   => 'welcome',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'check_token'],
                    'callback'              => [$loginService, 'login']
                ],
                'register' => [
                    'url'                   => 'register',
                    'methods'               => 'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$registrationService, 'register']
                ],
                'validate-otp' => [
                    'url'                   => 'validate-otp',
                    'methods'               => 'POST',
                    'permission_calback'    => '__return_true',
                    'callback'              => [$registrationService, 'validateOTP']
                ],
                'login' => [
                    'url'                   => 'login',
                    'methods'               => 'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$loginService, 'login']
                ],
                'logout' => [
                    'url'                   => 'logout',
                    'methods'               => 'POST',
                    // 'permission_callback'   => [$authMiddleware, "check_token"],
                    'permission_callback'   => [$authMiddleware, "logout_handle"],
                    'callback'              => [$loginService, 'logout']
                ],
                'forgot-password' => [
                    'url'                   => 'forgot-password',
                    'methods'               => 'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$loginService, 'forgotPassword']
                ],
                'reset-password' => [
                    'url'                   => 'reset-password',
                    'methods'               => 'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$loginService, 'resetPassword']
                ],
                'refresh-token' => [
                    'url'                   => 'refresh-token',
                    'methods'               => 'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$refreshTokenService, 'refresh']
                ],
                'resend-otp' => [
                    'url'                   => 'resend-otp',
                    'methods'               => 'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$registrationService, 'resendOTP']
                ],
                'change-email-request' => [
                    'url'                   => '/change-email-request',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_both'],
                    'callback'              => [$candidateProfileService, 'changeEmailRequest'],
                ],
                'change_email' => [
                    'url'                   => '/change-email',
                    'methods'               => 'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$candidateProfileService, 'changeEmail'],
                ],
                'change_password' => [
                    'url'                   => '/change-password',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'check_token'],
                    'callback'              => [$candidateProfileService, 'changePassword'],
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
