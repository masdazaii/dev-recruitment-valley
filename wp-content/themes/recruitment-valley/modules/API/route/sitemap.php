<?php

namespace Route;

use Sitemap\SitemapService;

class SitemapEndpoint
{
    public $endpoint;

    public function __construct()
    {
        $this->endpoint = $this->sitemapEndpoints();
    }

    public function sitemapEndpoints()
    {
        $sitemapService = new SitemapService;

        $endpoint = [
            'path' => 'sitemap',
            'endpoints' => [
                'vacancy' => [
                    'url'                   => 'vacancy',
                    'methods'               => 'GET',
                    'permission_callback'   => "__return_true",
                    'callback'              => [$sitemapService, 'vacancies']
                ]
            ]
        ];

        return $endpoint;
    }

    public function get()
    {
        return $this->endpoint;
    }
}