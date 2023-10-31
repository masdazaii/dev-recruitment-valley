<?php

namespace OptionPage;

defined('ABSPATH') or die('Direct access not allowed!');

class EmailSetting
{
    public function __construct()
    {
        $this->EmailOptionPage();
    }

    private function EmailOptionPage()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title'    => 'Email Settings',
                'menu_title'    => 'Email Settings',
                'menu_slug'     => 'email-settings',
                'capability'    => 'edit_posts',
                'redirect'      => false
            ]);
        }
    }
}

// Initialize
new EmailSetting();
