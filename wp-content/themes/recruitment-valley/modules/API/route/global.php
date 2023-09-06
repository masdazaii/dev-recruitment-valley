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
use JobAlert\JobAlertService;
use Global\User\UserService;
use Sitemap\SitemapService;
use Service\ParserService;

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
        $jobAlertService = new JobAlertService;
        $userService = new UserService;
        $sitemapService = new SitemapService;
        $parserService = new ParserService;

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
                'check_if_favorite' => [
                    'url'                   => '/vacancies/check-favorite/(?P<vacancy_slug>[a-zA-Z0-9-_]+)',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_candidate'],
                    'callback'              => [$favoriteVacancyService, 'check']
                ],
                'vacancies_single' => [
                    'url'                   => 'vacancies/(?P<vacancy_slug>[a-zA-Z0-9-_]+)',
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
                'deactivate' => [
                    'url'                   => 'account/deactivate',
                    'methods'               => 'DELETE',
                    'permission_callback'   => [$authMiddleware, 'check_token'],
                    'callback'              => [$userService, 'deleteAccount']
                ],

                'delete_account_permanent' => [
                    'url'                   => 'account/delete',
                    'methods'               => 'DELETE',
                    'permission_callback'   => [$authMiddleware, 'check_token'],
                    'callback'              => [$userService, 'deleteAccountPermanent']
                ],

                'reactivate_account' => [
                    'url'                   => 'account/reactivate',
                    'methods'               => 'POST',
                    'permission_callback'   => "__return_true",
                    'callback'              => [$userService, 'reactivate']
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
                'get_terms' => [
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
                ],
                'get_employees_option_nd' => [
                    'url'                   => '/filters/employees-total',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$optionService, 'getCompanyEmployeesTotal']
                ],
                'get_terms_nd' => [
                    'url'                   => '/filters/(?P<taxonomy>[-\w]+)',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$termVacancyService, 'getSpesificTaxonomyTerm']
                ],
                'testGetAllTerm' => [
                    'url'                   => '/test-get-all-term',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$termVacancyService, 'testGetAllTerm']
                ],
                'Job_alert' => [
                    'url'                   => '/job-alert',
                    'methods'               => 'POST',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$jobAlertService, 'jobAlert']
                ],
                'root_sitemap' => [
                    'url'                   => '/sitemap',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$sitemapService, 'get']
                ],
                // 'test_xml' => [
                //     'url'                   => '/parse',
                //     'methods'               => 'GET',
                //     'permission_callback'   => '__return_true',
                //     'callback'              => [$parserService, 'testParse']
                // ]
            ]

        ];

        return $endpoint;
    }

    public function get()
    {
        return $this->endpoint;
    }
}
