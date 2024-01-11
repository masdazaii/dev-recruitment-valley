<?php

namespace MI\OptionPage;

defined('ABSPATH') or die('Direct access not allowed!');

use Vacancy\Import\Jobfeed\JobfeedController;
use Vacancy\Import\Xml\FlexFeedController;

class DevelopersOptionPage
{
    public function __construct()
    {
        error_log('construct');
        add_action('admin_menu', [$this, 'menuDevOnlyPage']);
        add_action('admin_post_set_vacancy_type', [$this, 'actionSetJobType']);
    }

    public function menuDevOnlyPage()
    {
        if (is_admin() && current_user_can('administrator')) {
            add_menu_page(
                $page_title = 'Dev Only Settings',
                $menu_title = 'Dev Only Settings',
                $capability = 'manage_options',
                $menu_slug  = 'dev-only-settings',
                $callback   = [$this, 'renderDevOnlyPage'],
                $icon_url = '',
                $position = 99
            );
        }
    }

    public function renderDevOnlyPage()
    {
        ob_start();
        $taxonomies = ["sector", "role", "type", "education", "working-hours", "status", "location", "experiences"];

        $availableTerms = [];
        foreach ($taxonomies as $taxonomy) {
            $args = array(
                'hide_empty' => false
            );

            $terms = get_terms($taxonomy, $args);

            foreach ($terms as $term) {
                $availableTerms[$taxonomy][] = [
                    'slug' => $term->slug,
                    'name' => $term->name
                ];
            }
        }
        include_once THEME_DIR . '/templates/admin/dev-only-template.php';
        $output = ob_get_clean();
        echo $output;
    }

    public function actionSetJobType()
    {
        $request = [
            'taxonomy'  => sanitize_text_field($_POST['dev-select-taxonomy']),
            'term'      => sanitize_text_field($_POST['dev-select-term'])
        ];

        if ($_POST['dev-select-source'] == 'flexfeed') {
            $flexfeedController = new FlexFeedController(FLEXFEED_API_URL ?? NULL);
            $response = $flexfeedController->setTerm($request);
            print('<pre>' . print_r($response, true) . '</pre>');

            $_SESSION['flash_message'] = [
                'status'    => $response['status'] == 200 ? 'success' : 'failed',
                'message'   => $response['message']
            ];
        } else if ($_POST['dev-select-source'] == 'textkernel') {
            $flexfeedController = new JobfeedController();
            $response = $flexfeedController->setTerm($request);

            $_SESSION['flash_message'] = [
                'status'    => $response['status'] == 200 ? 'success' : 'failed',
                'message'   => $response['message']
            ];
        } else {
            $_SESSION['flash_message'] = [
                'status'    => 'failed',
                'message'   => 'not available'
            ];
        }

        $url = admin_url('admin.php?page=dev-only-settings');
        // wp_redirect(esc_url(admin_url('admin.php')) . '?page=dev-only-settings&result=' . ($response['status'] == 200 ? 'success' : 'failed'));
        die();
    }
}

// Initialize
new DevelopersOptionPage();
