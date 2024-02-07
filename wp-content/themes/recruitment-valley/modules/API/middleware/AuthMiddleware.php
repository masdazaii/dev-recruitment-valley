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
use WP_User;
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

            $isDeleted = UserHelper::is_deleted($decodedToken->user_id);
            if ($isDeleted) return new WP_Error("user_deleted", $this->_message->get("auth.user_deleted"));

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

            $isDeleted = UserHelper::is_deleted($decodedToken->user_id);
            if ($isDeleted) {
                return new WP_Error("user_deleted", $this->_message->get("auth.user_deleted"));
            };

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

        /** Check if user already verify the OTP */
        $isVerified = get_user_meta($user->ID, 'otp_is_verified', true);

        if ($isVerified <= 0 || $isVerified == '0') {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.unauthenticate'), array("status" => 403));
        }

        if (!in_array(strtolower($user->roles[0]), $allowed)) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.unauthenticate'), array("status" => 403));
        }

        // $request->set_param('user_id', $request->user_id); // this will take the user_id of the currently logged in user
        $request->set_param('user_id', $handleToken->user_id);
        $request->set_param('email', $handleToken->user_email);
        return $request;
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
        if ($user && $user instanceof WP_User) {
            $request->set_param('user_role', $user->roles[0]);
        }

        /** Check if user already verify the OTP */
        $isVerified = get_user_meta($user->ID, 'otp_is_verified', true);
        if ($isVerified <= 0 || $isVerified == '0') {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.unauthenticate'), array("status" => 403));
        }

        if ($user === false)  return new WP_Error("rest_forbidden", $this->_message->get('auth.unauthenticate'), array("status" => 403));

        if (!in_array(strtolower($user->roles[0]), $allowed)) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.unauthenticate'), array("status" => 403));
        }

        return $request;
    }

    public function authorize_company_recruiter(WP_REST_Request $request)
    {
        $allowed        = ["recruiter", "company-recruiter"];
        $handleToken    = $this->_handle_token($request);

        if (is_wp_error($handleToken)) {
            return $handleToken;
        }

        $request->set_param('user_id', $handleToken->user_id);
        $user = get_user_by('ID', $handleToken->user_id);
        if ($user && $user instanceof WP_User) {
            $request->set_param('user_role', $user->roles[0]);
        }

        if ($user === false) return new WP_Error("rest_forbidden", $this->_message->get('auth.unauthenticate'), array("status" => 403));

        if (!in_array(strtolower($user->roles[0]), $allowed)) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.unauthenticate'), array("status" => 403));
        }

        return $request;
    }

    public function authorize_both(WP_REST_Request $request)
    {
        $allowed = ['candidate', 'company'];
        $handleToken = $this->_handle_token($request);

        if (is_wp_error($handleToken)) {
            return $handleToken;
        }

        $user = get_user_by('ID', $handleToken->user_id);

        /** Check if user already verify the OTP */
        $isVerified = get_user_meta($user->ID, 'otp_is_verified', true);

        if ($isVerified <= 0 || $isVerified == '0') {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.unauthenticate'), array("status" => 403));
        }

        if (!in_array(strtolower($user->roles[0]), $allowed)) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.unauthenticate'), array("status" => 403));
        }

        // $request->set_param('user_id', $request->user_id); // this will take the user_id of the currently logged in user
        $request->set_param('user_id', $handleToken->user_id);
        $request->set_param('email', $handleToken->user_email);
        $request->set_param('role', $handleToken->role);
        return true;
    }

    public function authorize_both_company(WP_REST_Request $request)
    {
        $allowed = ['recruiter', 'company-recruiter', 'company'];
        $handleToken = $this->_handle_token($request);

        if (is_wp_error($handleToken)) {
            return $handleToken;
        }

        $user = get_user_by('ID', $handleToken->user_id);

        /** Check if user already verify the OTP */
        $isVerified = get_user_meta($user->ID, 'otp_is_verified', true);

        if ($isVerified <= 0 || $isVerified == '0') {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.unauthenticate'), array("status" => 403));
        }

        if (!in_array(strtolower($user->roles[0]), $allowed)) {
            return new WP_Error("rest_forbidden", $this->_message->get('auth.unauthenticate'), array("status" => 403));
        }

        // $request->set_param('user_id', $request->user_id); // this will take the user_id of the currently logged in user
        $request->set_param('user_id', $handleToken->user_id);
        $request->set_param('email', $handleToken->user_email);
        $request->set_param('role', $handleToken->role);
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
