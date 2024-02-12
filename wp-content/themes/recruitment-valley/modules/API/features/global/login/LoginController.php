<?php

namespace Global;

// require_once MODULES_DIR . "/API/features/global/notification/NotificationService.php";
// require_once MODULES_URL . "/API/features/global/notification/NotificationService.php";
// error_log(MODULES_URL . "/API/features/global/notification/NotificationService.php");
// error_log(MODULES_DIR . "/API/features/global/notification/NotificationService.php");
require_once get_stylesheet_directory() . "/vendor/firebase/php-jwt/src/JWT.php";

use BD\Emails\Email;
use Constant\Message;
use Firebase\JWT\JWT as JWT;
use Firebase\JWT\Key;
use DateTimeImmutable;
use JWTHelper;
use Model\Company;
use constant\NotificationConstant;
use Global\NotificationService;

class LoginController
{
    private $_message;

    private $reset_password_url = FRONTEND_URL . "/autorisatie/nieuw-wachtwoord";
    private $_notification;
    private $_notificationConstant;

    public function __construct()
    {
        $this->_message = new Message;
        $this->_notification = new NotificationService();
        $this->_notificationConstant = new NotificationConstant();
    }

    public function login($request)
    {
        $user = get_user_by("email", $request["email"]);

        if (!$user) {
            return [
                "message" => $this->_message->get('auth.not_found_user'),
                "status" => 400
            ];
        }

        $isDeleted = get_user_meta($user->ID, "is_deleted", true);

        if ($isDeleted) {
            // if(time() > get_user_meta( $user->ID, "reactivation_datetime", true ))
            // {
            $date = new DateTimeImmutable();
            $expired = $date->modify("+2 hours")->getTimestamp();

            $args = [
                "reactivation_link" => FRONTEND_URL . "/autorisatie/heractiveer-account?token=" . JWTHelper::generate(["user_id" => $user->ID, "exp" => $expired]),
                "user_name" => $user->user_nicename
            ];

            $content = Email::render_html_email('reactivation-email.php', $args);

            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
            );

            wp_mail($user->user_email, "Reactivate Acccount Email", $content, $headers);

            update_user_meta(
                $user->ID,
                "reactivation_datetime",
                $expired
            );

            return [
                "message" => $this->_message->get('auth.reactivation_sent'),
                "status" => 400
            ];
            // }

            return [
                "message" => $this->_message->get('auth.reactivation_sent'),
                "status" => 400
            ];
        }

        if (!get_user_meta($user->ID, 'otp_is_verified', true)) {
            return [
                "message" => $this->_message->get('auth.unfinish_registration'),
                "status" => 400
            ];
        }

        $credentials = [
            "user_login"    => $user->user_login,
            "user_password" => $request["password"],
            "remember"      => $request["rememberMe"] ?? false
        ];

        $checkUser = wp_signon($credentials, true);

        if (is_wp_error($checkUser)) {
            return [
                "message" => $this->_message->get('auth.invalid_credential'),
                "status" => 400
            ];
        }

        wp_set_current_user($checkUser->ID);
        wp_set_auth_cookie($checkUser->ID, $credentials["remember"], is_ssl());

        $key     = JWT_SECRET ?? "+3;@54)g|X?V%lWf+^4@3Xuu55*])bPX ftl1b>Nrd|w/]v[>bVgQm(m.#fAyAOV";
        $issuedAt     = new DateTimeImmutable();
        $timeToLive = $credentials["remember"] ? "+3 day" : "+60 minutes";
        /** For Access Token */
        $expireAccessToken  = $issuedAt->modify($timeToLive)->getTimestamp(); // valid until 60 minutes after toket issued

        /** Changes Start here */
        $extraClaims = [];
        if ($user->roles[0] == 'candidate') {
            $setupStatus = get_field("ucaa_is_full_registered", "user_" . $user->ID) ?? false;
        } else if ($user->roles[0] == 'company') {
            $setupStatus = get_field("ucma_is_full_registered", "user_" . $user->ID) ?? false;

            /** Additional line start here */
            $company = new Company($user->ID);
            $extraClaims = [
                'is_unlimited' => $company->checkUnlimited() ? true : false,
                // 'unlimited_expired_date' => $company->getUnlimitedExpired()
            ];
        } else if ($user->roles[0] == 'company-recruiter') {
            $setupStatus = true;
        }

        /** Anggit's Syntax start here */
        // $payloadAccessToken = [
        //     "exp" => $expireAccessToken,
        //     "user_id" => $user->ID,
        //     "user_email" => $user->user_email,
        //     "role" => $user->roles[0],
        //     // "setup_status" => get_field("ucaa_is_full_registered", "user_" . $user->ID) ?? false,
        //     "setup_status" => $setupStatus,
        // ];

        // /** For Refresh Token */
        // // $expireRefreshToken  = $issuedAt->modify("+120 minutes")->getTimestamp(); // valid until 60 minutes after toket issued
        // $payloadRefreshToken = [
        //     // "exp" => $expireRefreshToken, // make time-to-live unimited base on mas esa feedback on 18 August 2023
        //     "user_id" => $user->ID,
        //     "user_email" => $user->user_email,
        //     "role" => $user->roles[0],
        //     // "setup_status" => get_field("ucaa_is_full_registered", "user_" . $user->ID) ?? false,
        //     "setup_status" => $setupStatus,
        // ];
        /** Anggit's Syntax end here */

