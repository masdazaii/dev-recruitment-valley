<?php

namespace Helper;

class ValidationHelper
{
    public static function validate($request, $rules)
    {
        $response = [
            'is_valid'  => true,
            'errors'    => []
        ];

        if (gettype($rules) === 'string') {
            $rules = array_unique(explode('|', $rules));
        }

        foreach ($rules as $key => $value) {
            switch ($value) {
                case 'required':
                    $check = self::_require($request[$key], $key);
                    if (!$check['is_valid']) {
                        $response['is_valid'] = false;
                        $response[$key][] = $check['message'];
                    }
                    break;
            }
        }

        return $response;
    }

    private static function _require($data, $key = null)
    {
        if (!isset($data) || $data == '') {
            return [
                'is_valid'  => false,
                'message'   => 'Field ' . $key ?? '' . ' is required.'
            ];
        }
    }
}
