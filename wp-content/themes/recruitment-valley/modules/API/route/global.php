<?php

namespace Route;

use Global\LoginService;
use Global\ContactService;
use Global\OptionService;
use Middleware\AuthMiddleware;
use Vacancy\VacancyCrudService;
use Vacancy\Term\VacancyTermService;
use Candidate\Profile\FavoriteVacancyService;
use Global\PackageService;

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
        $favoriteVacancyService = new FavoriteVacancyService;
        $paymentService = new PackageService;
        $optionService = new OptionService;
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
                'contact_employer' => [
                    'url'                   => '/contact/employer',
                    'methods'               => 'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$contactService, 'sendContactEmployer']
                ],
                'contact_job_seeker' => [
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
                'vacancies_filter' => [
                    'url'                   => '/vacancies/filters',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$termVacancyService, 'getAll']
                ],
                'add_favorite' => [
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
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$paymentService, 'get']
                ],
                'create_payment_package' => [
                    'url'                   => 'package/create-payment-link',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$paymentService, 'createPaymentUrl']
                ],
                "purchase_package" => [
                    'url'                   => 'package/purchase',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$paymentService, 'purchase']
                ],
                'show_payment_package' => [
                    'url'                   => '/packages/(?P<slug>[-\w]+)',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$paymentService, 'show']
                ],

                // 'get_sector_term' => [
                //     'url'                   => '/vacancies/filters/sector',
                //     'methods'               => 'GET',
                //     'permission_callback'   => '__return_true',
                //     'callback'              => [$termVacancyService, 'getSectors']
                // ],
                // 'get_employment_type_term' => [
                //     'url'                   => '/vacancies/filters/employment-type',
                //     'methods'               => 'GET',
                //     'permission_callback'   => '__return_true',
                //     'callback'              => [$termVacancyService, 'getEmploymentType']
                // ],

                /** The 2 above is merged to 1 endpoint */
                'get_term_test' => [
                    'url'                   => '/vacancies/filters/(?P<taxonomy>[-\w]+)',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$termVacancyService, 'getSpesificTaxonomyTerm']
                ],
                'get_employees_option' => [
                    'url'                   => '/options/employees-total',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$optionService, 'getCompanyEmployeesTotal']
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
