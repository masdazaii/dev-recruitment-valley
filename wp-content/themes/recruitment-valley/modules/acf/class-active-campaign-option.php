<?php
defined('ABSPATH') || die('Direct access not allowed');

class ActiveCampaignSettings
{
    public function __construct()
    {
        $this->ActiveCampaignPage();
    }

    private function ActiveCampaignPage()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title'    => 'Active Campaign Settings',
                'menu_title'    => 'Active Campaign Settings',
                'menu_slug'     => 'active-campaign-settings',
                'capability'    => 'edit_posts',
                'redirect'      => false
            ]);
        }
    }
}

// Initiate
new ActiveCampaignSettings;
