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
        echo "<pre>";
            var_dump(getenv('APP_URL'));
        echo "</pre>";
        die;
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