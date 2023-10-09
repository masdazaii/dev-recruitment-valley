<?php

namespace Global;

use ResponseHelper;
use WP_REST_Request;
use Constant\Message;
use WPMailSMTP\Vendor\GuzzleHttp\Psr7\Response;

class CouponService
{
    private $_couponController;
    private $_message;

    public function __construct()
    {
        $this->_couponController = new CouponController();
        $this->_message = new Message();
    }

    public function list(WP_REST_Request $request)
    {
        $response = $this->_couponController->list($request);
        return ResponseHelper::build($response);
    }

    public function inUse(WP_REST_Request $request)
    {
        $body = $request->get_body_params();
        $response = $this->_couponController->inUse($body);
        return ResponseHelper::build($response);
    }

    public function apply(WP_REST_Request $request)
    {
        $body = $request->get_body_params();
        $response = $this->_couponController->apply($body);
        return ResponseHelper::build($response);
    }
}
