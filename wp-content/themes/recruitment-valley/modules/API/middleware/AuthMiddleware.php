<?php

namespace Middleware;

use Constant\Message;
use Exception;
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

        if (!$user || !in_array('candidate', $user->roles))
            return new WP_Error("rest_unauthorized", $this->_message->get('auth.invalid_token'), array("status" => 403));

        return $request;
    }

    public function check_token_company(WP_REST_Request $request)
    {
        $this->check_token($request);
        $user = get_user_by('ID', $request->user_id);

        if (!$user || !in_array('company', $user->roles))
            return new WP_Error("rest_unauthorized", $this->_message->get('auth.invalid_token'), array("status" => 403));

        return $request;
    }

    public function check_token(WP_REST_Request $request)
    {
        $token = $request->get_header('Authorization');

        if ($token == "") {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.invalid_token'), array("status" => 403));
        }
        try {
            $decodedToken = JWT::decode($token, new Key(JWT_SECRET, "HS256"));
            $request->user_id = $decodedToken->user_id;

            return $request;
        } catch (ExpiredException $e) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.expired'), array("status" => 403));
        } catch (UnexpectedValueException $e) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.invalid_token'), array("status" => 403));
        }
    }

    /** Function to handle and authorize user role */
    private function _handle_token($request)
    {
        $token = $request->get_header('Authorization');

        if ($token == "") {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.invalid_token'), array("status" => 403));
        }
        try {
            $decodedToken = JWT::decode($token, new Key(JWT_SECRET, "HS256"));

            return $decodedToken;
        } catch (ExpiredException $e) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.expired'), array("status" => 403));
        } catch (UnexpectedValueException $e) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.invalid_token'), array("status" => 403));
        }
    }

    public function authorize_candidate(WP_REST_Request $request)
    {
        $allowed = ['candidate'];
        $handleToken = $this->_handle_token($request);

        if (is_wp_error($handleToken)) {
            return $handleToken;
        }

        $user = get_user_by('ID', $handleToken->user_id);

        if (!in_array(strtolower($user->roles[0]), $allowed)) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.unauthenticate'), array("status" => 403));
        }

        // $request->set_param('user_id', $request->user_id); // this will take the user_id of the currently logged in user
        $request->set_param('user_id', $handleToken->user_id);
        return true;
    }

    public function authorize_company(WP_REST_Request $request)
    {
        $allowed = ['company'];
        $handleToken = $this->_handle_token($request);

        if (is_wp_error($handleToken)) {
            return $handleToken;
        }


        /** mimazdazai code start here */
        // if (!in_array(strtolower($handleToken->role), $allowed)) {
        //     return new WP_Error("rest_forbidden", $this->_message->get('auth.unauthenticate'), array("status" => 403));
        // }

        // $request->user_id = $handleToken->user_id;

        /** Change start here */
        $request->set_param('user_id', $handleToken->user_id);
        $user = get_user_by('ID', $handleToken->user_id);
        
        if($user === false)  return new WP_Error("rest_forbidden", $this->_message->get('auth.unauthenticate'), array("status" => 403));

        if (!in_array(strtolower($user->roles[0]), $allowed)) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.unauthenticate'), array("status" => 403));
        }

        return true;
    }

    public function logout_handle(WP_REST_Request $request)
    {
        $token = $request->get_header('Authorization');

        if ($token == "") {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.invalid_token'), array("status" => 403));
        }
        try {
            $decodedToken = JWT::decode($token, new Key(JWT_SECRET, "HS256"));
            $request->set_param('user_id', $decodedToken->user_id);

            return $decodedToken;
        } catch (ExpiredException $e) {
            $request->set_param('user_id', $decodedToken->user_id);

            return true;
        } catch (UnexpectedValueException $e) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.invalid_token'), array("status" => 403));
        }
    }
}
