<?php

namespace Middleware;

use Constant\Message;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Helper\UserHelper;
use UnexpectedValueException;
use WP_Error;
use WP_REST_Request;
use WpOrg\Requests\Response;

class DevMiddleware
{
    private $_message;

    public function __construct()
    {
        $this->_message = new Message;
    }

    public function check_dev_key(WP_REST_Request $request)
    {
        $token = $request->get_header('Authorization');

        if ($token == "") {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.invalid_token'), array("status" => 403));
        }
        try {
            $decodedToken = JWT::decode($token, new Key(JWT_SECRET, "HS256"));

            /** Check if contain this claims. */
            if (!property_exists($decodedToken, 'sub') || !property_exists($decodedToken, 'password') || !property_exists($decodedToken, 'role') || !property_exists($decodedToken, 'exp')) {
                return new WP_Error("rest_forbidden", $this->_message->get('auth.invalid_token'), array("status" => 403, "message" => "Claim not complete!"));
            }

            /** Check claim value */
            if ($decodedToken->sub !== 'rv-for-dev-only') {
                return new WP_Error("rest_forbidden", $this->_message->get('auth.invalid_token'), array("status" => 403, "message" => "Wrong subject!"));
            }

            if ($decodedToken->password !== DEV_PASSWORD) {
                return new WP_Error("rest_forbidden", $this->_message->get('auth.invalid_token'), array("status" => 403, "message" => "Wrong password!"));
            }

            $request->set_param('dev', $decodedToken->name);
            $request->set_param('role', $decodedToken->role);
            return $request;
        } catch (ExpiredException $e) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.expired'), array("status" => 403));
        } catch (UnexpectedValueException $e) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.invalid_token'), array("status" => 403));
        }
    }
}
