<?php

namespace Helper;

class StringHelper
{
    public static function shortenString(String $str, Int $offset = 0, Int $length = 100, String $suffix = '')
    {
        $string = substr($str, $offset, $length);

        $string = preg_replace('/<\/?[^>]+|>/', ' ', $string); // Replace tag with whitespace
        $string = preg_replace('/(&[\w#]+;)+/', ' ', $string); // Trim html special char
        $string = preg_replace('/\s+/', ' ', $string); // Trim multiple whitespace

        return trim($string, " ") . $suffix;
    }

    public static function getYoutubeID(String $url)
    {
        /** Get uri segment */
        preg_match('/(?:[?&]v=|\/embed\/|\/1\/|\/v\/|https:\/\/(?:www\.)?youtu\.be\/)([^&\n?#]+)/', $url, $matches);

        /** Check ure pattern
         * possible uri segment (afaik) :
         * ?v=0zM3nApSvMg -> url : https://www.youtube.com/watch?v=0zM3nApSvMg&feature=feedrec_grec_index
         * ?v=0zM3nApSvMg -> url : https://www.youtube.com/watch?v=0zM3nApSvMg#t=0m10s
         * ?v=0zM3nApSvMg -> url : youtube.com/watch?v=0zM3nApSvMg
         * ?v=0zM3nApSvMg -> url : https://www.youtube.com/watch?v=0zM3nApSvMg
         * /embed/0zM3nApSvMg -> url : https://www.youtube.com/embed/0zM3nApSvMg?rel=0
         * /1/QdK8U-VIH_o -> url : https://www.youtube.com/user/IngridMichaelsonVEVO#p/a/u/1/QdK8U-VIH_o
         * /v/0zM3nApSvMg -> url : https://www.youtube.com/v/0zM3nApSvMg?fs=1&hl=en_US&rel=0
         * https://youtu.be/0zM3nApSvMg -> url : https://youtu.be/0zM3nApSvMg
         */


        switch ($matches[0]) {
            case strpos($matches[0], '?v=') !== false:
                $segments = explode('?v=', $matches[0]);
                return end($segments);
            case strpos($matches[0], '/embed/') !== false:
            case strpos($matches[0], '/embed') !== false:
            case strpos($matches[0], '/1/') !== false:
            case strpos($matches[0], '/1') !== false:
            case strpos($matches[0], '/v/') !== false:
            case strpos($matches[0], '/v') !== false:
            case strpos($matches[0], 'youtu.be') !== false:
                $segments = explode('/', $matches[0]);
                return end($segments);
            default:
                return '';
        }
    }
}
