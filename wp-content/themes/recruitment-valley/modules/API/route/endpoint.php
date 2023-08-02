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

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $candidateEndpoint = new CandidateEndpoint;
        $this->candidateEndpoint = $candidateEndpoint->get();

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
        $this->_run_list_endpoint($this->API, $this->version, $candidate["path"], $candidate["endpoints"]);
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
        $root = "{$API}/{$version}/{$endpoint}";
        foreach ($_endpoint_list as $args) {
            register_rest_route($root, $args['url'], $args);
        }
    }
}

new Endpoint();
