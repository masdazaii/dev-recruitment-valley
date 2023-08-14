<?php

namespace Global;

require_once get_stylesheet_directory() . "/vendor/firebase/php-jwt/src/JWT.php";

use Constant\Message;
use Firebase\JWT\JWT as JWT;
use Firebase\JWT\Key;
use DateTimeImmutable;

class LoginController
{
    private $_message;

    private $reset_password_url = "dev-recruitment-valley.test";

    public function __construct()
    {
        $this->_message = new Message;
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

        $payloadAccessToken = [
            "exp" => $expireAccessToken,
            "user_id" => $user->ID,
            "role" => $user->roles[0],
            "setup_status" => get_field("ucaa_is_full_registered", "user_" . $user->ID) ?? false,
        ];

        /** For Refresh Token */
        $expireRefreshToken  = $issuedAt->modify("+120 minutes")->getTimestamp(); // valid until 60 minutes after toket issued
        $payloadRefreshToken = [
            "exp" => $expireRefreshToken,
            "user_id" => $user->ID,
            "role" => $user->roles[0],
            "setup_status" => get_field("ucaa_is_full_registered", "user_" . $user->ID) ?? false,
        ];

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

    public function logout($jwtoken)
    {
        if (!isset($jwtoken) || $jwtoken === '') {
            return [
                "message"   => $this->_message->get("auth.token_not_provided"),
                "status"    => 403
            ];
        }

        $key     = JWT_SECRET ?? "+3;@54)g|X?V%lWf+^4@3Xuu55*])bPX ftl1b>Nrd|w/]v[>bVgQm(m.#fAyAOV";
        $payload = JWT::decode($jwtoken, new Key($key, 'HS256'));

        // Set jwt to blacklist
        // $_SESSION['JWT_BLACKLIST'][] = $jwtoken;

        // Remove refresh token from meta
        update_user_meta($payload->user_id, 'refresh_token', '');

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

        $subject = 'Password Reset';
        $message = '<p>Hallo ' . $user_login . '</p>
        <p>Please click on the following link to reset your password: </p>  <a href="' . $reset_password_url . '">' . $reset_password_url . '</a>';

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Recruitment Valley <info@recruitmentvalley.com>',
        );

        $email_sent = wp_mail($user->user_email, $subject, $message, $headers);

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

        if ($repeatPassword !== $repeatPassword) {
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

        return [
            "status" => 200,
            "message" => $this->_message->get('auth.reset_password.success'),
        ];
    }
}

new LoginController();
