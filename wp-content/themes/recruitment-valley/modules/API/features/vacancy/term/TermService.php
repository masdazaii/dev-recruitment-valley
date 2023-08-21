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

    /** The 2 above is merged to 1 endpoint */
    public function getSpesificTaxonomyTerm(WP_REST_Request $request)
    {
        $params = $request->get_params();
        switch (sanitize_text_field($params['taxonomy'])):
            case 'sector':
                $params['taxonomy'] = 'sector';
                break;
            case 'employment-type':
                $params['taxonomy'] = 'type';
                break;
            case 'role':
                $params['taxonomy'] = 'type';
                break;
            case 'education':
                $params['taxonomy'] = 'education';
                break;
            case 'working-hours':
                $params['taxonomy'] = 'working-hours';
                break;
            case 'location':
                $params['taxonomy'] = 'location';
                break;
            case 'experiences':
                $params['taxonomy'] = 'experiences';
                break;
            default:
                return ResponseHelper::build([
                    'message' => 'Taxonomy didn\'t exists!',
                    'status' => 400
                ]);
        endswitch;
        $response = $this->vacancyTermController->getSpesificTaxonomyTerm($params);
        return ResponseHelper::build($response);
    }
}
