<?php

namespace Candidate\Profile;

use Constant\Message;
use Error;
use Exception;
use Helper\ValidationHelper;
use JWTHelper;
use Model\ModelHelper;
use ResponseHelper;
use WP_REST_Request;

class ProfileController
{
    private $message = null;

    public function __construct()
    {
        $this->message = new Message;
    }

    public function get( WP_REST_Request $request )
    {
        $user_id = $request->user_id;

        try {
            $user = new Candidate($user_id);

            $response = [
                "firstName"=> $user->getFirstName(),
                "lastName"=> $user->getLastName(),
                "phoneNumberCode"=> $user->getPhoneNumberCode(),
                "phoneNumber"=> $user->getPhoneNumber(),
                "country"=> $user->getCountry(),
                "city"=> $user->getCity(),
                "email"=> $user->getEmail(),
                "cv"=> [
                    'url' => $user->getCv()["url"],
                    'fileName' => $user->getCv()["filename"],
                    'createdAt' => $user->getCv()["date"],
                ]
            ];

            return [
                "status" => 200,
                "data" => $response,
                "message" => $this->message->get('candidate.get')
            ];
        } catch (Exception $e) {
            return [
                "status" => $e->getCode(),
                "data" => null,
                "message" => $e->getMessage()
            ];
        }
    }

    public function setup(WP_REST_Request $request)
    {
        $user_id = $request->user_id;
        $fields = $request->get_body_params();

        $validate = ValidationHelper::doValidate($fields, [
            "firstName" => "required",
            "dateOfBirth" => "required",
            "phoneNumber" => "required",
            "phoneNumberCode" => "required",
            "country" => "required",
            "city" => "required",
            "linkedinPage" => "required",
        ]);

        if (!$validate['is_valid']) return wp_send_json_error(['validation' => $validate['fields'], 'status' => 400], 400);
        if (count($_FILES) === 0) return wp_send_json_error(['validation' => ['cv' => ["Field cv is requied."]], 'status' => 400], 400);

        global $wpdb;
        try {
            $wpdb->query('START TRANSACTION');

            $userdata = [
                'ID' => $user_id,
                'first_name'      => $fields['firstName'],
                'last_name'       => $fields['lastName'],
            ];

            $cv = ModelHelper::handle_upload('cv');
            $image = ModelHelper::handle_upload('image');

            wp_update_user($userdata);
            update_field('ucaa_date_of_birth', $fields['dateOfBirth'], 'user_' . $user_id);
            update_field('ucaa_phone', $fields['phoneNumber'], 'user_' . $user_id);
            update_field('ucaa_phone_code', $fields['phoneNumberCode'], 'user_' . $user_id);
            update_field('ucaa_country', $fields['country'], 'user_' . $user_id);
            update_field('ucaa_city', $fields['city'], 'user_' . $user_id);
            update_field('ucaa_linkedin_url_page', $fields['linkedinPage'], 'user_' . $user_id);
            update_field('ucaa_is_full_registered', 1, 'user_' . $user_id);
            if ($cv) {
                $cv_id = wp_insert_attachment($cv['cv']['attachment'], $cv['cv']['file']);
                update_field('ucaa_cv', $cv_id, 'user_' . $user_id);
            }
            if ($image) {
                $image_id = wp_insert_attachment($image['image']['attachment'], $image['image']['file']);
                update_field('ucaa_image', $image_id, 'user_' . $user_id);
            }

            $wpdb->query('COMMIT');
        } catch (Error $e) {
            $wpdb->query('ROLLBACK');
            return wp_send_json_error(['error' => $e, 'status' => 500], 500);
        }

        return [
            'message' => $this->message->get("profile.setup.success")
        ];
    }

    public function updatePhoto(WP_REST_Request $request)
    {
        $user_id = $request->user_id;

        global $wpdb;
        try {
            $wpdb->query('START TRANSACTION');

            $cv = ModelHelper::handle_upload('cv');
            $image = ModelHelper::handle_upload('image');

            if ($image) {
                $image_id = wp_insert_attachment($image['image']['attachment'], $image['image']['file']);
                update_field('ucaa_image', $image_id, 'user_' . $user_id);
            }

            $wpdb->query('COMMIT');

            return [
                "status" => 200,
                "message" => $this->message->get("profile.update.photo.success")
            ];

        } catch (Error $e) {
            $wpdb->query('ROLLBACK');
            return [
                "status" => 500,
                "message" => $e->getMessage()
            ];
        }
    }

    public function updateCv(WP_REST_Request $request)
    {
        $user_id = $request->user_id;

        global $wpdb;
        try {
            $wpdb->query('START TRANSACTION');

            $cv = ModelHelper::handle_upload('cv');

            if ($cv) {
                $cv_id = wp_insert_attachment($cv['cv']['attachment'], $cv['cv']['file']);
                update_field('ucaa_cv', $cv_id, 'user_' . $user_id);
            }

            $wpdb->query('COMMIT');

            return [
                "status" => 200,
                "message" => $this->message->get("profile.update.cv.success")
            ];

        } catch (Error $e) {
            $wpdb->query('ROLLBACK');
            return [
                "status" => 500,
                "message" => $e->getMessage()
            ];
        }
    }

