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
        add_filter('script_loader_tag', [$this, 'filterScriptLoaderTagHandle'], 10, 3);
    }

    public function enqueueAdminScripts()
    {
        wp_enqueue_script(
            'vacancyApprovalScript',
            THEME_URL . '/assets/js/src/wp-admin.js',
            array('jquery', 'acf-input'),
            FALSE
        );

        /** Prepare data for approval screen */
        if (isset($_GET['page']) && $_GET['page'] == 'import-approval') {
            /** Get Vacancy Term from taxonomy 'role' */
            $termModel = new Term();

            try {
                $roles = $termModel->selectTermByTaxonomy('role', true);
                $sectors = $termModel->selectTermByTaxonomy('sector', true);

                $options = [];

                /** Set option for approval screens */
                foreach ($roles as $role) {
                    $options['role'][$role['term_id']] = [
                        'id' => $role['term_id'], // if you want to change role value to slug, make sure to change the ajax vacancy list and validation to slug also.
                        'text' => $role['name']
                    ];
                }

                foreach ($sectors as $sector) {
                    $options['sector'][$sector['term_id']] = [
                        'id' => $sector['term_id'],
                        'text' => $sector['name']
                    ];
                }
            } catch (\Exception $exception) {
                error_log($exception->getMessage());
            }
        } else {
            $options = [];
        }

        /** RSS Ajax Data */
        $rssData = [
            'action'    => 'get_vacancies_for_rss',
            'nonce'     => wp_create_nonce('get_vacancies_for_rss'),
            'screen'    => 'add',
            'selectedCompany' => null,
            'selectedLanguage' => null,
            'selectedVacancies' => null
        ];

        $screen = get_current_screen();

        /** GET Rss single data */
        if (get_post_type() == 'rss' && isset($_GET['action']) && $_GET['action'] == 'edit') {
            try {
                $rssModel  = new Rss($_GET['post']);

                $selectedLanguage   = $rssModel->getRssLanguage();
                $selectedCompany    = $rssModel->getRssCompany();
                $selectedPaidStatus = $rssModel->getRssPaidStatus();

                $rssVacancies   = $rssModel->getRssVacancies();
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
                                            'company'   => $vacancyModel->getAuthor(),
                                            'language'  => $vacancyModel->getLanguage() !== null ? $vacancyModel->getLanguage()['value'] : null,
                                            'isPaid'    => $vacancyModel->getIsPaid()
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }

                $rssData['screen']              = 'edit';
                $rssData['selectedCompany']     = $selectedCompany;
                $rssData['selectedLanguage']    = $selectedLanguage;
                $rssData['selectedPaidStatus']  = $selectedPaidStatus;
                $rssData['selectedVacancies']   = $selectedVacancy;
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }

        $adminVacancyData = null;
        if (get_post_type() == 'vacancy') {
            if ($screen->action == 'add' || $screen->action == 'edit' || (isset($_GET['action']) && $_GET['action'] == 'edit')) {
                $adminVacancyData = [
                    'optionCityAction' => 'handle_city_list',
                    'countryData'   => \Constant\LocationConstant::countries('', 'all-remap', true, 'value')
                ];
            }

            if ($screen->action == 'edit' || (isset($_GET['action']) && $_GET['action'] == 'edit')) {

                try {
                    $vacancyModel = new Vacancy($_GET['post']);

                    $adminVacancyData['screen']              = 'edit';
                    $adminVacancyData['selectedCustomCompanyCity']  = $vacancyModel->getCustomCompanyCity('value');
                    $adminVacancyData['selectedVacancyCity'] = $vacancyModel->getCity('value');
                } catch (\Exception $e) {
                    error_log($e->getMessage());
                }
            }
        }

        $screenAction = $screen->action;
        if (!$screenAction || empty($screenAction)) {
            $screenAction = isset($_GET['action']) ? $_GET['action'] : '';
        }

        wp_localize_script(
            'vacancyApprovalScript',
            'adminData',
            [
                'ajaxUrl'   => admin_url('admin-ajax.php'),
                'postUrl'   => esc_url(admin_url('admin-post.php')),
                'themeUrl'  => THEME_URL,
                'list'      => [
                    'action'    => 'handle_vacancy_list'
                ],
                'approval'  => [
                    'options' => $options,
                    'changeRoleAction'    => 'handle_vacancy_role_change',
                    'changeSectorAction'    => 'handle_vacancy_sector_change',
                    'bulkAction'    => 'handle_vacancy_bulk_action'
                ],
                'vacancies' => $adminVacancyData,
                'rss'       => $rssData,
                'postType'  => get_post_type(),
                'screenAction' => $screenAction
            ]
        );

        wp_register_style('DataTables', 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css');
        wp_enqueue_style('DataTables');

        wp_register_script('DataTables', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js');
        wp_enqueue_script('DataTables');

        wp_register_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_style('select2');

        wp_register_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
        wp_enqueue_script('select2');

        if (get_post_type() == 'vacancy') {
            if ($screen->action == 'add' || $screen->action == 'edit' || (isset($_GET['action']) && $_GET['action'] == 'edit')) {
                wp_register_script('google', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDoZGferplQUrXna2-GKtEqBWpwpXj2OJA&libraries=places', [], false, ['strategy' => 'async']);
                wp_enqueue_script('google');
            }
        }
    }

    public function filterScriptLoaderTagHandle($tag, $handle, $src)
    {
        // if ($handle === 'google') {

        //     if (false === stripos($tag, 'async')) {

        //         $tag = str_replace(' src', ' async="async" src', $tag);
        //     }

        //     if (false === stripos($tag, 'defer')) {

        //         $tag = str_replace('<script ', '<script defer ', $tag);
        //     }
        // }

        return $tag;
    }
}

// Initialize
new AdminEnqueue();
