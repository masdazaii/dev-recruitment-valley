<?php

namespace Vacancy;

use ResponseHelper;
use WP_REST_Request;

class VacancyCrudService
{
    public $vacancyCrudController;

    public function __construct()
    {
        $this->vacancyCrudController = new VacancyCrudController;
    }

    public function create(WP_REST_Request $request)
    {
        $response = $this->vacancyCrudController->create();
        return ResponseHelper::build($response);
    }

    public function getAll( WP_REST_Request $request)
    {
        $params = $request->get_params();
        $response = $this->vacancyCrudController->getAll($params);
        return ResponseHelper::build($response);
    }
}