<?php


class ResponseHelper
{
    public static function build(array $response)
    {
        $wp_rest_response = new WP_REST_Response();
        $wp_rest_response->set_headers("Content-type: application/json");
        $wp_rest_response->set_status(isset($response['status']) ? $response['status'] : 200);
        $wp_rest_response->set_data($response);
        return $wp_rest_response;
    }
}
