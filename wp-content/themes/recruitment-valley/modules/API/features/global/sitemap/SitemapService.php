<?php

namespace Sitemap;

use ResponseHelper;

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
}