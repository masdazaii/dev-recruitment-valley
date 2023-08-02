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
        if (!isset($request["email"]) || !isset($request["password"]) || $request["email"] == "" || $request["password"] == "") {
            return [
                "message" => "Invalid Input.",
                "status" => 400
            ];
        }

        $user = get_user_by("email", $request["email"]);

        if(!$user)
        {
            return [
                "message" => "Invalid Input.",
                "status" => 400
            ];
        }

        $credentials = [
            "user_login"    => $user->user_login,
            "user_password" => $request["password"],
            "remember"      => $request["remember"] ?? false
        ];

        $checkUser = wp_signon($credentials, true);

        if (is_wp_error($checkUser)) {
            return [
                "message" => "Credentials is invalid.",
                "status" => 400
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
            // "sub" => $user->ID,
            "user_id" => $user->ID,
            "role" => $user->user_role[0],
            "setup_status" => false
        ];

        /** For Refresh Token */
        // $expireRefreshToken  = $issuedAt->modify("+120 minutes")->getTimestamp(); // valid until 60 minutes after toket issued
        $payloadRefreshToken = [
            // "iat" => $issuedAt,
            // "nbf" => $issuedAt,
            // "sub" => $user->ID,
            // "exp" => $expireRefreshToken,
            "user_id" => $user->ID
        ];

        return [
            "message" => "Login success.",
            "data" => [
                "token" => JWT::encode($payloadAccessToken, $key, 'HS256'),
                "refreshToken" => JWT::encode($payloadRefreshToken, $key, 'HS256')
            ],
            "status" => 200
        ];
    }

    public function logout($jwtoken)
    {
        if (!isset($jwtoken) || $jwtoken === '') {
            return [
                "message"   => "Token is not provided.",
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
            "message"   => "User logged out succesfully.",
            "status"    => 200
        ];
    }
}

new LoginController();
