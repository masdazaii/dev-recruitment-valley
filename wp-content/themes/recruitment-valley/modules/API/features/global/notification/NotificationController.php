<?php

namespace Global;

use WP_Error;
use WP_REST_Request;
use constant\NotificationConstant;
use Throwable;

class NotificationController
{
    public $wpdb;
    private $_body;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->_body = new NotificationConstant();
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

    public function store(WP_REST_Request $request)
    {
    }

    public function delete(WP_REST_Request $request)
    {
    }

    public function read($request)
    {
        $request['user_id'] = 19;

        if ($this->wpdb->update('rv_notifications', ['read_status' => 'true'], ['id' => $request['notif_id']])) {
            return [
                'status' => 200,
                'message' => "Notification is readed."
            ];
        } else {
            return [
                'status' => 500,
                'message' => "Failed notification is readed."
            ];
        }
    }

    public function write($type, $recipient, $data)
    {
        error_log(json_encode($data));
        try {
            $current = new \DateTime("now", new \DateTimeZone('UTC'));

            switch ($type) {
                case "vacancy.expired_1":
                case "vacancy.expired_3":
                case "vacancy.expired_7":
                case "vacancy.expired_5":
                case "vacancy.expired":
                    $message = $data['title'] . ' ' . $this->_body->get($type);
                    break;
                case "vacancy.applied":
                    $message = $this->_body->get($type) . ' ' . $data['title'];
                    break;
                default:
                    $message = $this->_body->get($type);
            }

            $notification = [
                'notification_body' => $message,
                'notification_type' => $type,
                'recipient_id'      => $recipient,
                'created_at'        => date('Y-m-d H:i:s'),
                'created_at_utc'    => $current->format('Y-m-d H:i:s'),
                'data'              => serialize($data)
            ];

            $result = $this->wpdb->insert('rv_notifications', $notification);

            if (!$result) {
                error_log(json_encode($notification));
            }

            return $result;
        } catch (Throwable $th) {
            error_log($th);
        }
    }
}
