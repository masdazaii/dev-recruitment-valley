<?php

namespace Route;

use Company\Vacancy\VacancyService;
use Middleware\AuthMiddleware;
use Service\ChildCompanyService;
use Service\CompanyRecruiterService;
use Vacancy\VacancyCrudService;

class CompanyRecruiterEndpoint
{
    private $endpoint = [];
    private const uri_child_company = "/child-company";

    public function __construct()
    {
        $this->endpoint = $this->companyRecruiterEndpoints();
    }

    public function companyRecruiterEndpoints(): array
    {
        $authMiddleware             = new AuthMiddleware();
        $companyRecruiterService    = new CompanyRecruiterService();
        $childCompanyService        = new ChildCompanyService();
        $vacancyCrudService         = new VacancyCrudService();
        $vacancyService             = new VacancyService();

        $endpoint = [
            'path'      => 'recruiter',
            'endpoints' =>
            [
                'setup_account'             => [
                    'url'                   => 'setup',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$companyRecruiterService, 'setup']
                ],
                'get_recruiter_profile'     => [
                    'url'                   => 'profile',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$companyRecruiterService, 'myProfile']
                ],
                'store_profile_address'     => [
                    'url'                   => 'profile/address',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$companyRecruiterService, 'storeAddress']
                ],
                'store_profile_socials'     => [
                    'url'                   => 'profile/socials',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$companyRecruiterService, 'storeSocials']
                ],
                'store_profile_information' => [
                    'url'                   => 'profile/information',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$companyRecruiterService, 'storeInformation']
                ],
                'profile_get_photo' => [
                    'url'                   => 'profile/image',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$companyRecruiterService, 'getPhoto']
                ],
                // 'delete_profile_gallery'    => [
                //     'url'                   => 'profile/gallery/(?P<id>[-\w]+)',
                //     'methods'               => 'DELETE',
                //     'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                //     'args' => [
                //         'id' => [
                //             'type'        => 'string',
                //         ]
                //     ],
                //     'callback'              =>  [$companyRecruiterService, 'deleteGalleryItem']
                // ],

                /** Child Company Endpoint - Start Here */
                'create_child_company'      => [
                    'url'                   => self::uri_child_company,
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$childCompanyService, 'createChildCompany']
                ],
                'list_child_company'        => [
                    'url'                   => self::uri_child_company,
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$childCompanyService, 'listChildCompany']
                ],
                'create-paid-vacancy-default-value' => [
                    'url'                   => self::uri_child_company . "/(?P<childCompany>[-\w]+)/create-paid-vacancy-default-value",
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$childCompanyService, 'getCreatePaidVacancyDefaultValue']
                ],
                'show_child_company'        => [
                    'url'                   => self::uri_child_company . "/(?P<childCompany>[-\w]+)",
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$childCompanyService, 'showChildCompany']
                ],
                'update_post_child_company' => [
                    'url'                   => self::uri_child_company . "/(?P<childCompany>[-\w]+)",
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$childCompanyService, 'updateChildCompany']
                ],
                'update_put_child_company'  => [
                    'url'                   => self::uri_child_company . "/(?P<childCompany>[-\w]+)",
                    'methods'               => 'PUT',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$childCompanyService, 'updateChildCompany']
                ],
                // 'delete_child_company'      => [
                //     'url'                   => self::uri_child_company . "/(?P<childCompany>[-\w]+)",
                //     'methods'               => 'DELETE',
                //     'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                //     'callback'              => [$childCompanyService, 'delete']
                // ],

                /** Vacancy */
                // 'create_free_vacancy' => [
                //     'url'                   => 'vacancy/free',
                //     'methods'               => 'POST',
                //     'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                //     'callback'              => [$vacancyCrudService, 'createFreeJob']
                // ],
                'create_paid_vacancy' => [
                    'url'                   => 'vacancy/paid',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$vacancyCrudService, 'createPaidJob']
                ],
                'update_paid_vacancy' => [
                    'url'                   => 'vacancy/paid/(?P<vacancy_id>[-\w]+)',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$vacancyCrudService, 'updatePaid']
                ],
                'vacancy_delete' => [
                    'url'                   => 'vacancy/(?P<vacancy_id>[-\w]+)',
                    'methods'               => 'DElETE',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$vacancyCrudService, 'trash']
                ],
                'list_vacancies_dashbord' => [
                    'url'                   => 'vacancies',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$vacancyService, 'getAll']
                ],
                'count_vacancy_each_status' => [
                    'url'                   => 'dashboard/vacancy/status',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$vacancyService, 'getCountbyStatus']
                ],
                'show_vacancy_dashboard' => [
                    'url'                   => 'vacancy/(?P<vacancy_id>[-\w]+)',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$vacancyService, 'get']
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
