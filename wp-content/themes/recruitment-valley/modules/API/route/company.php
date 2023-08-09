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
            ]
        ];

        return $endpoint;
    }

    public function get()
    {
        return $this->endpoint;
    }
}
