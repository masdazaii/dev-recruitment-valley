<?php

namespace Route;

use Global\LoginService;

class CandidateEndpoint
{
    private $endpoint = [];

    public $loginService;

    public function __construct()
    {
        $this->endpoint = $this->candidateEndpoints();
        $this->loginService = new LoginService;
    }

    public function candidateEndpoints() : array
    {
        $loginService = new LoginService;
        $endpoint = [
            'path' => 'candidate',
            'endpoints' =>
            [
                'welcome' => [
                    'url'                   =>  'welcome',
                    'methods'               =>  'GET',
                    'permission_callback'   => '__return_true',
                    'callback'              =>  [$loginService, 'login']
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