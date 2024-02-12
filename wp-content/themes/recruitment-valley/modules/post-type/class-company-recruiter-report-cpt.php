<?php

namespace MI\PostType;

use DateTimeImmutable;
use PostType\RegisterCPT;
use Constant\Message;
use Model\CompanyRecruiterReport;

/**
 * Custom Post Type : Company Recruiter's - child company.
 * To handle data for child company that belong to user with role Recruiter
 *
 */
class CompanyRecruiterReportCPT extends RegisterCPT
{
    private $_message;

    private const slug = 'recruiter-report';

    public $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb             = $wpdb;
        $this->_message         = new Message();
        error_log('CompanyRecruiterReportCPT');
        add_action('init', [$this, 'registerCompanyRecruiterReportCPT']);
        add_filter("manage_recruiter-report_posts_columns", [$this, 'companyRecruiterReportColoumn'], 10, 1);
        add_action("manage_recruiter-report_posts_custom_column", [$this, 'companyRecruiterReportCustomColoumn'], 10, 2);
        add_filter('page_row_actions', [$this, 'companyRecruiterReportQuickAction'], 10, 2);
    }

    /**
     * Register Company Recruiter's - child company function
     * this is cpt for child company that belong to user with role recuriters
     *
     * @return void
     */
    public function registerCompanyRecruiterReportCPT()
    {
        $title = __('Company Recruiter Report', THEME_DOMAIN);
        $slug = self::slug;
        $args = [
            'menu_position'         => 10,
            'supports'              => array('title', 'editor'),
            'publicly_queryable'    => false,
            'has_archive'           => true,
            'public'                => true,
            'hierarchical'          => false,
            'show_in_rest'          => true
        ];

        $this->customPostType($title, $slug, $args);
    }

    /**
     * Set table coloumn function
     *
     * @param [type] $coloumn
     * @return void
     */
    public function companyRecruiterReportColoumn($coloumn)
    {
        unset($coloumn['date']);
        unset($coloumn['author']);
        $coloumn['title']       = __('Report Name');
        $coloumn['periode']     = __('Report Periode');
        $coloumn['download']    = __('Download Link');

        return $coloumn;
    }

    /**
     * Set Table Custom Coloumn function
     *
     * @param [type] $coloumn
     * @param [type] $post_id
     * @return void
     */
    public function companyRecruiterReportCustomColoumn($coloumn, $post_id)
    {
        $reportModel = new CompanyRecruiterReport($post_id);

        switch ($coloumn) {
            case 'title':
                echo $reportModel->title();
                break;
            case 'periode':
                echo $reportModel->getPeriodeStart('d F, Y') . ' - ' . $reportModel->getPeriodeEnd('d F, Y');
                break;
            case 'download':
                echo '<a href="' . $reportModel->getDownloadURL() . '" target="_blank" style="background-color: rgba(209 213 219 / 1); padding: 0.5rem 0.75rem 0.5rem 0.75rem; border-radius: 0.375rem; font-weight: 500;">Download Report</a>';
                break;
        }

        return $coloumn;
    }

    public function companyRecruiterReportQuickAction($actions, $post)
    {
        error_log('called');

        if ($post->post_type === self::slug) {
            unset($actions['edit']);
            unset($actions['trash']);

            unset($actions['inline hide-if-no-js']);
        }

        return $actions;
    }
}

/** Initialization */
new CompanyRecruiterReportCPT();