    public function updateProfile( WP_REST_Request $request )
    {
        $user_id = $request->user_id;
        $fields = $request->get_body_params();

        $validate = ValidationHelper::doValidate($fields, [
            "firstName" => "required",
            "dateOfBirth" => "required",
            "phoneNumber" => "required",
            "phoneNumberCode" => "required",
            "country" => "required",
            "city" => "required",
            "linkedinPage" => "required",
        ]);

        if (!$validate['is_valid']) return wp_send_json_error(['validation' => $validate['fields'], 'status' => 400], 400);
        if (count($_FILES) === 0) return wp_send_json_error(['validation' => ['cv' => ["Field cv is requied."]], 'status' => 400], 400);

        global $wpdb;
        try {
            $wpdb->query('START TRANSACTION');

            $userdata = [
                'ID' => $user_id,
                'first_name'      => $fields['firstName'],
                'last_name'       => $fields['lastName'],
            ];

            $cv = ModelHelper::handle_upload('cv');
            $image = ModelHelper::handle_upload('image');

            wp_update_user($userdata);
            update_field('ucaa_date_of_birth', $fields['dateOfBirth'], 'user_' . $user_id);
            update_field('ucaa_phone', $fields['phoneNumber'], 'user_' . $user_id);
            update_field('ucaa_phone_code', $fields['phoneNumberCode'], 'user_' . $user_id);
            update_field('ucaa_country', $fields['country'], 'user_' . $user_id);
            update_field('ucaa_city', $fields['city'], 'user_' . $user_id);
            update_field('ucaa_linkedin_url_page', $fields['linkedinPage'], 'user_' . $user_id);
            update_field('ucaa_is_full_registered', 1, 'user_' . $user_id);
            if ($cv) {
                $cv_id = wp_insert_attachment($cv['cv']['attachment'], $cv['cv']['file']);
                update_field('ucaa_cv', $cv_id, 'user_' . $user_id);
            }
            if ($image) {
                $image_id = wp_insert_attachment($image['image']['attachment'], $image['image']['file']);
                update_field('ucaa_image', $image_id, 'user_' . $user_id);
            }

            $wpdb->query('COMMIT');

            return [
                "status" => 200,
                "message" => $this->message->get('profile.update.success')
            ];
        } catch (Error $e) {
            $wpdb->query('ROLLBACK');
            return wp_send_json_error(['error' => $e, 'status' => 500], 500);
        }
    }

    public function changeEmailRequest( WP_REST_Request $request )
    {
        $oldEmail = $request["email"];
        $newEmail = $request["newEmail"];

        $newEmailExists = email_exists($newEmail);

        if($newEmailExists){
            return [
                "status" => 400,
                "message" => $this->message->get('candidate.change_email_request.email_exist')
            ];
        }

        $user = get_user_by('email', $oldEmail);
        if(!$user)
        {
            return [
                "status" => 400,
                "message" => $this->message->get('candidate.change_email_request.not_found')
            ];
        }

        $token = JWTHelper::generate(
            [
                "user_id" => $user->ID,
                "old_email" => $user->user_email,
                "new_email" => $newEmail,
            ], "+2 hours"
        );

        /** Send email to admin */
        $admin = get_option('admin_email');
        $subject = __("NEW CHANGE EMAIL REQUEST");

        $chang_email_url = get_site_url()."/changeEmail?token=".$token;

        $message = "new change email request, if this is not by you just skip it <br>";
        $message .= "<br>";
        $message .= "visit this url ". $chang_email_url;

        wp_mail($user->user_email, $subject, $message);

        return [
            "status" => 200,
            "message" => $this->message->get('candidate.change_email_request.success')
        ];
    }

    public function changeEmail(WP_REST_Request $request)
    {
        $body = $request->get_body_params();
        $payload = JWTHelper::check($body["token"]);
        if(isset($payload["status"]))
        {
            return $payload;
        }

        $user = get_user_by('id', $payload->user_id);
        if(!$user)
        {
            return [
                "status" => 400,
                "message" => $this->message->get('candidate.change_email_request.not_found')
            ];
        }

        $userNewEmail = get_user_by("email", $payload->new_email);
        if($userNewEmail)
        {
            return [
                "status" => 400,
                "message" => $this->message->get('candidate.change_email_request.email_exist')
            ];
        }

        $updatedEmail = wp_update_user([
            "ID" => $user->ID,
            "user_email" => $payload->new_email,
        ]);

        if(is_wp_error($updatedEmail))
        {
            return [
                "status" => 500,
                "message" => $this->message->get('candidate.change_email.fail')
            ];
        }else{
            return [
                "status" => 200,
                "message" => $this->message->get('candidate.change_email.success')
            ];
        }

    }
}
