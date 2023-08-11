<?php

namespace Vacancy\Term;

use ResponseHelper;
use WP_REST_Request;

class VacancyTermService
{
    public $vacancyTermController;

    public $vacancyTermResponse;

    public function __construct()
    {
        $this->vacancyTermController = new VacancyTermController;
        $this->vacancyTermResponse = new vacancyTermResponse;
    }

    public function getAll(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $response = $this->vacancyTermController->getAllTerm($params);
        $formattedData = $this->vacancyTermResponse->format($response['data']);
        $response['data'] = $formattedData;
        return ResponseHelper::build($response);
    }
}
