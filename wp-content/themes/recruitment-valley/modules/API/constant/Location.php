<?php

namespace Constant;

class LocationConstant
{
    private const cityDir = THEME_DIR . '/assets/dependecies/cities/';

    public static function cities(String $country = 'all')
    {
        if ($country == 'all') {
            $cities = file_get_contents(self::cityDir . 'all.json');
        } else {
            $cities = file_get_contents(self::cityDir . $country . '.json');
        }

        if ($cities) {
            $cities = json_decode($cities, true);
            return $cities;
        } else {
            return null;
        }
    }
}
