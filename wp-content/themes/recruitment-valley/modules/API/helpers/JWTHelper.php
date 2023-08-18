<?php

require_once get_stylesheet_directory() . "/vendor/firebase/php-jwt/src/JWT.php";

use Constant\Message;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT as JWT;
use Firebase\JWT\Key;

class JWTHelper
{
    private static $registeredClaims = [];
    private static $issuedAt;
    private static $_message;

    public function __construct()
    {
        self::$issuedAt = new DateTimeImmutable;

        self::$_message = new Message;

        self::$registeredClaims = [
            // "iat" => self::$issuedAt,
            // "nbf" => self::$issuedAt
        ];
    }

    /**
     * Generate HWT function
     *
     * @param array $claims
     * @param string $timeToLive -> set empty string for unlimited token life-time
     * @param string $algorithm
     * @return void
     */
    public static function generate(array $claims, string $timeToLive = '', $algorithm = 'HS256')
    {
        $key    = JWT_SECRET ?? "+3;@54)g|X?V%lWf+^4@3Xuu55*])bPX ftl1b>Nrd|w/]v[>bVgQm(m.#fAyAOV";

        $payload = array_merge($claims, self::$registeredClaims);

        if ($timeToLive !== '') {
            $issuedAt = self::$issuedAt;
            $payload['exp'] = $issuedAt->modify($timeToLive)->getTimestamp();
        }

        return JWT::encode($payload, $key, $algorithm);
    }

    public static function check($token)
    {
        if ($token == "") {
            return [
                "status" => 400,
                "message" => "Token Invalid"
            ];
        }

        try {
            $decodedToken = JWT::decode($token, new Key(JWT_SECRET, "HS256"));

            return $decodedToken;
        } catch (ExpiredException $e) {
            return [
                "status" => 400,
                "message" => self::$_message->get('auth.expired')
            ];
        } catch (UnexpectedValueException $e) {
            return [
                "status" => 400,
                "message" => "Token Invalid"
            ];
        }
    }
}

new JWTHelper();
