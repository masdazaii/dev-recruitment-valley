<?php

namespace Ajax\Location;

use Constant\LocationConstant;

class AjaxLocation
{
    public function __construct()
    {
        add_action('wp_ajax_handle_city_list', [$this, 'locationCityList']);
    }

    public function locationCityList()
    {
        $cities = \Constant\LocationConstant::cities($_POST['country']);

        wp_send_json([
            'success' => true,
            'message' => '$message',
            'data'    => $cities
        ], 200);
    }
}

// Initiate
new AjaxLocation();
