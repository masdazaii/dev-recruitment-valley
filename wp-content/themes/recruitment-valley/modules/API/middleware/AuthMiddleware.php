<?php

namespace Middleware;

use Constant\Message;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use UnexpectedValueException;
use WP_Error;
use WP_REST_Request;
use WpOrg\Requests\Response;

class AuthMiddleware
{
    private $_message;

    public function __construct()
    {
        $this->_message = new Message;
    }

    public function check_token_candidate(WP_REST_Request $request)
    {
        $this->check_token($request);
        $user = get_user_by('ID', $request->user_id);

        if (!in_array('candidate', $user->roles))
            return new WP_Error("rest_unauthorized", $this->_message->get('auth.invalid_token'), array("status" => 401));

        return $request;
    }

    public function check_token(WP_REST_Request $request)
    {
        $token = $request->get_header('Authorization');

        if ($token == "") {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.invalid_token'), array("status" => 401));
        }
        try {
            $decodedToken = JWT::decode($token, new Key(JWT_SECRET, "HS256"));
            $request->user_id = $decodedToken->user_id;

            return $request;
        } catch (ExpiredException $e) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.expired'), array("status" => 401));
        } catch (UnexpectedValueException $e) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.invalid_token'), array("status" => 401));
        }
    }
}
