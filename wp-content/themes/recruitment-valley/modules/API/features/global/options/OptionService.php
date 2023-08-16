<?php

namespace Global;

use ResponseHelper;
use WP_REST_Request;

class OptionService
{
    private $_optionController;

    public function __construct()
    {
        $this->_optionController = new OptionController();
    }

    public function getCompanyEmployeesTotal(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $response = $this->_optionController->getCompanyEmployeesOption($params);
        return ResponseHelper::build($response);
    }
}
