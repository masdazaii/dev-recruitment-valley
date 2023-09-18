<?php

namespace PostType;

use Constant\Message;
use DateTimeImmutable;
use Vacancy\Vacancy as VacancyModel;

class Vacancy extends RegisterCPT
{
    private $_message;
    public $wpdb;

    public function __construct()
    {
        add_action('init', [$this, 'RegisterVacancyCPT']);
        add_action('set_object_terms', [$this, 'setExpiredDate'], 10, 5);
        $this->_message = new Message();

        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function RegisterVacancyCPT()
    {
        $title = __('Vacancies', THEME_DOMAIN);
        $slug = 'vacancy';
        $args = [
            'menu_position' => 5,
            'supports' => array('title', 'editor', 'author', 'thumbnail')
        ];

        $this->customPostType($title, $slug, $args);

        $taxonomies = [
            [
                "name" => "sector",
                "arguments" => [
                    'label' => __("Sector", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "role",
                "arguments" => [
                    'label' => __("Role", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "type",
                "arguments" => [
                    'label' => __("Type", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "education",
                "arguments" => [
                    'label' => __("Education", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "working-hours",
                "arguments" => [
                    'label' => __("Working Hours", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "status",
                "arguments" => [
                    'label' => __("Status", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "location",
                "arguments" => [
                    'label' => __("Location", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "experiences",
                "arguments" => [
                    'label' => __("Working Experience", THEME_DOMAIN),
                ]
            ]
        ];

        foreach ($taxonomies as $key => $taxonomy) {
            $this->taxonomy($slug, $taxonomy["name"], $taxonomy["arguments"]);
        }
    }

    public function setExpiredDate($object_id, $terms = [], $tt_ids = [], $taxonomy = '', $append = true, $old_tt_ids = [])
    {
        $post = get_post($object_id, 'object');
        if ($post->post_type == 'vacancy') {
            $openTerm = get_term_by('slug', 'open', 'status', 'OBJECT');

            if ($taxonomy === 'status' && in_array($openTerm->term_id, $terms)) {
                if (!get_field('is_paid', $object_id, true)) {
                    $vacancyModel = new VacancyModel($object_id);
                    $today = new DateTimeImmutable("now");
                    $vacancyModel->setProp($vacancyModel->acf_expired_at, $today->modify("+30 days")->format("Y-m-d H:i:s"));
                }

                /** Create notification : vacancy is approved */
                // $current = new \DateTime("now", new \DateTimeZone('UTC'));
                // $notification = [
                //     'notification_title' => $this->_message->get("De vacature is goedgekeurd"),
                //     'notification_body'  => $this->_message->get("Congratulations! your post has been published successfully"),
                //     'read_status'   => 'false',
                //     'recipient_id'  => $post->post_author,
                //     'recipient_role'    => 'user',
                //     'created_at'        => date('Y-m-d H:i:s'),
                //     'created_at_utc'    => $current->format('Y-m-d H:i:s'),
                //     'notification_post_id' => $object_id
                // ];

                // $this->wpdb->query($this->wpdb->prepare("INSERT INTO `rv_notifications` (
                //     `notification_title`,
                //     `notification_body`,
                //     `read_status`,
                //     `recipient_id`,
                //     `recipient_role`,
                //     `created_at`,
                //     `created_at_utc`,
                //     `notification_post_id`) VALUES (
                //        {$notification['notification_title']},
                //        {$notification['notification_body']},
                //        {$notification['read_status']},
                //        {$notification['recipient_id']},
                //        {$notification['recipient_role']},
                //        {$notification['created_at']},
                //        {$notification['created_at_utc']},
                //        {$notification['notification_post_id']},
                //     ) ON DUPLICATE KEY UPDATE
                //     `notification_title` = VALUES(`notification_title`),
                //     `notification_body` = VALUES(`notification_body`),
                //     `read_status` = VALUES(`read_status`),
                //     `created_at` = VALUES(`created_at`),
                //     `created_at_utc` = VALUES(`created_at_utc`),
                // "));
            }
        }
    }
}
new Vacancy();
