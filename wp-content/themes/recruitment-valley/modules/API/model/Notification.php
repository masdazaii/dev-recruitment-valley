<?php

namespace Model;

use Exception;

class Notifaction
{
    private $wpdb;

    public function __construct( $notificationId = false )
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        if($notificationId)
        {
            $query = "select id from rv_notifications where id=". $notificationId;
            $notification = $this->wpdb->get_results($query, "array");
            if(count($notification) > 0)
            {
                throw new Exception(__("notification not found", "recruitment_valley"), 404 );
            }
        }
    }
}