<?php

use Integration\ActiveCampaign\ActiveCampaign;

defined('ABSPATH') || die('Direct access not allowed');

class AwsSettings
{
    public function __construct()
    {
        $this->AwsPage();
        // add_filter('acf/load_field/name=active_campaign_tags', [$this, "loadTagsOnActiveCampaign"]);
    }

    private function AwsPage()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title'    => 'Aws Settings',
                'menu_title'    => 'Aws Settings',
                'menu_slug'     => 'aws-settings',
                'capability'    => 'edit_posts',
                'redirect'      => false
            ]);
        }
    }

}

// Initiate
new AwsSettings;