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
                'not_found_user' => __("User was not found", THEME_DOMAIN),
                'unfinish_registration' => __("Unfinished registration.", THEME_DOMAIN),
                'invalid_credential' => __("Credentials is invalid.", THEME_DOMAIN),
                'required_credential' => __("Credentials is required.", THEME_DOMAIN),
                'login_success' => __("Login succes.", THEME_DOMAIN),
                'logout_success' => __("User logged out succesfully.", THEME_DOMAIN),
                'token_not_provided' => __("Token is not provided.", THEME_DOMAIN),
            ],
            'registration' => [
                'email_required' => __("Email is required.", THEME_DOMAIN),
                'email_wrong' => __("Please input valid email.", THEME_DOMAIN),
                'role_wrong' => __("Please select valid role.", THEME_DOMAIN),
                'email_invalid' => __("Email is invalid.", THEME_DOMAIN),
                'otp_required' => __("OTP is required.", THEME_DOMAIN),
                'otp_invalid' => __("OTP is invalid.", THEME_DOMAIN),
                'otp_expired' => __("OTP is expired.", THEME_DOMAIN),
                'new_otp_success' => __("Sucess get new OTP code.", THEME_DOMAIN),
                'new_otp_failed' => __("Failed to send new OTP code.", THEME_DOMAIN),
                'failed_verify_otp' => __("System Error, failed to verify OTP.", THEME_DOMAIN),
                'success_verify_otp' => __("OTP is verified!.", THEME_DOMAIN),
                'already_registered_user' => __("User is already registered.", THEME_DOMAIN),
                'registration_success' => __("We have sent an OTP code to your email. Please check your email and type in the code.", THEME_DOMAIN),
                'not_registered' => __("User with given email, not yet registered.", THEME_DOMAIN)
            ],
            'profile' => [
                'setup' => [
                    'success' =>  __("success setup profile", THEME_DOMAIN),
                ],
                'update' => [
                    'success' =>  __("success update profile", THEME_DOMAIN),
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
                "term" => [
                    'get_term_success' => __("Success get vacancies' filters.", THEME_DOMAIN),
                ],
                "create" => [
                    "free" => [
                        "success" => __("We have received your vacancy submission. We will review your vacancy in up to 2 business days", THEME_DOMAIN),
                        "fail" => __("Error creating free job", THEME_DOMAIN),
                    ],
                    "paid" => [
                        "success" => __("We have received your vacancy submission", THEME_DOMAIN),
                        "fail" => __("Error creating paid job", THEME_DOMAIN),

                    ]
                ]
            ],
            'candidate' => [
                "apply_vacancy" => [
                    "apply_success" => __("Success apply this job.", THEME_DOMAIN),
                    "apply_failed" => __("Failed apply this job.", THEME_DOMAIN),
                    "expired_job" => __("Failed, the job already expired", THEME_DOMAIN),
                ]
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
