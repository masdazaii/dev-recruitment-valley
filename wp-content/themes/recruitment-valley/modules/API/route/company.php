<?php


class ComapnyEndpoint
{
    private $companyEndoint = [];

    public function __construct()
    {

    }

    public function companyEndpoints()
    {
        $lists = [
            'welcome_company' => [
                'url'                   =>  'welcome-company',
                'methods'               =>  'GET',
                'permission_callback'   => '__return_true',
                'callback'              =>  ["hello world", 'register_mail']
            ]
        ];
    }
}