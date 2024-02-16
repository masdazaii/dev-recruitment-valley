<?php

namespace PostType;

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
        add_filter('post_row_actions', [$this, 'contactActionLink'], 10, 1);
    }

    public function contactCreateCPT()
    {
        $additionalArgs = [
            'publicly_queryable' => false,
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
        $column_name['title'] = __('Sender');
        // $column_name['email'] = __('Email Address');
        $column_name['phone'] = __('Phone Number');
        $column_name['content'] = __('Contact Message');
        $column_name['submit'] = __('Submited On');

        return $column_name;
    }

    public function contactRow($column_name, $post_id)
    {

        switch ($column_name) {
            case 'email':
                echo get_field('email', $post_id, true);
                break;
            case 'phone':
                echo '(' . get_field('phone_number_code_area', $post_id, true) . ') ' . get_field('phone_number', $post_id, true);
                break;
            case 'content':
                $content = substr(get_the_content(null, false, $post_id), 0, 100);
                $content .= '...';
                echo $content;
                break;
            case 'submit':
                echo get_the_date('Y/m/d H:i:s T', $post_id);
        }
    }

    public function contactActionLink($actions)
    {
        if (get_post_type() === 'contacts') {
            // unset($actions['edit']); // Edit action link
            unset($actions['inline hide-if-no-js']); // Quick edit action link
            unset($actions['view']); // View action link
        }
        return $actions;
    }
}

// Initiate
new ContactCPT();
