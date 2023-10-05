<?php

class RequestMethod
{
    public function __construct()
    {
        $this->generateRequestMethods();
    }

    public function generateRequestMethods()
    {
        $methods = [
            "post" => "POST",
            "put" => "PUT"
        ];

        foreach ($methods as $key => $method) {
            define("request_method_" . $key, $method);
        }
    }
}

new RequestMethod;
