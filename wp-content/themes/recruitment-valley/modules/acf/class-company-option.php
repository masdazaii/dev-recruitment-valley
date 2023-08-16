<?php
defined('ABSPATH') || die('Direct access not allowed');

class CompanySettings
{
    public function __construct()
    {
        $this->CompanyOptionPage();
    }

    private function CompanyOptionPage()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title'    => 'Company Settings',
                'menu_title'    => 'Company Settings',
                'menu_slug'     => 'company-settings',
                'capability'    => 'edit_posts',
                'redirect'      => false
            ]);
        }
    }
}

// Initiate
new CompanySettings();
