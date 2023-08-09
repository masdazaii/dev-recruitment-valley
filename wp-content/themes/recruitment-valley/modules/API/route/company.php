<?php

namespace Route;

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

        $endpoint = [
            'path'  => 'company',
            'endpoints' => [
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
            ]
        ];

        return $endpoint;
    }

    public function get()
    {
        return $this->endpoint;
    }
}
