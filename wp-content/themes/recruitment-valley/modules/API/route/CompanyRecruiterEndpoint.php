<?php

namespace Route;

use Middleware\AuthMiddleware;
use Service\CompanyRecruiterService;

class CompanyRecruiterEndpoint
{
    private $endpoint = [];

    public function __construct()
    {
        $this->endpoint = $this->companyRecruiterEndpoints();
    }

    public function companyRecruiterEndpoints(): array
    {
        $authMiddleware             = new AuthMiddleware();
        $companyRecruiterService    = new CompanyRecruiterService();

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
                'get_my_profile'            => [
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
                'delete_profile_gallery'    => [
                    'url'                   => 'profile/gallery/(?P<id>[-\w]+)',
                    'methods'               => 'DELETE',
                    'permission_callback'   => [$authMiddleware, 'authorize_company_recruiter'],
                    'args' => [
                        'id' => [
                            'type'        => 'string',
                        ]
                    ],
                    'callback'              =>  [$companyRecruiterService, 'deleteGalleryItem']
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
