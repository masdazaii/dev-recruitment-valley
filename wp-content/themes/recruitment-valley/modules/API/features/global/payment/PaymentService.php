<?php

namespace Global;

use PostType\Payment;
use WP_REST_Request;
use ResponseHelper;
use Constant\Message;
use Helper\ValidationHelper;

class PaymentService
{
    protected $_message;
    private $_paymentController;

    public function __construct()
    {
        $this->_message = new Message();
        $this->_paymentController = new PaymentController;
    }

    public function get(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $response = $this->_paymentController->get($params);
        return ResponseHelper::build($response);
    }

    public function show(WP_REST_Request $request)
    {
        $validator = new ValidationHelper('paymentPackage', $request->get_params());

        if (!$validator->tempValidate()) {
            return ResponseHelper::build([
                "message" => $this->_message->get('payment.package.show_not_found'),
                "errors" => $validator->getErrors(),
                "status" => 404
            ]);
        }

        $validator->tempSanitize();
        $params = $validator->getData();

        $response = $this->_paymentController->show($params);
        return ResponseHelper::build($response);
    }
}
