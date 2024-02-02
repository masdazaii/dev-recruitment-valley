<?php

namespace Route;

/**
 * This file is created for the purpose of registering API endpoints
 */
defined('ABSPATH') || die("Can't access directly");

class Endpoint
{
    private $API        = "mi";
    private $version    = "v1";
    private $versionThree   = "v3";

    private $candidateEndpoint = [];
    private $companyEndpoint = [];
    private $authEndpoint = [];
    private $globalEndpoint = [];
    private $vacancyEndpoint = [];
    private $sitemapEndpoint = [];
    private $webhookEndpoint = [];
    private $companyRecruiterEndpoint       = [];
    private $childCompanyRecruiterEndpoint  = [];

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $candidateEndpoint = new CandidateEndpoint;
        $this->candidateEndpoint = $candidateEndpoint->get();

        $companyEndpoint = new CompanyEndpoint;
        $this->companyEndpoint = $companyEndpoint->get();

        $globalEndpoint = new GlobalEndpoint;
        $this->globalEndpoint = $globalEndpoint->get();

        $authEndpoint = new AuthEndpoint;
        $this->authEndpoint = $authEndpoint->get();

        $vacancyEndpoint = new VacancyEndpoint;
        $this->vacancyEndpoint = $vacancyEndpoint->get();

        $sitemapEndpoint = new SitemapEndpoint;
        $this->sitemapEndpoint = $sitemapEndpoint->get();

        $webhookEndpoint = new WebhookEndpoint;
        $this->webhookEndpoint = $webhookEndpoint->get();

        $companyRecruiterEndpoint       = new CompanyRecruiterEndpoint();
        $this->companyRecruiterEndpoint = $companyRecruiterEndpoint->get();

        // $childCompanyRecruiterEndpoint          = new Endpoint();
        // $this->childCompanyRecruiterEndpoint    = $childCompanyRecruiterEndpoint->get();

        add_action('rest_api_init', [$this, 'register_endpoint']);
    }

    /**
     * register_endpoint
     * register api endpoint
     *
     * @return void
     */
    public function register_endpoint()
    {
        $candidate = $this->candidateEndpoint;
        $company = $this->companyEndpoint;
        $global = $this->globalEndpoint;
        $auth = $this->authEndpoint;
        $vacancy = $this->vacancyEndpoint;
        $sitemap = $this->sitemapEndpoint;
        $webhook = $this->webhookEndpoint;
        $companyRecruiter       = $this->companyRecruiterEndpoint;
        $childCompanyRecruiter  = $this->childCompanyRecruiterEndpoint;

        $this->_run_list_endpoint($this->API, $this->version, $vacancy["path"], $vacancy["endpoints"]);
        $this->_run_list_endpoint($this->API, $this->version, $candidate["path"], $candidate["endpoints"]);
        $this->_run_list_endpoint($this->API, $this->version, $company["path"], $company["endpoints"]);
        $this->_run_list_endpoint($this->API, $this->version, $global["path"], $global["endpoints"]);
        $this->_run_list_endpoint($this->API, $this->version, $auth["path"], $auth["endpoints"]);
        $this->_run_list_endpoint($this->API, $this->version, $sitemap['path'], $sitemap["endpoints"]);
        $this->_run_list_endpoint($this->API, $this->version, $webhook['path'], $webhook["endpoints"]);

        /** v3 */
        $this->_run_list_endpoint($this->API, $this->versionThree, $companyRecruiter['path'], $companyRecruiter['endpoints']);
        // $this->_run_list_endpoint($this->API, $this->versionThree, $childCompanyRecruiter['path'], $childCompanyRecruiter['endpoints']);
    }

    /**
     * _run_list_endpoint
     *
     * @param  mixed $API
     * @param  mixed $version
     * @param  mixed $endpoint
     * @param  mixed $_endpoint_list
     * @return void
     */
    private function _run_list_endpoint($API, $version, $endpoint, $_endpoint_list)
    {
        $root = "{$API}/{$version}";

        if ($endpoint !== "") {
            $root .= "/{$endpoint}";
        }

        foreach ($_endpoint_list as $args) {
            register_rest_route($root, $args['url'], $args);
        }
    }
}

new Endpoint();
