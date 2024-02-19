<?php

namespace Controller;

class BaseHandlerController extends BaseController
{
    public function __construct()
    {
    }

    public function handleError(Object $error, String $class, String $method, array $data = [], String $logname = 'log_error')
    {
        return parent::handleError($error, $class, $method, $data, $logname);
    }
}
