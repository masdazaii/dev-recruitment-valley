<?php

require_once get_stylesheet_directory() . "/vendor/firebase/php-jwt/src/JWT.php";

use Firebase\JWT\JWT as JWT;

class JWTHelper
{
    private static $registeredClaims = [];
    private static $issuedAt;

    public function __construct()
    {
        self::$issuedAt = new DateTimeImmutable;

        self::$registeredClaims = [
            // "iat" => self::$issuedAt,
            // "nbf" => self::$issuedAt
        ];
    }

    public static function generate(array $claims, string $timeToLive, $algorithm = 'HS256')
    {
        $key    = JWT_SECRET ?? "+3;@54)g|X?V%lWf+^4@3Xuu55*])bPX ftl1b>Nrd|w/]v[>bVgQm(m.#fAyAOV";

        $payload = array_merge($claims, self::$registeredClaims);

        $issuedAt = self::$issuedAt;
        $payload['exp'] = $issuedAt->modify($timeToLive)->getTimestamp();

        return JWT::encode($payload, $key, $algorithm);
    }
}

new JWTHelper();
