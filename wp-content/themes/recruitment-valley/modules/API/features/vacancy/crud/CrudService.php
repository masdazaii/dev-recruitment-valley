<?php

namespace Vacancy;

use Request\CandidateVacanciesRequest;
use Request\CreateFreeJobRequest;
use Request\CreatePaidJobRequest;
use Request\SingleVacancyRequest;
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

    public function getAll(WP_REST_Request $request)
    {
        $getAllRequest = new CandidateVacanciesRequest( $request );
        if(!$getAllRequest->validate())
        {
            $errors = $getAllRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        $params = $getAllRequest->getData();
        $response = $this->vacancyCrudController->getAll($params);
        $this->vacancyResponse->setCollection($response["data"]);
        $formattedResponse = $this->vacancyResponse->format();
        $response["data"] = $formattedResponse;
        return ResponseHelper::build($response);
    }

    public function get(WP_REST_Request $request)
    {
        $singleVacancyRequest = new SingleVacancyRequest($request);
        if(!$singleVacancyRequest->validate())
        {
            $errors = $singleVacancyRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        $singleVacancyRequest->sanitize();
        $params = $singleVacancyRequest->getData();
        $response = $this->vacancyCrudController->get($params);

        if (isset($response["data"])) {
            $this->vacancyResponse->setCollection($response["data"]);
            $formattedResponse = $this->vacancyResponse->formatSingle();
            $response["data"] = $formattedResponse;
        }

        return ResponseHelper::build($response);
    }

    public function createFreeJob(WP_REST_Request $request)
    {
        $createFreeJobRequest = new CreateFreeJobRequest($request);
        if(!$createFreeJobRequest->validate())
        {
            $errors = $createFreeJobRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        
        echo '<pre>';
        var_dump($createFreeJobRequest->getData());
        echo '</pre>';die;

        $createFreeJobRequest->sanitize();

        $params = $createFreeJobRequest->getData();
        $params["user_id"] = $request->user_id;
        $response = $this->vacancyCrudController->createFree($params);
        return ResponseHelper::build($response);
    }

    public function createPaidJob( WP_REST_Request $request)
    {
        $createPaidJobRequest = new CreatePaidJobRequest($request);
        if(!$createPaidJobRequest->validate())
        {
            $errors = $createPaidJobRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        echo '<pre>';
        var_dump($createPaidJobRequest->getData());
        echo '</pre>';

        $createPaidJobRequest->sanitize();

        echo '<pre>';
        var_dump($createPaidJobRequest->getData());
        echo '</pre>';die;

        $params = $createPaidJobRequest->getData();
        $params["user_id"] = $request->user_id;
        $response = $this->vacancyCrudController->createPaid($params);
        return ResponseHelper::build($response);
    }

}
