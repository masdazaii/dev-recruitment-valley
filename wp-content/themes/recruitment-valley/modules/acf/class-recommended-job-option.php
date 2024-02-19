<?php


class RecommededJobOptionPage
{
    public function __construct()
    {
        add_action('init', [$this, 'register_option_page']);
    }

    public function register_option_page()
    {
        acf_add_options_page(array(
            'page_title'    => 'Recommended Jobs Settings',
            'menu_title'    => 'Recommended Job Settings',
            'menu_slug'     => 'recommended-job-settings',
            'capability'    => 'edit_posts',
            'redirect'      => false
        ));
    }
}

new RecommededJobOptionPage;