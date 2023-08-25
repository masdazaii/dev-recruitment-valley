<?php

namespace Global\User;

use WP_REST_Request;
use ResponseHelper;
use Constant\Message;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DomainException;
use UnexpectedValueException;

class UserService
{
    private $userController;
    private $_message;

    public function __construct()
    {
        $this->_message = new Message();
        $this->userController = new UserController;
    }

    public function getUserNav(WP_REST_Request $request)
    {
        $jwtoken = $request->get_header('Authorization');

        // return ResponseHelper::build([
        //     'status' => 200,
        //     'message' => $jwtoken
        // ]);

        if ($jwtoken == "") {
            return [
                "status" => 401,
                "message" => $this->_message->get("auth.invalid_token")
            ];
        }

        try {
            $decodedToken = JWT::decode($jwtoken, new Key(JWT_SECRET, "HS256"));

            $user = get_user_by('ID', $decodedToken->user_id);
            $params['user_id'] = $decodedToken->user_id;
            $params['roles'] = $user->roles[0];
        } catch (DomainException $e) {
            return [
                "status" => 401,
                'asd' => '1',
                "message" => $e->getMessage()
            ];
        } catch (ExpiredException $e) {
            return [
                "status" => 401,
                'asd' => '2',
                "message" => $e->getMessage()
            ];
        } catch (UnexpectedValueException $e) {
            return [
                "status" => 401,
                'asd' => '3',
                "message" => $e->getMessage()
            ];
        }

        // $params = $request->get_params();
        $response = $this->userController->getUserNav($params);
        return ResponseHelper::build($response);
    }
}
