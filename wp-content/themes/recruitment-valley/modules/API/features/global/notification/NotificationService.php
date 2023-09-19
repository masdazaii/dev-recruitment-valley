<?php

namespace Global;

use ResponseHelper;
use WP_REST_Request;
use constant\NotificationConstant;

class NotificationService
{
    private $_notificationController;
    private $_notificationConstant;

    public function __construct()
    {
        $this->_notificationController = new NotificationController();
        $this->_notificationConstant = new NotificationConstant();
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

    public function write(String $type, Mixed $recipient, array $data)
    {
        return $this->_notificationController->write($type, $recipient, $data);
    }
}
