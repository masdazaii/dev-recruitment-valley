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
                ],
                // 'blog' => [
                //     'url'                   => '/blogs',
                //     'methods'               => 'GET',
                //     'permission_callback'   => '__return_true',
                //     'callback'              => [$sitemapService, 'getBlogs']
                // ],
                'company' => [
                    'url'                   => '/companies',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$sitemapService, 'getCompanies']
                ],
                // 'event' => [
                //     'url'                   => '/events',
                //     'methods'               => 'GET',
                //     'permission_callback'   => '__return_true',
                //     'callback'              => [$sitemapService, 'getEvents']
                // ],
                'root_sitemap' => [
                    'url'                   => '',
                    'methods'               => 'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              => [$sitemapService, 'get']
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
