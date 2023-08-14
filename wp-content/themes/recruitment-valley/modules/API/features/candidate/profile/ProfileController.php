<?php

namespace Candidate\Profile;

use Constant\Message;
use Error;
use Helper\ValidationHelper;
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
                update_field('ucaa_image', $cv_id, 'user_' . $user_id);
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
}
