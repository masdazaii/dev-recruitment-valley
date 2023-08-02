<?php


class ResponseHelper
{
    public static function build(array $response)
    {
        if ($response["success"]) {
            return wp_send_json_success([
                "success" => $response["success"],
                "data" => $response["data"],
                "message" => $response["message"],
            ], $response["statusCode"]);
        } else {
            return wp_send_json_error([
                "success" => $response["success"],
                "message" => $response["message"],
            ], $response["statusCode"]);
        }
    }
}
