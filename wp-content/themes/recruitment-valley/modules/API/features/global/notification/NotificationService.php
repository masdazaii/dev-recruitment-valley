<?php

namespace Global\Notification;

use ResponseHelper;
use WP_REST_Request;

class NotificationService
{
    private $_notificationController;

    public function __construct()
    {
        $this->_notificationController = new NotificationController();
    }

    public function list(WP_REST_Request $request)
    {
        $response = $this->_notificationController->list($request);
        return ResponseHelper::build($response);
    }

    public function read(WP_REST_Request $request)
    {
        $response = $this->_notificationController->read($request);
        return ResponseHelper::build($response);
    }
}
