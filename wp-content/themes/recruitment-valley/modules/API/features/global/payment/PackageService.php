<?php

namespace Global;

use PostType\Payment;
use WP_REST_Request;
use ResponseHelper;
use Constant\Message;
use Helper\ValidationHelper;

class PackageService
{
    protected $_message;
    private $_packageController;

    public function __construct()
    {
        $this->_message = new Message();
        $this->_packageController = new PackageController;
    }

    public function get(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $response = $this->_packageController->get($params);
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

        $response = $this->_packageController->show($params);
        return ResponseHelper::build($response);
    }

    public function purchase( WP_REST_Request $request )
    {
        return $this->_packageController->purchase($request);
    }
}
