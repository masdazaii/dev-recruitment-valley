<?php
defined('ABSPATH') || die("Direct access not allowed");

class ContactCPT extends RegisterCPT
{
    protected $slugCPT;
    protected $contactMetaKeys;

    public function __construct()
    {
        $this->slugCPT = 'contacts';

        add_action('init', [$this, 'contactCreateCPT']);
        add_filter('manage_contacts_posts_columns', [$this, 'contactColumn'], 10, 1);
        add_action('manage_contacts_posts_custom_column', [$this, 'contactRow'], 10, 2);
    }

    public function contactCreateCPT()
    {
        $additionalArgs = [
            'menu_posisiton' => 5,
            'has_archive' => true,
            'public' => true,
            'hierarchical' => false,
            'show_in_rest' => true
        ];

        $this->customPostType('Contact', $this->slugCPT, $additionalArgs);
    }

    public function contactColumn($column_name)
    {
        unset($column_name['date']);
        $column_name['title'] = __('Company Name');
        $column_name['name'] = __('Sender Name');
        $column_name['email'] = __('Email Address');
        $column_name['phone'] = __('Phone Number');
        $column_name['content'] = __('Contact Message');
        $column_name['submit'] = __('Submited On');

        return $column_name;
    }

    public function contactRow($column_name, $post_id)
    {
        switch ($column_name) {
            case 'name':
                echo get_post_meta($post_id, '_contact_name', true);
                break;
            case 'email':
                echo get_post_meta($post_id, '_contact_email', true);
                break;
            case 'phone':
                echo get_the_content($post_id, '_contact_phone', true);
                break;
            case 'content':
                echo get_the_content(null, false, $post_id);
                break;
            case 'submit':
                echo get_the_date('Y/m/d H:i:s T', $post_id);
        }
    }
}

// Initiate
new ContactCPT();