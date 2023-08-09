<?php

namespace Route;

use Company\Profile\ProfileService;
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
        $profile = new ProfileService;

        $endpoint = [
            'path'  => 'company',
            'endpoints' => [
                'create_free_job' => [
                    'url'                   =>  'vacancy/free',
                    'methods'               =>  'POST',
                    'permission_callback'   => [$authMiddleware, 'check_token'],
                    'callback'              =>  [$vacancyCrudService, 'createFreeJob']
                ],
                'create_paid_job' => [
                    'url'                   =>  'vacancy/paid',
                    'methods'               =>  'POST',
                    'permission_callback'   => [$authMiddleware, 'check_token'],
                    'callback'              =>  [$vacancyCrudService, 'createPaidJob']
                ],
                'profile_get' => [
                    'url'                   =>  'profile',
                    'methods'               =>  'Get',
                    'permission_callback'   => [$authMiddleware, 'check_token_company'],
                    'callback'              =>  [$profile, 'get']
                ],
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
            ]
        ];

        return $endpoint;
    }

    public function get()
    {
        return $this->endpoint;
    }
}
