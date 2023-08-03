<?php

namespace Route;

use Vacancy\VacancyCrudService;

class VacancyEndpoint
{
    private $endpoint = [];

    public function __construct()
    {
        $this->endpoint = $this->vacancyCrudEndpoints();
    }

    public function vacancyCrudEndpoints(): array
    {
        $vacancyCrudService = new VacancyCrudService;

        $endpoint = [
            'path' => '',
            'endpoints' =>
            [
                'vacancies' => [
                    'url'                   =>  'vacancies',
                    'methods'               =>  'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              =>  [$vacancyCrudService, 'create']
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
