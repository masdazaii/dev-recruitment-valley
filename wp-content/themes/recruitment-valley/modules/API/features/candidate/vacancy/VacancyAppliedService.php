<?php

namespace Candidate\Vacancy;

use ResponseHelper;
use WP_REST_Request;

class VacancyAppliedService
{
    public $vacancyAppliedController;

    public function __construct()
    {
        $this->vacancyAppliedController = new VacancyAppliedController;
    }

    public function applyVacancy(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $response = $this->vacancyAppliedController->applyVacancy($params);
        return ResponseHelper::build($response);
    }
}
