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
                    'permission_callback'   => [$authMiddleware, 'check_token'],
                    'callback'              =>  [$vacancyCrudService, 'createFreeJob']
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
