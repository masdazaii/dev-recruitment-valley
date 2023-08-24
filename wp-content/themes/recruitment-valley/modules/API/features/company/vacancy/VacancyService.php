<?php

namespace Company\Vacancy;

use ResponseHelper;
use Vacancy\VacancyResponse;
use WP_REST_Request;

class VacancyService
{
    public $vacancyController;
    public $vacancyResponse;

    public function __construct()
    {
        $this->vacancyController = new VacancyController;
        $this->vacancyResponse = new VacancyResponse;
    }

    public function getByStatus(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $response = $this->vacancyController->getByStatus($params);
        $this->vacancyResponse->setCollection($response["data"]);
        $response["data"] = $this->vacancyResponse->formatCompany();
        return ResponseHelper::build($response);
    }

    public function getCountbyStatus(WP_REST_Request $request)
    {
        $params["user_id"] = $request['user_id'];
        $response = $this->vacancyController->getTermCount($params);
        return ResponseHelper::build($response);
    }

    public function getAll(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $params["user_id"] = $request["user_id"];
        $response = $this->vacancyController->getAll($params);
        $this->vacancyResponse->setCollection($response["data"]);
        $response["data"] = $this->vacancyResponse->formatCompany();
        return ResponseHelper::build($response);
    }

    public function get(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $params["user_id"] = $request["user_id"];
        $response = $this->vacancyController->get($request);
        $this->vacancyResponse->setCollection($response["data"]);
        $response["data"] = $this->vacancyResponse->formatResponseUpdate();
        return ResponseHelper::build($response);
    }
}
