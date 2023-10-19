<?php

namespace Route;

use Company\Profile\ProfileService;
use Company\Vacancy\VacancyController;
use Company\Vacancy\VacancyService;
use Global\LoginService;
use Global\RegistrationService;
use Middleware\AuthMiddleware;
use Vacancy\VacancyCrudService;

class CompanyEndpoint
{
    private $endpoint = [];

    public function __construct()
    {
        $this->endpoint = $this->companyEndpoints();
    }

    public function companyEndpoints()
    {
        $authMiddleware = new AuthMiddleware;
        $vacancyCrudService = new VacancyCrudService;
        $vacancyService = new VacancyService;
        $profile = new ProfileService;

        $endpoint = [
            'path'  => 'company',
            'endpoints' => [
                'repost_job' => [
                    'url'                   =>  'vacancy/repost/(?P<vacancy_id>[-\w]+)',
                    'methods'               =>  'POST',
                    'permission_callback'   =>  [$authMiddleware, 'authorize_company'],
                    'callback'              =>  [$vacancyCrudService, 'repostJob']
                ],
                'create_free_job' => [
                    'url'                   =>  'vacancy/free',
                    'methods'               =>  'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              =>  [$vacancyCrudService, 'createFreeJob']
                ],
                'create_paid_job' => [
                    'url'                   =>  'vacancy/paid',
                    'methods'               =>  'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              =>  [$vacancyCrudService, 'createPaidJob']
                ],
                'profile_get' => [
                    'url'                   =>  'profile',
                    'methods'               =>  'Get',
                    // 'permission_callback'   => [$authMiddleware, 'check_token_company'],
                    'permission_callback'   => [$authMiddleware, 'authorize_company'], // Changed line
                    'callback'              =>  [$profile, 'get']
                ],
                // 'profile_post_detail' => [
                //     'url'                   => '/profile/detail',
                //     'methods'               => 'POST',
                //     'permission_callback'   => [$authMiddleware, 'check_token_company'],
                //     'args' => [
                //         'id' => [
                //             'type'        => 'string',
                //         ]
                //     ],
                //     'callback'              =>  [$profile, 'post_detail']
                // ],
                'profile_post_address' => [
                    'url'                   => 'profile/address',
                    'methods'               => 'Post',
                    'permission_callback'   => [$authMiddleware, 'check_token_company'],
                    'callback'              =>  [$profile, 'post_address']
                ],
                'profile_post_socials' => [
                    'url'                   => 'profile/socials',
                    'methods'               => 'Post',
                    'permission_callback'   => [$authMiddleware, 'check_token_company'],
                    'callback'              =>  [$profile, 'post_socials']
                ],
                'profile_post_information' => [
                    'url'                   => 'profile/information',
                    'methods'               => 'Post',
                    'permission_callback'   => [$authMiddleware, 'check_token_company'],
                    'callback'              =>  [$profile, 'post_information']
                ],
                'profile_delete_gallery' => [
                    'url'                   => 'profile/gallery/(?P<id>[-\w]+)',
                    'methods'               => 'DELETE',
                    'permission_callback'   => [$authMiddleware, 'check_token_company'],
                    'args' => [
                        'id' => [
                            'type'        => 'string',
                        ]
                    ],
                    'callback'              =>  [$profile, 'delete_gallery']
                ],
                'create-paid-job-default-value' => [
                    'url'                   => '/create-paid-job-default-value',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$profile, 'getCreatePaidJobDefaultValue']
                ],
                'status_vacancy_count' => [
                    'url'                   => 'dashboard/vacancy/status',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$vacancyService, 'getCountbyStatus']
                ],
                'vacancies' => [
                    'url'                   => 'vacancies',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$vacancyService, 'getAll']
                ],
                // 'vacancy_update' => [
                //     'url'                   => 'vacancy/(?P<vacancy_id>[-\w]+)',
                //     'methods'               => 'POST',
                //     'permission_callback'   => [$authMiddleware, 'authorize_company'],
                //     'callback'              => [$vacancyCrudService, 'update']
                // ],
                'vacancy_update_free' => [
                    'url'                   => 'vacancy/free/(?P<vacancy_id>[-\w]+)',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$vacancyCrudService, 'updateFree']
                ],
                'vacancy_update_paid' => [
                    'url'                   => 'vacancy/paid/(?P<vacancy_id>[-\w]+)',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$vacancyCrudService, 'updatePaid']
                ],
                'vacancy_delete' => [
                    'url'                   => 'vacancy/(?P<vacancy_id>[-\w]+)',
                    'methods'               => 'DElETE',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$vacancyCrudService, 'trash']
                ],
                'setup-company-profile' => [
                    'url'                   => '/profile/setup',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$profile, 'setup'],
                ],
                'profile_update_photo' => [
                    'url'                   => '/profile/image',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$profile, 'updatePhoto']
                ],
                'profile_get_photo' => [
                    'url'                   => 'profile/image',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$profile, 'getPhoto']
                ],
                'profile_update_detail' => [
                    'url'                   => 'profile/detail',
                    'methods'               => 'POST',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$profile, 'updateDetail']
                ],
                "get_credit" => [
                    'url'                   => 'credit',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$profile, 'getCredit']
                ],
                "get_vacancy_applicants" => [
                    'url'                   => 'vacancy/applicants/(?P<vacancy>[-\w]+)', // last uri segment is meant to be vacancy id
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$vacancyService, 'listApplicants']
                ],
                "get_vacancy_applications" => [
                    'url'                   => 'vacancy/applications/(?P<application>[-\w]+)', // last uri segment is meant to be application id
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
                    'callback'              => [$vacancyService, 'showApplicants']
                ],
                "get_vacancy_detail" => [
                    'url'                   => 'vacancy/(?P<vacancy_id>[-\w]+)',
                    'methods'               => 'GET',
                    'permission_callback'   => [$authMiddleware, 'authorize_company'],
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
