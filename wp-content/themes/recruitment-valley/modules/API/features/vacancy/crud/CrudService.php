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
        $response["data"] = $formattedResponse;
        return ResponseHelper::build($response);
    }

    public function get( WP_REST_Request $request )
    {
        $params = $request->get_params();
        $response = $this->vacancyCrudController->get($params);

        if(isset($response["data"]))
        {
            $this->vacancyResponse->setCollection($response["data"]);
            $formattedResponse = $this->vacancyResponse->formatSingle();
            $response["data"] = $formattedResponse;
        }

        return ResponseHelper::build($response);
    }

    public function createFreeJob( WP_REST_Request $request)
    {
        $params = $request->get_params();
        $params["user_id"] = $request->user_id;
        $response = $this->vacancyCrudController->createFree($params);
        return ResponseHelper::build($response);
    }

    public function createPaidJob( WP_REST_Request $request)
    {
        $params = $request->get_params();
        $params["user_id"] = $request->user_id;
        $response = $this->vacancyCrudController->createPaid($params);
        return ResponseHelper::build($response);
    }

}