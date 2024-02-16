<?php

namespace Global\User;

use Constant\Message;
use JWTHelper;
use ResponseHelper;
use WP_REST_Request;
use Candidate\Profile\Candidate;
use Helper\UserHelper;
use Model\Company;
use Model\CompanyRecruiter;

// use Global\NotificationService;
// use constant\NotificationConstant;

class UserController
{
    private $_message = null;
    private $wpdb;
    private $_notification;
    private $_notificationConstant;

    public function __construct()
    {
        global $wpdb;
        $this->_message = new Message;
        $this->wpdb = $wpdb;

        // $this->_notification = new NotificationService();
        // $this->_notificationConstant = new NotificationConstant();
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
            } else if (in_array($request['roles'], ['recruiter', 'company-recruiter'])) {
                $user = new CompanyRecruiter($request['user_id']);

                return [
                    'status' => 200,
                    'message' => $this->_message->get('company_recruiter.profile.get_success'),
                    'data' => [
                        "image"     => $user->getThumbnail(),
                        "firstName" => $user->getName(),
                        "lastName"  => '',
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

    public function deleteAccountPermanent(WP_REST_Request $request)
    {

        $userId = $request->user_id;
        $request = $request->get_params();
        $userPassword = $request["password"];

        $user = get_user_by("id", $userId);
        if (!$user) {
            return [
                "status" => 400,
                "message" => $this->_message->get("profile.delete.user_not_found"),
            ];
        }

        if (UserHelper::is_deleted($user->ID)) {
            return [
                "status" => 400,
                "message" => $this->_message->get("profile.user_deleted"),
            ];
        }

        $samePassword = wp_check_password($userPassword, $user->user_pass, $user->ID);
        if (!$samePassword) {
            return [
                "status" => 400,
                "message" => $this->_message->get("profile.delete.password_missmatch"),
            ];
        }


        require_once(ABSPATH . 'wp-admin/includes/user.php');
        wp_delete_user($userId);

        return [
            "status" => 200,
            "message" => $this->_message->get("profile.acount.delete_success"),
        ];
    }

    public function deleteAccount(WP_REST_Request $request)
    {
        $userId = $request->user_id;
        $request = $request->get_params();
        $userPassword = $request["password"];

        $user = get_user_by("id", $userId);
        if (!$user) {
            return [
                "status" => 400,
                "message" => $this->_message->get("profile.delete.user_not_found"),
            ];
        }

        if (UserHelper::is_deleted($user->ID)) {
            return [
                "status" => 400,
                "message" => $this->_message->get("profile.user_deleted"),
            ];
        }

        $samePassword = wp_check_password($userPassword, $user->user_pass, $user->ID);
        if (!$samePassword) {
            return [
                "status" => 400,
                "message" => $this->_message->get("profile.delete.password_missmatch"),
            ];
        }

        $this->wpdb->query("START TRANSACTION");
        try {
            $userRoles = $user->roles[0];

            // if($userRoles == "candidate")
            // {
            //     update_user_meta($user->ID, "ucaa_is_full_registered", false);
            // }else{
            //     update_user_meta($user->ID, "ucma_is_full_registered", false);
            // }

            update_user_meta($user->ID, 'is_deleted', true);

            $this->wpdb->query("COMMIT");

            /** Create Notification : account deactivated */
            // $this->_notification->write($this->_notificationConstant::ACCOUNT_DEACTIVATE, $user->ID, [
            //     'id' => $user->ID
            // ]);

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

    public function reactivate(WP_REST_Request $request)
    {
        $request = $request->get_params();

        $token = $request["token"];

        $checkedToken = JWTHelper::check($token);

        if (is_array($checkedToken)) {
            return $checkedToken;
        }

        $userId = $checkedToken->user_id;

        $isUserDeleted = get_user_meta($userId, "is_deleted", true);
        if (!$isUserDeleted) {
            return [
                "status" => 400,
                "message" => $this->_message->get("profile.account.still_active")
            ];
        }

        $this->wpdb->query("START TRANSACTION");
        try {
            update_user_meta($userId, "is_deleted", false);

            $this->wpdb->query("COMMIT");

            /** Create Notification : account reactivated */
            // $this->_notification->write($this->_notificationConstant::ACCOUNT_DEACTIVATE, $userId, [
            //     'id' => $userId
            // ]);

            return [
                "status" => 200,
                "message" => $this->_message->get("profile.account.reactive_success")
            ];
        } catch (\Throwable $th) {
            //throw $th;
            $this->wpdb->query('ROLLBACK');
            error_log($th->getMessage());
            return [
                "status" => 400,
                "message" => $this->_message->get("profile.account.reactive_failed")
            ];
        }
    }
}
