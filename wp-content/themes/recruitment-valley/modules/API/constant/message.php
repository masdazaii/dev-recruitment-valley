<?php

namespace Constant;
class Message
{
    public $list;

    public function __construct()
    {
        $this->list = [
            'auth' => [
                'unauthenticate' => __('Unauthorized', THEME_DOMAIN),
                'invalid_token' => __('Unauthorized', THEME_DOMAIN),
                'expired' => __('Unauthorized', THEME_DOMAIN),
            ],
        ];
    }

    public function get($message_location)
    {
        $keys = explode('.', $message_location);
        $message = $this->list;
        
        foreach ($keys as $key) {
            if (isset($message[$key])) {
                $message = $message[$key];
            } else {
                return null; // Key not found, return null or any other default value you prefer.
            }
        }

        return $message;
    }
}