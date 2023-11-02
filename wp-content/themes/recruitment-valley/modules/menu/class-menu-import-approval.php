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
                        $vacancy->setApprovedAt('now');

                        $_SESSION['flash_message'] = [
                            'status'    => 'success',
                            'message'   => 'Vacancy Approved!'
                        ];
                    } else {
                        $vacancy->setApprovedStatus('rejected');
                        $vacancy->setStatus('declined');
                        $vacancy->setApprovedAt('now');

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

    public function vacancyApprovalListAjax()
    {
        try {
            $filters = [
                'page'          => $_GET['start'] ? ($_GET['start'] / $_GET['length'] + 1) :  1,
                'postPerPage'   => $_GET['length'] ?? 10,
                // 'orderBy'       => ($_GET['orderBy']),
                // 'sort'          => ,
                'search'        => isset($_GET['search']) ? $_GET['search']['value'] : null
            ];

            if (isset($_GET['orderBy'])) {
                // $filters['prderBy'] = $_GET['orderBy'];
                switch ($_GET['orderBy']) {
                    case 'none':
                        $filters['orderBy'] = 'none';
                        break;
                    case 'ID':
                        $filters['orderBy'] = 'ID';
                        break;
                    case 'author':
                        $filters['orderBy'] = 'author';
                        break;
                    case 'title':
                        $filters['orderBy'] = 'title';
                        break;
                    case 'name':
                        $filters['orderBy'] = 'name';
                        break;
                    case 'type':
                        $filters['orderBy'] = 'type';
                        break;
                    case 'modified':
                        $filters['orderBy'] = 'modified';
                        break;
                    case 'parent':
                        $filters['orderBy'] = 'parent';
                        break;
                    case 'rand':
                        $filters['orderBy'] = 'rand';
                        break;
                    case 'comment_count':
                        $filters['orderBy'] = 'comment_count';
                        break;
                    case 'relevance':
                        $filters['orderBy'] = 'relevance';
                        break;
                    case 'menu_order':
                        $filters['orderBy'] = 'menu_order';
                        break;
                    case 'meta_value':
                        $filters['orderBy'] = 'meta_value';
                        break;
                    case 'meta_value_num':
                        $filters['orderBy'] = 'meta_value_num';
                        break;
                    case 'post__in':
                        $filters['orderBy'] = 'post__in';
                        break;
                    case 'post_name__in':
                        $filters['orderBy'] = 'post_name__in';
                        break;
                    case 'post_parent__in':
                        $filters['orderBy'] = 'post_parent__in';
                        break;
                    case 'date':
                    case 'post_date':
                    default:
                        $filters['orderBy'] = 'post_date';
                        break;
                }
            } else {
                $filters['oderBy'] = 'post_date';
            }

            if (isset($_GET['order'])) {
                if (is_array($_GET['order'])) {
                    /** Set sort for datatable */
                    switch ($_GET['order'][0]['dir']) {
                        case strtolower('ASC'):
                        case strtolower('ASCENDING'):
                            $filters['order'] = 'ASC';
                            break;
                        case strtolower('DESC'):
                        case strtolower('DESCENDING'):
                        default:
                            $filters['order'] = 'DESC';
                            break;
                    }
                } else {
                    switch ($_GET['order']) {
                        case strtolower('ASC'):
                        case strtolower('ASCENDING'):
                            $filters['order'] = 'ASC';
                            break;
                        case strtolower('DESC'):
                        case strtolower('DESCENDING'):
                        default:
                            $filters['order'] = 'DESC';
                            break;
                    }
                }
            }

            $filters['offset']  = $filters['page'] <= 1 ? 0 : ((intval($filters['page']) - 1) * intval($filters['postPerPage']));

            $vacancy = new Vacancy();

            // $vacancies = $vacancy->getImportedVacancy($filters)->posts; // Get imported only

            $termProcessing = get_term('processing', 'status');
            if ($termProcessing) {
                $taxonomyFilters['status'] = $termProcessing;
            }

            /** Set time limit 24 hour ago */
            $now = new \DateTimeImmutable('now');
            $timeLimit = $now->format('Y-m-d H:i:s');

            /** Get vacancies that fulfill this criteria :
             * (Not expired OR didn't have meta expired_at)
             * AND
             * (Is imported OR didn't have meta rv_vacancy_is_imported OR is_paid = 0)
             * AND
             * Status is processing
             */
            $vacancies = $vacancy->getVacancies($filters, [
                /** Anggit's changes add the query expired & processing */
                "post_type"         => 'vacancy',
                "posts_per_page"    => $filters['postPerPage'] ?? 10,
                "offset"            => $filters['offset'] ?? 0,
                "orderby"           => $filters['orderBy'] ?? "date",
                "order"             => $filters['sort'] ?? 'ASC',
                "post_status"       => "publish",
                "meta_query"        => [
                    "relation"      => 'AND',
                    [
                        'relation'  => 'OR',
                        [
                            'key'       => 'expired_at',
                            'value'     => $timeLimit,
                            'compare'   => '>=',
                            'type'      => 'Date',
                        ],
                        [
                            'key'       => 'expired_at',
                            'value'     => '',
                            'compare'   => '='
                        ],
                        [
                            'key'       => 'expired_at',
                            'compare'   => 'NOT EXISTS'
                        ]
                    ],
                    [
                        "relation" => 'OR',
                        [
                            'key'       => 'rv_vacancy_is_imported',
                            'value'     => 1,
                            'compare'   => '=',
                        ],
                        [
                            'key'       => 'rv_vacancy_is_imported',
                            'compare'   => 'NOT EXISTS'
                        ],
                        [
                            'key'       => 'is_paid',
                            'value'     => 0,
                            'compare'   => '=',
                        ],
                    ]
                ],
                'tax_query' => [
                    "relation" => "OR",
                    [
                        'taxonomy' => "status",
                        'field'    => 'slug',
                        'terms'    => 'processing',
                        'compare'  => '='
                    ]
                ]
            ]);

            $vacanciesResponse = [];

            if ((int)$vacancies->found_posts > 0) {
                $no = ($filters['page'] == 1 ? 1 : $filters['offset'] + 1);
                foreach ($vacancies->posts as $vacancy) :
                    $eachVacancy = new Vacancy($vacancy->ID);
                    $eachVacancyApprovalStatus = $eachVacancy->getApprovedStatus();

                    $now            = new \DateTimeImmutable('now');
                    $isExpired      = false;
                    $expiredAt      = $eachVacancy->getExpiredAt('Y-m-d H:i:s');
                    if ($expiredAt) {
                        $expiredAt = new \DateTime($expiredAt);

                        /** Check if expired */
                        if ($expiredAt < $now) {
                            $isExpired = true;
                        } else {
                            $isExpired = false;
                        }
                    } else {
                        $isExpired = false;
                    }

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
                        'isImported'        => $eachVacancy->checkImported(),
                        'isExpired'         => $isExpired
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
                'filters'           => $filters,
                'query'             => $vacancies->query
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
