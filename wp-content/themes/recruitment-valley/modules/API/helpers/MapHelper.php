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
    public static function generateLongLat( string $location )
    {
        $locationtUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($location) . "&key=" . static::$map_api_key;

        $res = file_get_contents( $locationtUrl );
        $response = json_decode($res, true);
        
        if($response["status"] === "OK")
        {
            error_log($res);
            return [
                "lat" => $response['results'][0]['geometry']['location']['lat'],
                "long" => $response['results'][0]['geometry']['location']['lng'],
            ];
        }else{
            throw new Exception("Error Processing Request", 400);
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

        $angle = 2 * asin( sqrt(pow(sin($dLat/2), 2) + 
            cos(deg2rad($from["lat"])) * cos(deg2rad($to["lat"])) * pow(sin($dLon/2), 2))); 

        return $angle * static::$earth_radius;
    }
}