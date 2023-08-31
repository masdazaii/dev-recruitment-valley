<?php

namespace JobAlert;

use WP_REST_Request;
use ResponseHelper;
use Constant\Message;

class JobAlertService
{
    private $_message;
    private $setupJobAlertController;

    public function __construct()
    {
        $this->_message = new Message();
        $this->setupJobAlertController = new JobAlertController();
    }

    public function jobAlert(WP_REST_Request $request)
    {
        $response[] = $this->setupJobAlertController->jobAlert($request);
        return ResponseHelper::build($response);
    }
}
