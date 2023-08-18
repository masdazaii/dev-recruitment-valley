<?php
defined('ABSPATH') || die('Direct access not allowed');

class StripeSettings
{
    public function __construct()
    {
        $this->CompanyOptionPage();
    }

    private function CompanyOptionPage()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title'    => 'Stripe Settings',
                'menu_title'    => 'Stripe Settings',
                'menu_slug'     => 'stripe-settings',
                'capability'    => 'edit_posts',
                'redirect'      => false
            ]);
        }
    }
}

// Initiate
new StripeSettings;
