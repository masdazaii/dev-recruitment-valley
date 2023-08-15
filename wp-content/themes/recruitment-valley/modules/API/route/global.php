<?php

namespace Route;

use Global\LoginService;
use Global\RegistrationService;
use Global\ContactService;
use Global\PaymentService;
use Middleware\AuthMiddleware;
use RefreshToken\RefreshTokenService;
use Vacancy\VacancyCrudService;
use Vacancy\Term\VacancyTermService;
use Candidate\Profile\FavoriteVacancyService;

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
        $favoriteVacancyService = new FavoriteVacancyService;
        $paymentService = new PaymentService;

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
                ],
                'add-favorite' => [
                    'url'                   => '/vacancies/favorite',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_candidate'],
                    'callback'              => [$favoriteVacancyService, 'addFavoriteVacancy'],
                ],
                'vacancies_single' => [
                    'url'                   => 'vacancies/(?P<vacancy_slug>[a-zA-Z0-9-]+)',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$crudVacancyService, 'get']
                ],
                'get_payment_package' => [
                    'url'                   => '/packages',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$paymentService, 'get']
                ],
                'show_payment_package' => [
                    'url'                   => '/packages/(?P<slug>[-\w]+)',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$paymentService, 'show']
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
