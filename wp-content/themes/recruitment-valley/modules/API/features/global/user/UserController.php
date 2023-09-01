<?php

namespace Global\User;

use Constant\Message;
use JWTHelper;
use ResponseHelper;
use WP_REST_Request;
use Candidate\Profile\Candidate;
use Helper\UserHelper;
use Model\Company;

class UserController
{
    private $_message = null;
    private $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->_message = new Message;
        $this->wpdb = $wpdb;
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

    public function deleteAccount( WP_REST_Request $request )
    {
        $userId = $request->user_id;
        $request = $request->get_params();
        $userPassword = $request["password"];

        $user = get_user_by( "id", $userId );
        if(!$user)
        {
            return [
                "status" => 400, 
                "message" => $this->_message->get("profile.delete.user_not_found"),
            ];
        }

        if(UserHelper::is_deleted($user->ID))
        {
            return [
                "status" => 400, 
                "message" => $this->_message->get("profile.user_deleted"),
            ];
        }

        $samePassword = wp_check_password( $userPassword, $user->user_pass, $user->ID );
        if(!$samePassword)
        {
            return [
                "status" => 400, 
                "message" => $this->_message->get("profile.delete.password_missmatch"),
            ];
        }

        $this->wpdb->query("START TRANSACTION");
        try {
            $userRoles = $user->roles[0];

            if($userRoles == "candidate")
            {
                update_user_meta($user->ID, "ucaa_is_full_registered", false);
            }else{
                update_user_meta($user->ID, "ucma_is_full_registered", false);    
            }
    
            update_user_meta($user->ID, 'is_deleted', true);
            
            $this->wpdb->query("COMMIT");
            return [
                "status" => 201,
                "message" => $this->_message->get("profile.delete.success") 
            ];
        } catch (\Throwable $th) {
            $this->wpdb->query("ROLLBACK");
            return [
                "status" => 201,
                "message" => $this->_message->get("profile.delete.fail") 
            ];
        }
    }
}
