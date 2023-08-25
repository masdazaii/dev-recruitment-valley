<?php

namespace Global\User;

use Constant\Message;
use JWTHelper;
use ResponseHelper;
use WP_REST_Request;
use Candidate\Profile\Candidate;
use Model\Company;

class UserController
{
    private $_message = null;

    public function __construct()
    {
        $this->_message = new Message;
    }

    public function getUserNav($request)
    {
        try {
            if ($request['roles'] === 'candidate') {
                $user = new Candidate($request['user_id']);

                return [
                    'status' => 200,
                    'message' => $this->_message->get('candidate.profile.get_success'),
                    'data' => [
                        "image" => $user->getImage(),
                        "firstName" => $user->getFirstName(),
                        "lastName" => $user->getLastName(),
                    ]
                ];
            } else if ($request['roles'] === 'company') {
                $user = new Company($request['user_id']);

                return [
                    'status' => 200,
                    'message' => $this->_message->get('candidate.profile.get_success'),
                    'data' => [
                        "image" => $user->getThumbnail(),
                        "firstName" => $user->getName(),
                        "lastName" => '',
                    ]
                ];
            }
        } catch (\Exception $e) {
            return [
                "status" => $e->getCode(),
                'asdasd' => 'asd',
                "message" => $e->getMessage()
            ];
        }
    }
}
