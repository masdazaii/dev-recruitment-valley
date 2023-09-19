<?php

namespace Global\Notification;

use WP_Error;
use WP_REST_Request;

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
        $userId = $request->user_id;
        $filters = [
            'status'    => $request['status'],
            'page'      => isset($request['page']) ? intval($request['page']) : 1,
            'perPage'   => isset($request['perPage']) ? intval($request['perPage']) : 7,
            'isRead'    => $request['isRead'] ? ($request['isRead'] ? 1 : 0 ) : 0 
        ];

        $filters['offset'] = $filters['page'] <= 1 ? 0 : ((intval($filters['page']) - 1) * intval($filters['perPage']));

        $query = "SELECT rvn.id, rvn.notification_body, rvn.read_status, rvn.notification_type, rvn.created_at FROM rv_notifications as rvn WHERE  rvn.recipient_id = {$userId} LIMIT {$filters["perPage"]} OFFSET {$filters["offset"] }";
        
        $countQuery = "select COUNT(id) as count  FROM rv_notifications where recipient_id = {$userId}";

        $results = $this->wpdb->get_results($query);
        $resultCount = $this->wpdb->get_results($countQuery, OBJECT)[0]->count;

        $notificationCount = 0;

        $notifications = array_map(function ($notification){
            return [
                "id" => (int) $notification->id,
                "message" => $notification->notification_body,
                "type" => $notification->notification_type,
                "isRead" => $notification->read_status == "0" ? false : true,
            ];
        }, $results);

        if(count($results) <= 0)
        {
            return [
                "status" => 404,
                "message" => "Notification not found"
            ];
        }

        return [
            "status" => 200,
            "message" => "success get notification!",
            "data" => $notifications,
            "meta" => [
                "currentPage" => intval($filters['page']) == 0 ? 1 : intval($filters['page']) ,
                "totalPage" => floor($resultCount/intval($filters['perPage'])) == 0 ? 1 : floor($resultCount/intval($filters['perPage']))  ,
                "total" => (int) $resultCount
            ]
            // "data" => [
            //     [
            //         "id"    => 1,
            //         "title" => "Notification title.",
            //         "message"   => "Notification body message, Lorem ipsum, dolor sit amet consectetur adipisicing elit. Sit, perspiciatis.",
            //         "isRead"    => false,
            //         "time"      => '2023-12-13 23:59:59',
            //         "timeUTC"   => '2023-12-13 16:59:59'
            //     ],
            //     [
            //         "id"    => 2,
            //         "title" => "Notification title.",
            //         "message"   => "Notification body message, Lorem ipsum, dolor sit amet consectetur adipisicing elit. Sit, perspiciatis.",
            //         "isRead"    => false,
            //         "time"      => '2023-12-13 23:59:59',
            //         "timeUTC"   => '2023-12-13 16:59:59'
            //     ]
            // ]
        ];
    }

    public function store( WP_REST_Request $request )
    {

    }

    public function delete( WP_REST_Request $request )
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

    public function write($request)
    {
        // $current = new \DateTime("now", new \DateTimeZone('UTC'));
        // $notification = [
        //     'notification_title' => $this->_message->get("vacancy.notification.submitted"),
        //     'notification_body'  => $this->_message->get("vacancy.create.free.success"),
        //     'read_status'   => 'false',
        //     'recipient_id'  => $request['user_id'],
        //     'recipient_role'    => 'user',
        //     'created_at'        => date('Y-m-d H:i:s'),
        //     'created_at_utc'    => $current->format('Y-m-d H:i:s'),
        //     'notification_post_id' => $vacancyModel->vacancy_id
        // ];
        // $this->wpdb->insert('rv_notifications', $notification);
    }
}
