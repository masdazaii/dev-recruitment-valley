<?php

namespace Helper;

use Exception;

class Maphelper
{
    public static $map_api_key = "AIzaSyDoZGferplQUrXna2-GKtEqBWpwpXj2OJA";

    public static $earth_radius = 6371;

    /**
     * generateLongLat
     *
     * @param  mixed $location
     * @return void
     */
    public static function generateLongLat(string $location)
    {
        $locationtUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($location) . "&key=" . static::$map_api_key;

        $res = file_get_contents($locationtUrl);
        $response = json_decode($res, true);

        if ($response["status"] === "OK") {
            error_log($res);
            return [
                "lat" => $response['results'][0]['geometry']['location']['lat'],
                "long" => $response['results'][0]['geometry']['location']['lng'],
            ];
        } else {
            throw new Exception("Invalid location, pleas insert valid location", 400);
        }
    }


    /**
     * calculateDistance
     *
     * @param  mixed $from contain key lat and long
     * @param  mixed $to contain key lat and long
     * @return void
     */
    public static function calculateDistance($from, $to)
    {
        $dLat = deg2rad($to["lat"] - $from["lat"]);
        $dLon = deg2rad($to["long"] - $from["long"]);

        // $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($from["lat"])) * cos(deg2rad($to["lat"])) * sin($dLon / 2) * sin($dLon / 2);
        // $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        // $distance = self::$earth_radius * $c;

        $angle = 2 * asin(sqrt(pow(sin($dLat / 2), 2) +
            cos(deg2rad($from["lat"])) * cos(deg2rad($to["lat"])) * pow(sin($dLon / 2), 2)));

        return $angle * static::$earth_radius;
    }

    public static function reverseGeoData(string $by, string $lang = 'en', string $result = 'all', array $coordinate = [], string $address = "")
    {
        if ($by === 'coordinate') {
            if (empty($coordinate)) {
                throw new Exception("Coordinate cannot be empty!");
            } else {
                if (array_key_exists('latitude', $coordinate) && array_key_exists('longitude', $coordinate)) {
                    $mapsAPI = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $coordinate['latitude'] . "," . $coordinate['longitude'] . "&language=" . $lang . "&key=" . static::$map_api_key;
                } else {
                    throw new Exception("Coordinate cannot be empty!");
                }
            }
        } else {
            $mapsAPI = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $address . "&language=" . $lang . "&key=" . static::$map_api_key;
        }

        $curl_handle = curl_init();
        $ch = curl_setopt($curl_handle, CURLOPT_URL, $mapsAPI);
        $ch = curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        $ch = curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl_handle);
        $data = json_decode($output, true);
        curl_close($curl_handle);

        // print('<pre>' . print_r($data, true) . '</pre>');

        if ($data['status'] === 'OK') {
            $data = $data['results'][0];
            $remapData = [];
            foreach ($data['address_components'] as $key => $value) {
                if (in_array('street_number', $value['types'])) {
                    $remapData['street_number'] = [
                        'long_name' => $value['long_name'],
                        'short_name' => $value['short_name']
                    ];
                } else if (in_array('route', $value['types'])) {
                    $remapData['route'] = [
                        'long_name' => $value['long_name'],
                        'short_name' => $value['short_name']
                    ];
                } else if (in_array('neighborhood', $value['types'])) {
                    $remapData['neighborhood'] = [
                        'long_name' => $value['long_name'],
                        'short_name' => $value['short_name']
                    ];
                } else if (in_array('sublocality', $value['types'])) {
                    $remapData['sublocality'] = [
                        'long_name' => $value['long_name'],
                        'short_name' => $value['short_name']
                    ];
                } else if (in_array('administrative_area_level_2', $value['types'])) {
                    $remapData['city'] = [
                        'long_name' => $value['long_name'],
                        'short_name' => $value['short_name']
                    ];
                } else if (in_array('administrative_area_level_1', $value['types'])) {
                    if (array_key_exists('city', $remapData) && empty($remapData['city'])) {
                        $remapData['city'] = [
                            'long_name' => $value['long_name'],
                            'short_name' => $value['short_name']
                        ];
                    }

                    $remapData['state'] = [
                        'long_name' => $value['long_name'],
                        'short_name' => $value['short_name']
                    ];
                } else if (in_array('country', $value['types'])) {
                    $remapData['country'] = [
                        'long_name' => $value['long_name'],
                        'short_name' => $value['short_name']
                    ];
                } else if (in_array('postal_code', $value['types'])) {
                    $remapData['postal_code'] = [
                        'long_name' => $value['long_name'],
                        'short_name' => $value['short_name']
                    ];
                } else {
                    $remapData[$value['types'][0]] = [
                        'long_name' => $value['long_name'],
                        'short_name' => $value['short_name']
                    ];
                }
            }

            switch ($result) {
                case 'country':
                    return $remapData['country'];
                case 'state':
                    return $remapData['state'];
                case 'city':
                    return $remapData['city'];
                default:
                    return $remapData;
            }
        } else {
            throw new Exception("Invalid location, pleas insert valid location", 400);
        }
    }
}
