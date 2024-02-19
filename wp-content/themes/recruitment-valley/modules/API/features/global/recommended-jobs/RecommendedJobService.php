<?php

use Vacancy\VacancyResponse;

class RecommendedJobService
{
    public $recommendedJobController;
    public $vacancyResponse;

    public function __construct()
    {
        $this->recommendedJobController = new RecommendedJobController;
        $this->vacancyResponse = new VacancyResponse;
    }

    public function lists( WP_REST_Request $request )
    {
        $recommendedJob = $this->recommendedJobController->get( $request );

        $this->vacancyResponse->setCollection($recommendedJob["data"]);
        $formattedRecommendedJob = $this->vacancyResponse->format();

        $recommendedJob["data"] = $formattedRecommendedJob;

        return ResponseHelper::build($recommendedJob);
    }

}