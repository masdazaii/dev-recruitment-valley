<?php

namespace Sitemap;

use ResponseHelper;
use WP;
use WP_REST_Request;

class SitemapService
{
    public $sitemapController;

    public function __construct()
    {
        $this->sitemapController = new SitemapController;
    }

    public function vacancies()
    {
        $response = $this->sitemapController->vacancy();
        return ResponseHelper::build($response);
    }

    public function getBlogs(WP_REST_Request $request)
    {
        return ResponseHelper::build([
            'status' => 503,
            'message' => 'Endpoint not available for now'
        ]);
    }

    public function getCompanies(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $response = $this->sitemapController->getCompanies($params);

        return ResponseHelper::build([
            'status' => 503,
            'message' => 'Endpoint not available for now'
        ]);
    }

    public function getVacancies(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $response = $this->sitemapController->getVacancy($params);

        return ResponseHelper::build($response);
    }

    public function getEvents(WP_REST_Request $request)
    {
        return ResponseHelper::build([
            'status' => 503,
            'message' => 'Endpoint not available for now'
        ]);
    }

    /**
     * Get all sitemaps function
     *
     * @param WP_REST_Request $request
     * @return array
     */
    public function get(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $response = $this->sitemapController->get($params);

        return ResponseHelper::build($response);
    }
}
