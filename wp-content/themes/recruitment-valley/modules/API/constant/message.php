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
            'profile' => [
                'setup' => [
                    'success' =>  __("success setup profile", THEME_DOMAIN)
                ]
            ],
            'input' => [
                'invalid_input'   => __("Input is invalid.", THEME_DOMAIN),
                'failed_to_store' => __("Failed to store data.", THEME_DOMAIN),
            ],
            'contact' => [
                'invalid_input' => [
                    'phone_number_required' => __("Phone number is required.", THEME_DOMAIN),
                    'phone_number_code_required' => __("Phone number code is required.", THEME_DOMAIN),
                    'email_required' => __("Email is required.", THEME_DOMAIN),
                    'email_invalid' => __("Email is invalid.", THEME_DOMAIN),
                    'company_name_required' => __("Company name is required.", THEME_DOMAIN),
                    'company_sender_name_required' => __("Name is required.", THEME_DOMAIN),
                    'job_seeker_first_name_required' => __("First name is required.", THEME_DOMAIN),
                    'job_seeker_last_name_required' => __("Last name is required.", THEME_DOMAIN),
                    'message_required' => __("Message is required.", THEME_DOMAIN),
                ],
                'success'   => __("We have received your submission. We will reach back to you soon!", THEME_DOMAIN)
            ],
            'vacancy' => [
                "get_all" => __("Success get vacancies"),
                "not_found" => __("there is no vancancy found base on your criteria", THEME_DOMAIN),
            ]
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
