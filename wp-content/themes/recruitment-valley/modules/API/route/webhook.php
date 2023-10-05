<?php

namespace Route;

use Global\PackageService;

class WebhookEndpoint
{
    public $endpoint;

    public function __construct()
    {
        $this->endpoint = $this->webhookEndpoints();
    }

    public function webhookEndpoints()
    {
        $packageService = new PackageService;

        $endpoint = [
            'path' => 'webhook',
            'endpoints' => [
                'vacancy' => [
                    'url'                   => 'payment-success',
                    'methods'               => 'POST',
                    'permission_callback'   => "__return_true",
                    'callback'              => [$packageService, 'onWebhookTrigger']
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