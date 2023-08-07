<?php

namespace Route;

use Global\LoginService;
use Global\RegistrationService;
use Global\ContactService;
use Middleware\AuthMiddleware;
use RefreshToken\RefreshTokenService;
use Vacancy\VacancyCrudService;
use Vacancy\Term\VacancyTermService;

class GlobalEndpoint
{
    private $endpoint = [];

    public $loginService;

    public function __construct()
    {
        $this->endpoint = $this->globalEndpoints();
    }

    /** Changes comes from here */
    public function globalEndpoints(): array
    {
        $loginService = new LoginService;
        $contactService = new ContactService;
        $crudVacancyService = new VacancyCrudService;
        $termVacancyService = new VacancyTermService;
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
                'contact-employer' => [
                    'url'                   => '/contact/employer',
                    'methods'               => 'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$contactService, 'sendContactEmployer']
                ],
                'contact-job-seeker' => [
                    'url'                   => '/contact/job-seeker',
                    'methods'               => 'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$contactService, 'sendContactJobSeeker']
                ],
                'vacancies' => [
                    'url'                   => 'vacancies',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$crudVacancyService, 'getAll']
                ],
                'vacancies-filter' => [
                    'url'                   => '/vacancies/filters',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$termVacancyService, 'getAll']
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
