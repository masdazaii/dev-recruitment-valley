<?php

namespace Global;

use Helper\OTPHelper as OTP;

class RegistrationController
{
    public function registration($params1 = null)
    {
        /** Get Content-type Header */
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

        if (stripos($contentType, 'application/json')) {
            $json = file_get_contents('php://input');
            $data = json_decode($json);
            $request = [
                "email"         => sanitize_email($data["email"]),
                "password"      => sanitize_text_field($data["password"]),
                "accountType"   => $data["accountType"]
            ];
        } else if (stripos($contentType, 'application/x-www-form-urlencoded')) {
            $request = [
                "email"         => sanitize_email($_POST["email"]),
                "password"      => sanitize_text_field($_POST["password"]),
                "accountType"   => $_POST["accountType"]
            ];
        }

        // $addUser = wp_insert_user([
        //     'user_email' => $request['email'],
        //     'user_pass'  => wp_hash_password($request['password'])
        // ]);
        $addUser = true;

        if ($addUser) {
            $otp = OTP::generate(6);

            return [
                'success'   => true,
                'message'   => 'Registration Success.',
                'data'      => [
                    'otp'   => $otp,
                    'test'  => $params1,
                    'req'   => $request
                ],
                'statusCode' => 200
            ];
        } else {
            return [
                'success'   => false,
                'message'   => 'Registration Failed.',
                'statusCode' => 500
            ];
        }
    }
}

new RegistrationController();
