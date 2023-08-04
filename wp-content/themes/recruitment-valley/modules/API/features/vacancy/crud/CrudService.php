<?php

namespace Vacancy;

use ResponseHelper;
use WP_REST_Request;

class VacancyCrudService
{
    public $vacancyCrudController;

    public $vacancyResponse;

    public function __construct()
    {
        $this->vacancyCrudController = new VacancyCrudController;
        $this->vacancyResponse = new VacancyResponse;
    }

    // public function create(WP_REST_Request $request)
    // {
    //     $response = $this->vacancyCrudController->create();
    //     return ResponseHelper::build($response);
    // }

    public function getAll( WP_REST_Request $request )
    {
        $params = $request->get_params();
        $response = $this->vacancyCrudController->getAll($params);
        $this->vacancyResponse->setCollection($response["data"]);
        $formattedResponse = $this->vacancyResponse->format();
        return ResponseHelper::build($formattedResponse);
    }
}