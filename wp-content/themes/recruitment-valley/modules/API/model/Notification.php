<?php

namespace Model;

use Exception;

class Notification
{
    private $wpdb;

    private $table = "rv_notifications";

    public $notification;
    public $recipient_id;
    public $recipient;
    public $type;

    public function __construct($notificationId = false)
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        if ($notificationId) {
            $query = "select * from rv_notifications where id = " . $notificationId . ";";
            $notification = $this->wpdb->get_results($query);
            if (count($notification) > 0) {
                $this->notification = $notification[0];
            } else {
                throw new Exception(__("notification not found", "recruitment_valley"), 404);
            }
        }
    }

    public function getRecipientId()
    {
        return $this->notification->recipient_id;
    }

    public function getData()
    {
        return $this->notification;
    }

    public function getType()
    {
        return $this->notification->notification_type;
    }

    public function getReadStatus()
    {
        return $this->notification->read_status == "0" ? false : true;
    }

    public function setNotification($notificationId)
    {
        if ($notificationId) {
            $query = "select * from rv_notifications where id = " . $notificationId . ";";
            $notification = $this->wpdb->get_results($query);
            if (count($notification) > 0) {
                $this->notification = $notification[0];
            } else {
                throw new Exception(__("notification not found", "recruitment_valley"), 404);
            }
        }
    }

    /**
     * set (update) notification entry, make sure the notification id already set
     *
     * @param  mixed $payload
     * @return void
     */
    public function set($payload, $where = [])
    {
        return $this->wpdb->update($this->table, $payload, $where);
    }
}
