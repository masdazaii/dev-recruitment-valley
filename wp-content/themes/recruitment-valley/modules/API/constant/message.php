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
                'generate_token_success' => __('Success generating token', THEME_DOMAIN),
                'generate_token_error' => __('Something error when generating token', THEME_DOMAIN),
                'forgot_password' => [
                    "required_email" => __('Email was required', THEME_DOMAIN),
                    "email_sent" => __('Email already sent', THEME_DOMAIN),
                    "email_not_sent" => __('Email wasnt sent', THEME_DOMAIN),
                ],
                "reset_password" => [
                    "new_password_required" => __("New Password required", THEME_DOMAIN),
                    "repeat_password_required" => __("Repeat Password required", THEME_DOMAIN),
                    "key_required" => __("Key required", THEME_DOMAIN),
                    "password_missmatch" => __("Your password missmatch", THEME_DOMAIN),
                    "incorrect_key" => __("Incorrect key for requested user", THEME_DOMAIN),
                    'success' => __("success reset password", THEME_DOMAIN),
                ],
                'not_found_user' => __("User was not found"),
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