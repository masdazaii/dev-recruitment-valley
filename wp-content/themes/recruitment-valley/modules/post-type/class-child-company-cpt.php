<?php

namespace MI\PostType;

use DateTimeImmutable;
use PostType\RegisterCPT;
use Constant\Message;
use constant\NotificationConstant;
use Global\NotificationService;
use Model\ChildCompany as ChildCompanyModel;
use Model\CompanyRecruiter;

/**
 * Custom Post Type : Company Recruiter's - child company.
 * To handle data for child company that belong to user with role Recruiter
 *
 */
class ChildCompanyCPT extends RegisterCPT
{
    private $_message;
    private $_notification;
    private $_notificationConstant;

    private const slug = 'child-company';

    public $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb             = $wpdb;
        $this->_message         = new Message();
        $this->_notification    = new NotificationService();
        $this->_notificationConstant = new NotificationConstant();

        add_action('init', [$this, 'registerChildCompanyCPT']);
        add_action('admin_menu', [$this, 'childCompanyAdminMenu']);
        add_filter('manage_child-company_posts_columns', [$this, 'childCompanyColoumn'], 10, 1);
        add_action('manage_child-company_posts_custom_column', [$this, 'childCompanyCustomColoumn'], 10, 2);
    }

    /**
     * Register Company Recruiter's - child company function
     * this is cpt for child company that belong to user with role recuriters
     *
     * @return void
     */
    public function registerChildCompanyCPT()
    {
        $title = __('Recruiters Child Company', THEME_DOMAIN);
        $slug = self::slug;
        $args = [
            'menu_position'         => 99,
            'supports'              => array('title', 'author'),
            'publicly_queryable'    => false
        ];

        $this->customPostType($title, $slug, $args);
    }

    /**
     * Move the admin menu function.
     *
     * Remove the default menu and make a submenu in user.
     *
     * @return void
     */
    function childCompanyAdminMenu()
    {
        if (is_admin() && current_user_can('administrator')) {
            remove_menu_page("edit.php?post_type=" . self::slug);
        }

        if (is_admin() && current_user_can('administrator')) {
            add_submenu_page(
                $parent_slug = 'users.php',
                $page_title = __('Child Company', THEME_DOMAIN),
                $menu_title = __('Child Company', THEME_DOMAIN),
                $capability = 'manage_options',
                $menu_slug  = 'edit.php?post_type=' . self::slug,
            );
        }
    }

    public function childCompanyColoumn($coloumn)
    {
        unset($coloumn['date']);
        unset($coloumn['author']);
        $coloumn['title']       = __('Company Name');
        $coloumn['phone']       = __('Company Phone');
        $coloumn['recruiter']   = __('Recruiter Company');

        return $coloumn;
    }

    public function childCompanyCustomColoumn($coloumn, $post_id)
    {
        $childCompanyModel  = new ChildCompanyModel($post_id);
        $recruiterModel     = new CompanyRecruiter($childCompanyModel->getChildCompanyOwner());

        switch ($coloumn) {
            case 'title':
                echo $childCompanyModel->getName();
                break;
            case 'email':
                echo $childCompanyModel->getEmail();
                break;
            case 'phone':
                echo $childCompanyModel->getPhoneNumber();
                break;
            case 'recruiter':
                echo $recruiterModel->getName() ?? '-';
                break;
        }

        return $coloumn;
    }
}

/** Initialization */
new ChildCompanyCPT();
