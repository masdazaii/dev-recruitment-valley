<?php

namespace Company\Vacancy;

use Constant\Message;
use Helper\ValidationHelper;
use ResponseHelper;
use Vacancy\VacancyResponse;
use WP_REST_Request;

class VacancyService
{
    public $vacancyController;
    public $vacancyResponse;
    private $_message;

    public function __construct()
    {
        $this->vacancyController = new VacancyController;
        $this->vacancyResponse = new VacancyResponse;
        $this->_message = new Message();
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

        if (!isset($response["data"])) {
            return ResponseHelper::build($response);
        }

        $this->vacancyResponse->setCollection($response["data"]);
        $response["data"] = $this->vacancyResponse->formatResponseUpdate();
        return ResponseHelper::build($response);
    }

    public function listApplicants(WP_REST_Request $request)
    {
        $params = $request->get_params();

        /** Validate : if vacancy exists */
        $validator = new ValidationHelper('vacancyApplicants', $params);

        if (!$validator->tempValidate()) {
            return ResponseHelper::build([
                "status"    => 400,
                "message"   => $this->_message->get("vacancy.not_found")
            ]);
        }

        $response = $this->vacancyController->listApplicants($params);
        return ResponseHelper::build($response);
    }

    public function showApplicants(WP_REST_Request $request)
    {
        $params = $request->get_params();

        /** Validate : if application exists */
        $validator = new ValidationHelper('vacancySingleApplicant', $params);

        if (!$validator->tempValidate()) {
            return ResponseHelper::build([
                "status"    => 400,
                "message"   => $this->_message->get("vacancy.get_application_not_found")
            ]);
        }

        $response = $this->vacancyController->showApplicants($params);
        return ResponseHelper::build($response);
    }
}
