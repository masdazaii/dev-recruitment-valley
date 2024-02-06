<?php

namespace Route;

use Middleware\AuthMiddleware;
use Service\ChildCompanyService;
use Service\CompanyRecruiterService;

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

        $endpoint = [
            'path' => 'company-recruiter',
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
                'delete_child_company'      => [
                    'url'                   => self::uri_child_company . "/(?P<childCompany>[-\w]+)",
                    'methods'               => 'DELETE',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'callback'              => [$childCompanyService, 'delete']
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
