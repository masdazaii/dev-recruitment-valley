<?php

namespace Vacancy;

use ResponseHelper;
use WP_REST_Request;

class VacancyCrudService
{
    public $vacancyServiceController;

    public function __construct()
    {
        $this->vacancyServiceController = new VacancyCrudController;
    }

    public function create(WP_REST_Request $request)
    {
        $response = $this->vacancyServiceController->create();
        return ResponseHelper::build($response);
    }
}