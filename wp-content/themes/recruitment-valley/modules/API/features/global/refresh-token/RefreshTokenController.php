<?php

namespace RefreshToken;

use Constant\Message;
use DomainException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use JWTHelper;
use UnexpectedValueException;

class RefreshTokenController
{
    private $_message;

    public function __construct()
    {
        $this->_message = new Message;
    }

    public function refresh($request)
    {
        $token = $request["refresh_token"];

        if ($token == "") {
            return [
                "status" => 401,
                "message" => $this->_message->get("auth.invalid_token")
            ];
        }

        try {
            $decodedToken = JWT::decode($token, new Key(JWT_SECRET, "HS256"));

            $user = get_user_by('id', $decodedToken->user_id);

            /** If token not same as refresh_token meta */
            if ($token !== get_user_meta($decodedToken->user_id, 'refresh_token', true)) {
                return [
                    "status" => 401,
                    "message" => $this->_message->get("auth.invalid_token")
                ];
            }

            if (!$user) {
                return [
                    "status" => 401,
                    "message" => $this->_message->get("auth.invalid_token")
                ];
            }

            /** Changes Star here */
            if ($decodedToken->role == 'candidate') {
                $setupStatus = get_field("ucaa_is_full_registered", "user_" . $user->ID) ?? false;
            } else if ($decodedToken->role == 'company') {
                $setupStatus = get_field("ucma_is_full_registered", "user_" . $user->ID) ?? false;
            }

            $payloadNewAccessToken = [
                "user_id" => $user->ID,
                "role" => $user->roles[0],
                // "setup_status" => get_field("ucaa_is_full_registered", "user_" . $user->ID) ?? false,
                "setup_status" => $setupStatus,
            ];

            $payloadNewrefreshToken = [
                "user_id" => $user->ID,
                "role" => $user->roles[0],
                // "setup_status" => get_field("ucaa_is_full_registered", "user_" . $user->ID) ?? false,
                "setup_status" => $setupStatus,
            ];

            $newToken = JWTHelper::generate($payloadNewAccessToken, "+60  minutes");
            $newRefreshToken = JWTHelper::generate($payloadNewrefreshToken, ""); // Make time-to-live unimited base on mas esa feedback on 18 August 2023

            update_user_meta($user->ID, "refresh_token", $newRefreshToken);

            if ($newToken && $newRefreshToken) {
                return [
                    "status" => 201,
                    "message" => $this->_message->get('auth.generate_token_success'),
                    "data" => [
                        "token" => $newToken,
                        "refreshToken" => $newRefreshToken
                    ]
                ];
            } else {
                return [
                    "status" => 500,
                    "message" => $this->_message->get('auth.generate_token_error')
                ];
            }
        } catch (DomainException $e) {
            return [
                "status" => 401,
                "message" => $e->getMessage()
            ];
        } catch (ExpiredException $e) {
            return [
                "status" => 401,
                "message" => $e->getMessage()
            ];
        } catch (UnexpectedValueException $e) {
            return [
                "status" => 401,
                "message" => $e->getMessage()
            ];
        }
    }
}
