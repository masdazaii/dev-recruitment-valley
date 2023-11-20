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
use Vacancy\Import\ImportService;
use Global\NotificationService;
use Global\CouponService;
use Global\Rss\RssService;
use Global\FAQ\FaqService;

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
        $importService = new ImportService;
        $notificationService = new NotificationService;
        $couponService = new CouponService;
        $rssService = new RssService;
        $faqService = new FaqService;

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
                    // 'permission_callback'   => [$authMiddleware, 'authorize_company'], // Disable feedback FE 12 Nov 2023
                    'permission_callback'   => '__return_true',
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
                'Job_alert_unsubscribe' => [
                    'url'                   => 'job-alert/unsubscribe',
                    'methods'               => 'DELETE',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$jobAlertService, 'unsubscribe']
                ],
                'root_sitemap' => [
                    'url'                   => '/sitemap',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$sitemapService, 'get']
                ],
                'list_notifications' => [
                    'url'                   => '/notifications',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'check_token'],
                    'callback'              => [$notificationService, 'list']
                ],
                'check_unread'  => [
                    'url'                   => '/notifications/check-unread',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_both'],
                    'callback'              => [$notificationService, 'countUnread']
                ],
                'read_all_notifications' => [
                    'url'                   => '/notifications/read-all',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_both'],
                    'callback'              => [$notificationService, 'readAll']
                ],
                'read_notification' => [
                    'url'                   => '/notification/read/(?P<notif_id>[-\w]+)',
                    'methods'               => 'PUT',
                    'permission_callback'   => [$authMiddleware, 'check_token'],
                    'callback'              => [$notificationService, 'read']
                ],
                'delete_notification' => [
                    'url'                   => '/notifications/(?P<notif_id>[-\w]+)',
                    'methods'               => 'DELETE',
                    'permission_callback'   => [$authMiddleware, 'authorize_both'],
                    'callback'              => [$notificationService, 'delete']
                ],
                // import endpoint test start here,
                'flexfeed_import' => [
                    'url'                   => 'import/flexfeed',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$importService, 'flexFeed']
                ],
                'nationale_vacature_bank_import' => [
                    'url'                   => 'import/nationale-vacature-bank',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$importService, 'nationaleVacatureBank']
                ],
                // This is live version of nationale_vacature_bank_import
                'jobfeed_import' => [
                    'url'                   => 'import/jobfeed',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$importService, 'jobfeedImport']
                ],
                'jobfeed_expire' => [
                    'url'                   => 'import/jobfeed/expire',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$importService, 'jobfeedExpire']
                ],
                'flexfeed_export' => [
                    'url'                   => 'export/flexfeed',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$crudVacancyService, 'export']
                ],
                'list_coupon' => [
                    'url'                   => 'coupons',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$couponService, 'list']
                ],
                'validate_coupon' => [
                    'url'                   => 'coupons/validate',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$couponService, 'apply']
                ],
                'get_rss' => [
                    'url'                   => 'rss/vacancy',
                    'method'                => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$rssService, 'get']
                ],
                'show_single_rss' => [
                    'url'                   => 'rss/vacancy/(?P<rss>[-\w]+)',
                    'method'                => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$rssService, 'show']
                ],
                'list_faq'  => [
                    'url'                   => '/faq',
                    'method'                => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$faqService, 'list']
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
