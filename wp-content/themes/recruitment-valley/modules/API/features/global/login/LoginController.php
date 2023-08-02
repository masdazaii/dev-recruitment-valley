<?php

namespace Global;

require_once get_stylesheet_directory() . "/vendor/firebase/php-jwt/src/JWT.php";

use Firebase\JWT\JWT as JWT;
use Firebase\JWT\Key;
use DateTimeImmutable;

class LoginController
{
    public function login($request)
    {
        /** !is */
        if (!isset($request["email"]) && !isset($request["password"])) {
            return [
                "success" => false,
                "message" => "Invalid Input.",
                "data" => [],
                "statusCode" => 400
            ];
        }

        $user = get_user_by("email", $request["email"]);
        $credentials = [
            "user_login"    => $user->user_login,
            "user_password" => $request["password"],
            "remember"      => $request["remember"] ?? false
        ];

        $checkUser = wp_signon($credentials, true);

        if (is_wp_error($checkUser)) {
            return [
                "success" => false,
                "message" => "Credentials is invalid.",
                "statusCode" => 400
            ];
        }
        wp_set_current_user($checkUser->ID);
        wp_set_auth_cookie($checkUser->ID, $credentials["remember"], is_ssl());

        $key     = JWT_SECRET ?? "+3;@54)g|X?V%lWf+^4@3Xuu55*])bPX ftl1b>Nrd|w/]v[>bVgQm(m.#fAyAOV";
        // $issuedAt     = new DateTimeImmutable();

        /** For Access Token */
        // $expireAccessToken  = $issuedAt->modify("+60 minutes")->getTimestamp(); // valid until 60 minutes after toket issued
        $payloadAccessToken = [
            // "iat" => $issuedAt,
            // "nbf" => $issuedAt,
            // "exp" => $expireAccessToken,
            // "sub" => $user->user_id,
            "user_id" => $user->user_id,
            "role" => $user->user_role[0],
            "setup_status" => false
        ];

        /** For Refresh Token */
        // $expireRefreshToken  = $issuedAt->modify("+120 minutes")->getTimestamp(); // valid until 60 minutes after toket issued
        $payloadRefreshToken = [
            // "iat" => $issuedAt,
            // "nbf" => $issuedAt,
            // "sub" => $user->user_id,
            // "exp" => $expireRefreshToken,
            "user_id" => $user->user_id
        ];

        return [
            "success" => true,
            "message" => "Login success.",
            "data" => [
                "token" => JWT::encode($payloadAccessToken, $key, 'HS256'),
                "refreshToken" => JWT::encode($payloadRefreshToken, $key, 'HS256')
            ],
            "statusCode" => 200
        ];

        // return [
        //     "success" => true,
        //     "message" => "heloo world",
        //     "data" => [],
        //     "statusCode" => 200
        // ];
    }

    public function logout()
    {
        /** Get Content-type Header */
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

        if (stripos($contentType, 'application/json')) {
            $json = file_get_contents('php://input');
            $data = json_decode($json);
            $request = [
                "email"     => sanitize_email($data["email"]),
                "password"  => sanitize_text_field($data["password"]),
                "remember"  => $data["rememberMe"]
            ];
        } else if (stripos($contentType, 'application/x-www-form-urlencoded')) {
            $request = [
                "email"     => sanitize_email($_POST["email"]),
                "password"  => sanitize_text_field($_POST["password"]),
                "remember"  => $_POST["rememberMe"]
            ];
        }
    }
}

new LoginController();
