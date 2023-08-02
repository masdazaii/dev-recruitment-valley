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

    private $candidateEndpoint = [];
    private $companyEndpoint = [];
    private $globalEndpoint = [];

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

        // $this->_run_list_endpoint($this->API, $this->version, $candidate["path"], $candidate["endpoints"]);
        // $this->_run_list_endpoint($this->API, $this->version, $company["path"], $company["endpoints"]);
        $this->_run_list_endpoint($this->API, $this->version, $global["path"], $global["endpoints"]);
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

        if($endpoint !== "")
        {
            $root = "/{$endpoint}";
        }

        foreach ($_endpoint_list as $args) {
            register_rest_route($root, $args['url'], $args);
        }
    }
}

new Endpoint();
