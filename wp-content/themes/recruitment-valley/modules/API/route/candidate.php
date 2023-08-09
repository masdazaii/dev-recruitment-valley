<?php

namespace Route;

use Candidate\Profile\ProfileController;
use Candidate\Profile\SetupProfileController;
use Candidate\Vacancy\VacancyAppliedService;
use Global\LoginService;
use Global\RegistrationService;
use Middleware\AuthMiddleware;

use WP_REST_Request;

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
        $vacancyAppliedService = new VacancyAppliedService;
        $authMiddleware = new AuthMiddleware;

        $endpoint = [
            'path' => 'candidate',
            'endpoints' =>
            [
                // 'welcome' => [
                //     'url'                   =>  'welcome',
                //     'methods'               =>  'GET',
                //     'permission_callback'   => [ $authMiddleware, 'check_token' ],
                //     'callback'              =>  [$loginService, 'login']
                // ],
                // 'register' => [
                //     'url'                   =>  'register',
                //     'methods'               =>  'POST',
                //     'permission_callback'   => '__return_true',
                //     'callback'              =>  [$registrationService, 'register']
                // ],
                // 'validate-otp' => [
                //     'url'                   => 'validate-otp',
                //     'methods'               => 'POST',
                //     'permission_calback'    => '__return_true',
                //     'callback'              => [$registrationService, 'validateOTP']
                // ],
                // 'login' => [
                //     'url'                   =>  'login',
                //     'methods'               =>  'POST',
                //     'permission_callback'   => '__return_true',
                //     'callback'              =>  [$loginService, 'login']
                // ]
                'profile-setup' => [
                    'url'                   =>  'profile/setup',
                    'methods'               =>  'POST',
                    'permission_callback'   => [$authMiddleware, 'check_token_candidate'],
                    'callback'              =>  [new ProfileController(), 'setup'],
                ],
                'apply-job' => [
                    'url'                   => 'apply',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_candidate'],
                    'callback'              => [$vacancyAppliedService, 'applyVacancy'],
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
