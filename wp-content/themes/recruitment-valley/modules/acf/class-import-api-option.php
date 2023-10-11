<?php

namespace OptionPage;

defined('ABSPATH') or die('Direct access not allowed!');

class ImportApiSetting
{
    public function __construct()
    {
        $this->importApiOptionPage();
    }

    private function importApiOptionPage()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title'    => 'Import API Settings',
                'menu_title'    => 'Import API Settings',
                'menu_slug'     => 'import-api-settings',
                'capability'    => 'edit_posts',
                'redirect'      => false
            ]);
        }
    }
}

// Initialize
new ImportApiSetting();
