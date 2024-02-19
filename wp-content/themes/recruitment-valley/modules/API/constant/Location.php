<?php

namespace Constant;

use Exception;

class LocationConstant
{
    private const cityDir   = THEME_DIR . '/assets/dependencies/cities/';
    private const countries = THEME_DIR . '/assets/dependencies/countries/all.json';

    public static function cities(String $country = 'all')
    {
        if ($country == 'all' || empty($country)) {
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

    public static function countries(String $code = '', String $result = 'all', Bool $associative = true, String $key = 'code')
    {
        if (file_exists(self::countries)) {
            $countries = file_get_contents(self::countries);
            if ($countries) {
                $countries = json_decode($countries, true);

                if ($code && !empty($code)) {
                    $country = $countries[$code];

                    if ($country && is_array($country)) {
                        switch ($result) {
                            case 'code':
                                return isset($country['code']) ? $country['code'] : null;
                                break;
                            case 'label':
                                return isset($country['label']) ? $country['label'] : null;
                                break;
                            case 'value':
                                return isset($country['value']) ? $country['value'] : null;
                                break;
                            case 'array':
                            default:
                                return $country;
                                break;
                        }
                    } else {
                        return null;
                    }
                } else {
                    switch ($result) {
                        case 'code':
                        case 'label':
                        case 'value':
                        case 'array':
                        case 'all-remap':
                            return self::remap($countries, $result, $associative, $key);
                            break;
                        case 'all':
                        default:
                            return $countries;
                            break;
                    }
                }
            } else {
                return null;
            }
        } else {
            throw new \Exception('Country list not found!');
        }
    }

    private static function remap($data, $return, $associative, $key)
    {
        if ($data && is_array($data)) {
            $result = [];
            foreach ($data as $code => $country) {
                if ($associative) {
                    if (in_array($key, ['code', 'value', 'label'])) {
                        $result[$country[$key]] = $country;
                    } else {
                        $result[$code] = $country;
                    }
                } else {
                    $result[] = $country[$return];
                }
            }

            return $result;
        } else {
            throw new Exception('Data is empty!');
        }
    }
}
