<?php

namespace Global;

use DateTimeImmutable;
use WP_User;
use Helper\OTPHelper as OTP;

class RegistrationController
{
    public function registration($request)
    {
        if (!isset($request["email"]) || !isset($request["password"])) {
            return [
                "success"       => false,
                "message"       => "Invalid Input.",
                "statusCode"    => 400
            ];
        }

        $addUser = wp_insert_user([
            "user_login" => $request["email"],
            "user_email" => $request["email"],
            "user_pass"  => wp_hash_password($request["password"])
        ]);

        if (is_wp_error($addUser)) {
            return [
                "success"       => false,
                "message"       => "Registration Failed.",
                "statusCode"    => 500
            ];
        }

        /** Set registered Status */
        update_field('is_full_registered', '0', $addUser);

        /** Set Roles */
        $thisUser = new WP_User($addUser);
        $thisUser->set_role($request['accountType']);

        /** Create OTP */
        $otp = OTP::generate(6);
        $otpCreatedAt = new DateTimeImmutable();
        $otpExpiredAt = $otpCreatedAt->modify("+10 minute")->format("Y-m-d H:i:s");
        add_user_meta($addUser, "otp", $otp);
        add_user_meta($addUser, "otp_expired", $otpExpiredAt);

        /** Send OTP code */
        wp_mail($request["email"], "One Time Password", "Your OTP : " . $otp);

        return [
            "success"       => true,
            "message"       => "We have sent an OTP code to your email. Please check your email and type in the code.",
            "statusCode"    => 201
        ];
    }

    public function validateOTP($request)
    {
        print('<pre>' . print_r($request['email'], true) . '</pre>');
        if (!isset($request['otp']) || !isset($request['email'])) {
            $message = !isset($request['email']) ? 'Email is required.' : '';
            $message = !isset($request['otp']) ? 'OTP is required.' : '';

            return [
                "success"        => false,
                "message"       => $message,
                "errors"        => [
                    "otp"       => "OTP is required"
                ],
                "statusCode"    => 400
            ];
        }

        $user = get_user_by("email", $request["email"]);

        /** Get user otp meta id */
        $otpMetaValue = get_user_meta($user->ID, 'otp', true);
        $otpExpiredMetaValue = get_user_meta($user->ID, 'otp_expired', true);

        /** Validate OTP */
        if ($otpMetaValue !== $request['otp']) {
            return [
                "success"        => false,
                "message"       => "OTP is invalid.",
                "data"        => [
                    "otp"       => "OTP is invalid"
                ],
                "statusCode"    => 400
            ];
        }

        $now = time();
        if (strtotime($otpExpiredMetaValue) > $now) {
            return [
                "success"        => false,
                "message"       => "Invalid Input.",
                "errors"        => [
                    "otp"       => "OTP is invalid"
                ],
                "statusCode"    => 400
            ];
        }

        return [
            "success"       => true,
            "message"       => "OTP is valid.",
            "statusCode"    => 200
        ];
    }
}

new RegistrationController();
