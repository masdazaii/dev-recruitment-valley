<?php

namespace Route;

use Candidate\Profile\ProfileController;
use Candidate\Profile\SetupProfileController;
use Candidate\Vacancy\VacancyAppliedService;
use Candidate\Profile\FavoriteVacancyService;
use Candidate\Profile\ProfileService;
use Global\LoginService;
use Global\User\UserService;
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
        $profileService = new ProfileService;
        $vacancyAppliedService = new VacancyAppliedService;
        $favoriteVacancyService = new FavoriteVacancyService;
        $userService = new UserService;
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
                'get_candidate' => [
                    'url'                   => 'profile',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'check_token'],
                    'callback'              => [$profileService, 'get'],
                ],
                'profile-setup' => [
                    'url'                   =>  'profile/setup',
                    'methods'               =>  'POST',
                    'permission_callback'   => [$authMiddleware, 'check_token_candidate'],
                    'callback'              =>  [new ProfileController(), 'setup'],
                ],
                'update_photo' => [
                    'url'                   =>  'profile/photo',
                    'methods'               =>  'POST',
                    'permission_callback'   => [$authMiddleware, 'check_token_candidate'],
                    'callback'              =>  [$profileService, 'updatePhoto'],
                ],
                'get_cv' => [
                    'url'                   =>  '/profile/apply-data',
                    'methods'               =>  'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_candidate'],
                    'callback'              => [$profileService, 'getApplyData'],
                ],
                'update_cv' => [
                    'url'                   => 'profile/cv',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'check_token_candidate'],
                    'callback'              => [$profileService, 'updateCv'],
                ],
                'delete_cv' => [
                    'url'                   => 'profile/cv',
                    'methods'               => 'DELETE',
                    'permission_callback'   => [$authMiddleware, 'authorize_candidate'],
                    'callback'              => [$profileService, 'destroyCV'],
                ],
                'candidate_nav' => [
                    'url'                   => '/profile/user-nav',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    // 'callback'              => [$profileService, 'getUserNav'],
                    'callback'              => [$userService, 'getUserNav'],
                ],
                'apply-job' => [
                    'url'                   => 'apply',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_candidate'],
                    'callback'              => [$vacancyAppliedService, 'applyVacancy'],
                ],
                // 'add-favorite' => [
                //     'url'                   => 'profile/jobs/favorite',
                //     'methods'               => 'POST',
                //     'permission_callback'   => [$authMiddleware, 'authorize_candidate'],
                //     'callback'              => [$favoriteVacancyService, 'addFavoriteVacancy'],
                // ],
                'list-favorite-vacancy' => [
                    'url'                   => '/profile/jobs/favorite',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_candidate'],
                    'callback'              => [$favoriteVacancyService, 'list'],
                ],
                'delete-favorite-vacancy' => [
                    'url'                   => '/profile/jobs/favorite/(?P<vacancyId>\d+)',
                    'methods'               => 'DELETE',
                    'permission_callback'   => [$authMiddleware, 'authorize_candidate'],
                    'callback'              => [$favoriteVacancyService, 'destroy'],
                ],
                'update_profile' => [
                    'url'                   =>  'profile/update',
                    'methods'               =>  'POST',
                    'permission_callback'   => [$authMiddleware, 'check_token_candidate'],
                    'callback'              =>  [$profileService, 'updateProfile'],
                ],
                'change_email_request' => [
                    'url'                   =>  'profile/change-email-request',
                    'methods'               =>  'POST',
                    // 'permission_callback'   => [$authMiddleware, 'check_token'],
                    'permission_callback'   => [$authMiddleware, 'authorize_candidate'],
                    'callback'              =>  [$profileService, 'changeEmailRequest'],
                ],
                'change_email' => [
                    'url'                   =>  'profile/change-email',
                    'methods'               =>  'POST',
                    'callback'              =>  [$profileService, 'changeEmail'],
                ],
                'change_password' => [
                    'url'                   =>  'profile/change-password',
                    'methods'               =>  'POST',
                    'permission_callback'   => [$authMiddleware, 'check_token'],
                    'callback'              =>  [$profileService, 'changePassword'],
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
