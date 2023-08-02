<?php

require_once get_stylesheet_directory() . "/vendor/firebase/php-jwt/src/JWT.php";

use Firebase\JWT\JWT as JWT;

class JWTHelper
{
    private $registeredClaims;
    private $issuedAt;

    public function __construct()
    {
        $this->issuedAt = new DateTimeImmutable;

        $this->registeredClaims = [
            "iat" => $this->issuedAt,
            "iat" => $this->issuedAt,
            "nbf" => $this->issuedAt
        ];
    }

    public function generate(array $claims, string $timeToLive, $algorithm = 'HS256')
    {
        $key    = JWT_SECRET ?? "+3;@54)g|X?V%lWf+^4@3Xuu55*])bPX ftl1b>Nrd|w/]v[>bVgQm(m.#fAyAOV";

        $payload = array_merge($claims, $this->registeredClaims);

        $issuedAt = $this->issuedAt;
        $payload['exp'] = $issuedAt->modify($timeToLive)->getTimestamp();

        return JWT::encode($payload, $key, $algorithm);
    }
}
