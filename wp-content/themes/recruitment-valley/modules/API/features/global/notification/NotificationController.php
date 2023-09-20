<?php

namespace Global;

use Constant\Message;
use Exception;
use Model\Notification;
use Throwable;
use WP_Error;
use WP_REST_Request;
use constant\NotificationConstant;

class NotificationController
{
    public $wpdb;
    private $_body;
    private $_message;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->_body = new NotificationConstant();
        $this->_message = new Message();
    }

    public function list($request)
    {
        $userId = $request->user_id;
        $filters = [
            'status'    => $request['status'],
            'page'      => isset($request['page']) ? intval($request['page']) : 1,
            'perPage'   => isset($request['perPage']) ? intval($request['perPage']) : 7,
            'isRead'    => $request['isRead'] ? ($request['isRead'] ? 1 : 0) : 0
        ];

        $filters['offset'] = $filters['page'] <= 1 ? 0 : ((intval($filters['page']) - 1) * intval($filters['perPage']));

        $query = "SELECT rvn.id, rvn.notification_body, rvn.read_status, rvn.notification_type, rvn.created_at, rvn.data as notification_data FROM rv_notifications as rvn WHERE  rvn.recipient_id = {$userId} LIMIT {$filters["perPage"]} OFFSET {$filters["offset"]}";

        $countQuery = "select COUNT(id) as count  FROM rv_notifications where recipient_id = {$userId}";

        $results = $this->wpdb->get_results($query);
        $resultCount = $this->wpdb->get_results($countQuery, OBJECT)[0]->count;

        $notificationCount = 0;

        $notifications = array_map(function ($notification) {
            /** Anggit's syntax start here */
            // return [
            //     "id" => (int) $notification->id,
            //     "message" => $notification->notification_body,
            //     "type" => $notification->notification_type,
            //     "isRead" => $notification->read_status == "0" ? false : true,
            // ];

            /** Changes start here */
            $notifData = NULL;
            if (strpos($notification->notification_type, 'vacancy') !== false) {
                if (!empty($notification->notification_data)) {
                    $data = maybe_unserialize($notification->notification_data);
                    $notifData = [
                        'id'    => (!empty($data) && array_key_exists('id', $data) ? $data['id'] : null),
                        'slug'  => (!empty($data) && array_key_exists('slug', $data) ? $data['slug'] : null)
                    ];
                }
            }

            $notif = [
                "id"        => (int)$notification->id,
                "message"    => $notification->notification_body,
                "type"      => $notification->notification_type,
                "isRead"    => $notification->read_status == "0" ? false : true,
                "notificationData" => $notifData
            ];

            return $notif;
        }, $results);

        if (count($results) <= 0) {
            return [
                "status"    => 200,
                "message"   => "Notification not found",
                "data"      => []
            ];
        }

        return [
            "status" => 200,
            "message" => "success get notification!",
            "data" => $notifications,
            "meta" => [
                "currentPage" => intval($filters['page']) == 0 ? 1 : intval($filters['page']),
                "totalPage" => floor($resultCount / intval($filters['perPage'])) == 0 ? 1 : floor($resultCount / intval($filters['perPage'])),
                "total" => (int) $resultCount
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
        $userId = $request->user_id;
        $notifId = $request->get_param("notif_id");

        try {
            $notification = new Notification($notifId);

            if ($notification->getRecipientId() != $userId) {
                throw new Exception(__("You doesnt have authorization to this notification", THEME_DOMAIN), 401);
            }

            if ($notification->getReadStatus()) {
                throw new Exception(__("Message already read", THEME_DOMAIN), 200);
            }

            $notification->set(["read_status" => 1], ["id" => $notifId]);

            return [
                "status" => 200,
                "message" => "Message has been readed"
            ];
        } catch (WP_Error $wpe) {
            error_log($wpe->get_error_message());
            return [
                "status" => 400,
                "message" => "Something error when reading notification"
            ];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [
                "status" => $e->getCode(),
                "message" => $e->getMessage()
            ];
        } catch (Throwable $th) {
            error_log($th->getMessage());
            return [
                "status" => 400,
                "message" => "Something error when reading notification"
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

            $message = $this->_body->get($type);
            if (strpos($type, 'vacancy') !== false) {
                $message = str_replace('{job_title}', '"' . $data['title'] . '"', $message);
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

    public function checkUnread($request)
    {
        $userId = $request->user_id;
        $queryCountUnread = "SELECT COUNT(id) as count_unread FROM rv_notifications where recipient_id = {$userId} AND read_status = '0'";
        $resultCount = $this->wpdb->get_results($queryCountUnread, OBJECT)[0]->count_unread;

        return [
            "status"    => 200,
            "message"   => $this->_message->get('notification.get_success'),
            "data"      => [
                "unreadNotifications" => (int)$resultCount ?? 0
            ]
        ];
    }
}
