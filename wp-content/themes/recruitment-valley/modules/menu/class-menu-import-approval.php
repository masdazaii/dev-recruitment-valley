<?php

namespace Menu;

use Vacancy\Vacancy;
use Helper\ValidationHelper;
use Model\Term;
use WP_Query;

defined('ABSPATH') or die("Direct access not allowed!");

class ImportMenu
{
    public $successMessage;
    public $errorMessage;

    public function __construct()
    {
        add_action('init', [$this, 'initMethod']);
        add_action('admin_menu', [$this, 'importVacancyApprovalMenu']);
        add_action('admin_post_handle_imported_vacancy_approval', [$this, 'vacancyApprovalSumbitHandle']);
        add_action('admin_notices', [$this, 'vacancyApprovalNotices']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueVacancyApprovalScripts']);
        add_action('wp_ajax_handle_vacancy_list', [$this, 'vacancyApprovalListAjax']);
        // add_action('wp_ajax_nopriv_handle_vacancy_list', [$this, 'vacancyApprovalListAjax']);
        add_action('wp_ajax_handle_vacancy_role_change', [$this, 'vacancyChangeRoleAjax']);
    }

    public function initMethod()
    {
        session_start();
    }

    public function importVacancyApprovalMenu()
    {
        if (is_admin() && current_user_can('administrator')) {
            add_menu_page(
                $page_title = __('Vacancy Approval', THEME_DOMAIN),
                $menu_title = __('Vacancy Approval', THEME_DOMAIN),
                $capability = 'manage_options',
                $menu_slug  = 'import-approval',
                $callback   = [$this, 'importVacancyApprovalRenderPage'],
                $icon_url   = 'dashicons-pressthis',
                $position   = 7
            );
        }
    }

    public function importVacancyApprovalRenderPage()
    {
        /** Get imported vacancies */
        $vacancy = new Vacancy();
        $importedVacancies = $vacancy->getImportedVacancy();

        extract(['vacancies' => $importedVacancies]);

        ob_start();
        include __DIR__ . '/imported-vacancy-approval-page.php';
        $output = ob_get_clean();
        echo $output;
    }

    public function vacancyApprovalSumbitHandle()
    {
        if (is_admin() && current_user_can('administrator')) {
            global $wpdb;

            $wpdb->query("START TRANSACTION");
            try {
                /** Validate and sanitize request */
                $validator = new ValidationHelper('vacancyApproval', $_POST);

                if (!$validator->tempValidate()) {
                    $errors = $validator->getErrors();
                    $message = '';
                    foreach ($errors as $field => $message) {
                        $message .= $field . ' : ' . $message . PHP_EOL;
                    }

                    $_SESSION['flash_message'] = [
                        'status'    => 'failed',
                        'message'   => $message
                    ];

                    wp_redirect(esc_url(admin_url('admin.php')) . '?page=import-approval&result=failed');
                }

                /** Validate nonce */
                if (!$validator->validateNonce('nonce_vacancy_approval', $_POST['nonce'])) {
                    $_SESSION['flash_message'] = [
                        'status'    => 'failed',
                        'message'   => $validator->getNonceError()
                    ];

                    wp_redirect(esc_url(admin_url('admin.php')) . '?page=import-approval&result=nonce-failed');
                }

                /** Sanitize request body */
                $validator->tempSanitize();
                $body       = $validator->getData();

                /** Update vacancy */
                $vacancy    = new Vacancy($body['vacancyID']);

                /** Set status */
                if (array_key_exists('approval', $body)) {
                    if ($body['approval'] === 'approved') {
                        $vacancy->setApprovedStatus('admin-approved');
                        $vacancy->setStatus('open');

                        $_SESSION['flash_message'] = [
                            'status'    => 'success',
                            'message'   => 'Vacancy Approved!'
                        ];
                    } else {
                        $vacancy->setApprovedStatus('rejected');
                        $vacancy->setStatus('close');

                        $_SESSION['flash_message'] = [
                            'status'    => 'success',
                            'message'   => 'Vacancy Rejected!'
                        ];
                    }
                }

                $vacancy->setApprovedBy(get_current_user_id());

                wp_redirect(esc_url(admin_url('admin.php')) . '?page=import-approval');

                $wpdb->query("COMMIT");
            } catch (\WP_Error $err) {
                $wpdb->query("ROLLBACK");
                error_log($err->get_error_message());
                wp_redirect(esc_url(admin_url('admin.php')) . '?page=import-approval&result=failed-wp-err');
            } catch (\Exception $e) {
                $wpdb->query("ROLLBACK");
                error_log($e->getMessage());
                wp_redirect(esc_url(admin_url('admin.php')) . '?page=import-approval&result=failed-exception');
            } catch (\Throwable $th) {
                $wpdb->query("ROLLBACK");
                error_log($th->getMessage());
                wp_redirect(esc_url(admin_url('admin.php')) . '?page=import-approval&result=failed-throwable');
            }
        }
    }

    public function vacancyApprovalNotices()
    {
        if (isset($_SESSION['flash_message'])) {
            if (is_array($_SESSION['flash_message']) && array_key_exists('status', $_SESSION['flash_message'])) {
                if ($_SESSION['flash_message']['status'] == 'success') {
                    $this->successNotice();
                } else {
                    $this->errorNotice();
                }
                unset($_SESSION['flash_message']);
            }
        }
    }

    public function errorNotice()
    {
        echo '<div class="error notice is-dismissible">';
        echo '<p>' . __($_SESSION['flash_message']['message'], THEME_DOMAIN) . '</p>';
        echo '</div>';
    }

    public function successNotice()
    {
        echo '<div class="updated notice is-dismissible">';
        echo '<p>' . __($_SESSION['flash_message']['message'], THEME_DOMAIN) . '</p>';
        echo '</div>';
    }

    public function enqueueVacancyApprovalScripts()
    {
        wp_enqueue_script(
            'vacancyApprovalScript',
            THEME_URL . '/assets/js/src/wp-admin.js',
            array('jquery'),
            FALSE
        );

        /** Get Vacancy Term from taxonomy 'role' */
        $termModel = new Term();
        $terms = $termModel->selectTermByTaxonomy('role', true);

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
                ]
            ]
        );

        wp_register_style('DataTables', 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css');
        wp_enqueue_style('DataTables');

        wp_register_script('DataTables', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js');
        wp_enqueue_script('DataTables');
    }

    public function vacancyApprovalListAjax()
    {
        try {
            $filters = [
                'page'          => $_GET['start'] ? ($_GET['start'] / $_GET['length'] + 1) :  1,
                'postPerPage'   => $_GET['length'] ?? 10,
                'orderBy'       => $_GET['orderBy'],
                'sort'          => $_GET['order'],
                'search'        => isset($_GET['search']) ? $_GET['search']['value'] : null
            ];

            $filters['offset']  = $filters['page'] <= 1 ? 0 : ((intval($filters['page']) - 1) * intval($filters['postPerPage']));

            $vacancy = new Vacancy();

            // $vacancies = $vacancy->getImportedVacancy($filters)->posts; // Get imported only

            $vacancies = $vacancy->getVacancies($filters, [], [
                "meta_query" => [
                    "relation" => 'OR',
                    [
                        'key'       => 'rv_vacancy_is_imported',
                        'value'     => 1,
                        'compare'   => '=',
                    ],
                    [
                        'key'       => 'is_paid',
                        'value'     => 0,
                        'compare'   => '=',
                    ],
                ]
            ]);

            $vacanciesResponse = [];

            if ((int)$vacancies->found_posts > 0) {
                $no = ($filters['page'] == 1 ? 1 : $filters['offset'] + 1);
                foreach ($vacancies->posts as $vacancy) :
                    $eachVacancy = new Vacancy($vacancy->ID);
                    $eachVacancyApprovalStatus = $eachVacancy->getApprovedStatus();

                    $vacanciesResponse[] = [
                        'id'                => $vacancy->ID,
                        'no'                => $no,
                        'title'             => $eachVacancy->getTitle(),
                        'vacancyStatus'     => $eachVacancy->getStatus()['name'],
                        'approvalStatus'    => !empty($eachVacancyApprovalStatus) ? $eachVacancyApprovalStatus['label'] : 'waiting approval',
                        'publishDate'       => $eachVacancy->getPublishDate(),
                        'rowNonce'          => wp_create_nonce('nonce_vacancy_approval'),
                        'role'              => $eachVacancy->getSelectedTerm('role', 'id'),
                        'editUrl'           => get_edit_post_link($vacancy->ID),
                        'trashUrl'          => get_delete_post_link($vacancy->ID),
                        'paidStatus'        => $eachVacancy->getIsPaid(),
                        'isImported'        => $eachVacancy->checkImported()
                    ];
                    $no++;
                endforeach;
            }

            wp_send_json([
                'message'           => "Success",
                "draw"              => (int)$_GET['draw'],
                "recordsTotal"      => (int)$vacancies->found_posts,
                "recordsFiltered"   => (int)$vacancies->found_posts,
                'data'              => $vacanciesResponse,
                'search'            => isset($_GET['search']) ? $_GET['search']['value'] : null,
                'filters'           => $filters
            ], 200);
        } catch (\Exception $error) {
            wp_send_json(['message' => $error->getMessage()], 400);
        }
    }

    public function vacancyChangeRoleAjax()
    {
        if (is_admin() && current_user_can('administrator')) {
            global $wpdb;

            $wpdb->query("START TRANSACTION");
            try {
                /** Validate and sanitize request */
                $validator = new ValidationHelper('vacancyChangeRole', $_POST);

                if (!$validator->tempValidate()) {
                    $errors = $validator->getErrors();
                    $message = '';
                    foreach ($errors as $field => $message) {
                        $message .= $field . ' : ' . $message . PHP_EOL;
                    }

                    wp_send_json([
                        'success' => false,
                        'message' => $message
                    ], 400);
                }

                /** Validate nonce */
                if (!$validator->validateNonce('nonce_vacancy_approval', $_POST['nonce'])) {
                    wp_send_json([
                        'success' => false,
                        'message' => $validator->getNonceError()
                    ], 400);
                }

                /** Sanitize request body */
                $validator->tempSanitize();
                $body       = $validator->getData();

                /** Update vacancy */
                $vacancy    = new Vacancy($body['vacancyID']);

                if ($vacancy->setVacancyTerms('role', $body['inputRole'])) {
                    $wpdb->query("COMMIT");

                    wp_send_json([
                        'success' => true,
                        'message' => __('Role is updated!', THEME_DOMAIN)
                    ], 200);
                } else {
                    $wpdb->query("ROLLBACK");

                    wp_send_json([
                        'success' => false,
                        'message' => __('Failed to update role!', THEME_DOMAIN)
                    ], 500);
                }
            } catch (\WP_Error $err) {
                $wpdb->query("ROLLBACK");
                error_log($err->get_error_message());

                wp_send_json([
                    'success' => false,
                    'message' => $err->get_error_message()
                ], 500);
            } catch (\Exception $e) {
                $wpdb->query("ROLLBACK");
                error_log($e->getMessage());

                wp_send_json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            } catch (\Throwable $th) {
                $wpdb->query("ROLLBACK");
                error_log($th->getMessage());

                wp_send_json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        }
    }
}

// Initialize
new ImportMenu();
