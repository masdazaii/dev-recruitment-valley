<?php

namespace Custom\Setup;

defined("ABSPATH") or die("Direct access not allowed!");

use DateTime;
use Model\Term;
use Model\Rss;
use Vacancy\Vacancy;
use WP_Query;

class AdminEnqueue
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
    }

    public function enqueueAdminScripts()
    {
        wp_enqueue_script(
            'vacancyApprovalScript',
            THEME_URL . '/assets/js/src/wp-admin.js',
            array('jquery', 'acf-input'),
            FALSE
        );

        /** Get Vacancy Term from taxonomy 'role' */
        $termModel = new Term();

        try {
            $terms = $termModel->selectTermByTaxonomy('role', true);
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
        }

        /** RSS Ajax Data */
        $rssData = [
            'action'    => 'get_vacancies_by_company',
            'nonce'     => wp_create_nonce('get_vacancies_by_company'),
            'screen'    => 'add',
            'selectedCompany' => null,
            'selectedVacancies' => null
        ];

        // $screen = get_current_screen(); // not working in enqueue
        // if ($screen->parent_base == 'edit') {
        if (isset($_GET['action']) && $_GET['action'] == 'edit') {
            try {
                $rssController  = new Rss($_GET['post']);
                $rssVacancies   = $rssController->getRssVacancies();
                $rssCompany   = $rssController->getRssCompany();
                $selectedVacancy = [];
                if ($rssVacancies && is_array($rssVacancies)) {
                    foreach ($rssVacancies as $vacancy) {
                        $vacancyModel = new Vacancy($vacancy);

                        $postStatus = $vacancyModel->getPostStatus();
                        if ($postStatus && $vacancyModel->getPostStatus() == 'publish') {
                            $expiredAt = $vacancyModel->getExpiredAt();
                            if ($expiredAt) {
                                $today      = new \DateTime("now");
                                $expiredAt  = new \DateTime($expiredAt);
                                $dateDiff   = date_diff($today, $expiredAt)->format('%R%a');
                                if ($dateDiff >= 0) {
                                    $status = $vacancyModel->getStatus();
                                    if ($status && $status['name'] = 'open') {
                                        $selectedVacancy[] = [
                                            'id'    => $vacancy,
                                            'text'  => $vacancyModel->getTitle(),
                                            'company' => $vacancyModel->getAuthor()
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }

                $rssData['screen']              = 'edit';
                $rssData['selectedCompany']     = $rssCompany;
                $rssData['selectedVacancies']   = $selectedVacancy;
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }

        wp_localize_script(
            'vacancyApprovalScript',
            'vacanciesData',
            [
                'ajaxUrl'   => admin_url('admin-ajax.php'),
                'postUrl'   => esc_url(admin_url('admin-post.php')),
                'themeUrl'  => THEME_URL,
                'list'      => [
                    'action'    => 'handle_vacancy_list',
                    'role'      => $terms
                ],
                'approval'  => [
                    'changeRoleAction'    => 'handle_vacancy_role_change'
                ],
                'rss'       => $rssData,
                'postType'  => get_post_type()
            ]
        );

        wp_register_style('DataTables', 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css');
        wp_enqueue_style('DataTables');

        wp_register_script('DataTables', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js');
        wp_enqueue_script('DataTables');
    }
}

// Initialize
new AdminEnqueue();