        /** Changes start here */
        $payloadAccessToken = array_merge([
            "exp" => $expireAccessToken,
            "user_id" => $user->ID,
            "user_email" => $user->user_email,
            "role" => $user->roles[0],
            // "setup_status" => get_field("ucaa_is_full_registered", "user_" . $user->ID) ?? false,
            "setup_status" => $setupStatus,
        ], $extraClaims);

        /** For Refresh Token */
        // $expireRefreshToken  = $issuedAt->modify("+120 minutes")->getTimestamp(); // valid until 60 minutes after toket issued
        $payloadRefreshToken = array_merge([
            // "exp" => $expireRefreshToken, // make time-to-live unimited base on mas esa feedback on 18 August 2023
            "user_id" => $user->ID,
            "user_email" => $user->user_email,
            "role" => $user->roles[0],
            // "setup_status" => get_field("ucaa_is_full_registered", "user_" . $user->ID) ?? false,
            "setup_status" => $setupStatus,
        ], $extraClaims);
        /** Changes end here */

        // store refresh token to db
        update_user_meta($user->ID, 'refresh_token', JWT::encode($payloadRefreshToken, $key, 'HS256'));

        return [
            "message" => $this->_message->get("auth.login_success"),
            "data" => [
                "token" => JWT::encode($payloadAccessToken, $key, 'HS256'),
                "refreshToken" => JWT::encode($payloadRefreshToken, $key, 'HS256'),
            ],
            "status" => 200
        ];
    }

    public function logout($request)
    {
        // Set jwt to blacklist
        // $_SESSION['JWT_BLACKLIST'][] = $jwtoken;

        // Remove refresh token from meta
        update_user_meta($request['user_id'], 'refresh_token', '');

        return [
            "message"   => $this->_message->get("auth.logout_success"),
            "status"    => 200
        ];
    }

    public function forgot_password($request)
    {
        $email = $request["email"];

        if (!isset($email) || $email == "") {
            return [
                "message" => $this->_message->get('auth.forgot_password.required_email'),
                "status" => 400
            ];
        }

        $user = get_user_by('email', $email);

        if (!$user) {
            return [
                "message" => $this->_message->get('auth.not_found_user'),
                "status" => 400
            ];
        }

        $user_login = $user->user_login;
        $key = wp_generate_uuid4();

        update_user_meta($user->ID, "reset_password_key", $key);

        $reset_password_token = base64_encode($user_login . "_" . $key);
        $reset_password_url = $this->reset_password_url . "?key=" . $reset_password_token;

        $site_title = get_bloginfo('name');
        $subject = sprintf(__('Wachtwoord opnieuw instellen - %s', 'THEME_DOMAIN'), $site_title);

        $args = [
            'reset_url'     => $reset_password_url,
            'user.email'    => $user->user_email,
            'user.name'     => $user->first_name !== '' ? $user->first_name : $user->user_email,
        ];

        $email_sent = Email::send($user->user_email, $subject, $args, 'reset-password.php');

        /** Create notification : payment success */
        $this->_notification->write($this->_notificationConstant::ACCOUNT_PASSWORD_FORGOT, $user->ID, [
            'id' => $user->ID
        ]);

        if ($email_sent) {
            return [
                "status" => 200,
                "message" => $this->_message->get("auth.forgot_password.email_sent")
            ];
        } else {
            return [
                "status" => 400,
                "message" => $this->_message->get("auth.forgot_password.email_not_sent")
            ];
        }
    }

    public function reset_password($request)
    {
        $newPassword = $request["newPassword"];
        $repeatPassword = $request["repeatNewPassword"];
        $key = base64_decode($request["key"]);

        $errors = [];

        if (!isset($newPassword) || $newPassword === "") {
            array_push($errors, ["key" => "newPassword", "message" => $this->_message->get("auth.reset_password.new_password_required")]);
        }

        if (!isset($repeatPassword) || $repeatPassword === "") {
            array_push($errors, ["key" => "repeatNewPassword", "message" => $this->_message->get("auth.reset_password.repeat_password_required")]);
        }

        // if ($repeatPassword !== $repeatPassword) { // Changed Below
        if ($newPassword !== $repeatPassword) {
            array_push($errors, ["key" => "passwordMissmatch", "message" => $this->_message->get("auth.reset_password.password_missmatch")]);
        }

        if (count($errors) > 0) {
            return [
                "status" => 400,
                "message" => $errors
            ];
        }

        $user_login = explode("_", $key)[0];
        $reset_password_key = explode("_", $key)[1];

        $user = get_user_by('login', $user_login);

        if (!$user) {
            return [
                "status" => 400,
                "message" => $this->_message->get("auth.not_found_user")
            ];
        }

        $user_reset_password_key = get_user_meta($user->ID, "reset_password_key", true);

        if ($reset_password_key !== $user_reset_password_key) {
            return [
                "status" => 400,
                "message" => $this->_message->get("auth.reset_password.incorrect_key"),
            ];
        }

        reset_password($user, $newPassword);
        update_user_meta($user->ID, "reset_password_key", ''); // Added Line

        /** Create notification : payment success */
        $this->_notification->write($this->_notificationConstant::ACCOUNT_PASSWORD_RESET, $user->ID, [
            'id' => $user->ID
        ]);

        return [
            "status" => 200,
            "message" => $this->_message->get('auth.reset_password.success'),
        ];
    }
}

new LoginController();
