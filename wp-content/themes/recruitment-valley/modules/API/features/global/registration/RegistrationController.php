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
                "message"   => "Invalid Input.",
                "status"    => 400
            ];
        }

        $userExist = get_user_by('email', $request['email']);
        if ($userExist) {
            $userValidated = get_user_meta($userExist->ID, 'otp_is_verified', true);
            if ($userValidated && $userValidated == '1') {
                return [
                    "message"   => "User is already registered.",
                    "status"    => 400
                ];
            }

            if (wp_check_password($request['password'], $userExist->user_pass, $userExist->ID)) {
                $addUser = $userExist->ID;
            }
        } else {
            $addUser = wp_insert_user([
                "user_login" => $request["email"],
                "user_email" => $request["email"],
                "user_pass"  => $request["password"]
            ]);
        }

        if (is_wp_error($addUser)) {
            return [
                "message"   => "Registration Failed. ".$addUser->get_error_message(),
                "status"    => 400
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
        update_user_meta($addUser, "otp", $otp);
        update_user_meta($addUser, "otp_expired", $otpExpiredAt);
        update_user_meta($addUser, "otp_is_verified", "0"); // Set OTP verified status, if 1 => true / validated, 0 => false / invalid

        /** Send OTP code */
        wp_mail($request["email"], "One Time Password", "Your OTP : " . $otp);

        return [
            "message"   => "We have sent an OTP code to your email. Please check your email and type in the code.",
            "status"    => 201
        ];
    }

    public function validateOTP($request)
    {
        if (!isset($request['otp']) || !isset($request['email'])) {
            $message = !isset($request['email']) ? 'Email is required.' : '';
            $message = !isset($request['otp']) ? 'OTP is required.' : '';

            return [
                "message"       => $message,
                "errors"        => [
                    "otp"       => "OTP is required"
                ],
                "status"    => 400
            ];
        }

        $user = get_user_by("email", $request["email"]);

        /** Get user otp meta id */
        $otpMetaValue = get_user_meta($user->ID, 'otp', true);
        $otpExpiredMetaValue = get_user_meta($user->ID, 'otp_expired', true);

        /** Validate OTP */
        if ($otpMetaValue !== $request['otp']) {
            return [
                "message"       => "OTP is invalid.",
                "errors"        => [
                    "otp"       => "OTP is invalid"
                ],
                "status"    => 400
            ];
        }

        $now = time();
        if (strtotime($otpExpiredMetaValue) < $now) {
            return [
                "message"       => "OTP is expired.",
                "errors"        => [
                    "otp"       => "OTP is expired"
                ],
                "status"    => 400
            ];
        }

        $setOTPVerified = update_user_meta($user->ID, "otp_is_verified", "1");
        if (!$setOTPVerified) {
            return [
                "message"   => "System Error, failed to verify otp.",
                "status"    => 500
            ];
        }

        return [
            "message"       => "OTP is valid.",
            "status"    => 200
        ];
    }
}

new RegistrationController();
