<?php

namespace Global\Notification;

use WP_Error;

class NotificationController
{
    public $wpdb;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
    }

    public function list($request)
    {
        $request['user_id'] = 19;
        $filters = [
            'status'    => $request['status'],
            'page'      => isset($request['page']) ? intval($request['page']) : 1,
            'perPage'   => isset($request['page']) ? intval($request['page']) : 7,
        ];

        $filters['offset'] = $filters['page'] <= 1 ? 0 : ((intval($filters['page']) - 1) * intval($filters['postPerPage']));

        $results = $this->wpdb->get_results("SELECT COUNT(rvn.id) as count_notif, rvn.id, rvn.notification_title, rvn.notification_body, rvn.read_status FROM rv_notifications as rvn WHERE rvn.recipient_role = \'user\' AND rvn.recipient_id = \'{$request['user_id']}\'");

        print('<pre>' . print_r($results, true) . '</pre>');

        // foreach ()

        return [
            "status" => 200,
            "message" => "success get notification!",
            "data" => [
                [
                    "id"    => 1,
                    "title" => "Notification title.",
                    "message"   => "Notification body message, Lorem ipsum, dolor sit amet consectetur adipisicing elit. Sit, perspiciatis.",
                    "isRead"    => false,
                    "time"      => '2023-12-13 23:59:59',
                    "timeUTC"   => '2023-12-13 16:59:59'
                ],
                [
                    "id"    => 2,
                    "title" => "Notification title.",
                    "message"   => "Notification body message, Lorem ipsum, dolor sit amet consectetur adipisicing elit. Sit, perspiciatis.",
                    "isRead"    => false,
                    "time"      => '2023-12-13 23:59:59',
                    "timeUTC"   => '2023-12-13 16:59:59'
                ]
            ]
        ];
    }
}
