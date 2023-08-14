<?php

namespace Company\Profile;

use Constant\Message;
use Error;
use Helper;
use Helper\ValidationHelper;
use Model\ModelHelper;
use ResponseHelper;
use WP_REST_Request;
use WP_REST_Response;

class ProfileController
{
    private $message = null;

    public function __construct()
    {
        $this->message = new Message;
    }

    public function get(WP_REST_Request $request)
    {
        // $user_id = $request->user_id;
        $user_id = $request['user_id']; // Changed Line
        $user_data = ProfileModel::user_data($user_id);
        $user_data_acf = Helper::isset($user_data, 'acf');
        $galleries = Helper::isset($user_data_acf, 'ucma_gallery_photo') ?? [];

        $galleries = array_map(function ($gallery) {
            static $i = 0;
            $result = [
                'id' => $gallery['ID'] . "-" . $i,
                'title' => $gallery['title'],
                'url' => $gallery['url'],
            ];
            $i++;
            return $result;
        }, $galleries);

        /** Added line Start here */
        $image = Helper::isset($user_data_acf, 'ucma_image') ?? [];
        if (!empty($image)) {
            $image = [
                'id' => $image['ID'],
                'title' => $image['title'],
                'url' => $image['url']
            ];
        }
        /** Added line End here */

        return [
            'a' => $user_data,
            'detail' => [
                // 'email' => Helper::isset($user_data, 'user_email'),
                'phoneNumber' => Helper::isset($user_data_acf, 'ucma_phone_code') . " " . Helper::isset($user_data_acf, 'ucma_phone'),
                'address' => Helper::isset($user_data_acf, 'ucma_city') . ", " . Helper::isset($user_data_acf, 'ucma_country'),
                'kvk' => Helper::isset($user_data_acf, 'ucma_kvk_number'),
                'sector' => Helper::isset($user_data_acf, 'ucma_sector'),
                'website' => Helper::isset($user_data_acf, 'ucma_website_url'),
                'employees' => Helper::isset($user_data_acf, 'ucma_employees'),
                'btw' => Helper::isset($user_data_acf, 'ucma_btw_number'),
                /** Added Line start here */
                'companyEmail' => Helper::isset($user_data_acf, 'ucma_company_email'), // Added Line
                'comapnyName' => Helper::isset($user_data_acf, 'ucma_company_name'), // Added Line
                'image' => $image, // Added line
                /** Added Line end here */
            ],
            'socialMedia' => [
                'facebook' => Helper::isset($user_data_acf, 'ucma_facebook_url'),
                'linkedin' => Helper::isset($user_data_acf, 'ucma_linkedin_url'),
                'instagram' => Helper::isset($user_data_acf, 'ucma_instagram_url'),
                'twitter' => Helper::isset($user_data_acf, 'ucma_twitter_url')
            ],
            'address' => [
                'country' => Helper::isset($user_data_acf, 'ucma_country'),
                'streetname' => Helper::isset($user_data_acf, 'ucma_street'),
                'city' => Helper::isset($user_data_acf, 'ucma_city'),
                'postcode' => Helper::isset($user_data_acf, 'ucma_postcode'),
            ],
            'information' => Helper::isset($user_data_acf, 'ucma_short_decription'),
            'gallery' => $galleries
        ];
    }

    public function post_address(WP_REST_Request $request)
    {
        $user_id = $request->user_id;

        $fields = $request->get_body();
        $fields = (array)json_decode($fields);

        $validate = ValidationHelper::doValidate($fields, [
            "country" => "required",
            "city" => "required",
            "street" => "required",
            "postcode" => "required",
        ]);
        if (!$validate['is_valid']) wp_send_json_error(['validation' => $validate['fields'], 'status' => 400], 400);

        global $wpdb;
        try {
            $wpdb->query('START TRANSACTION');
            update_field('ucma_country', $fields['country'], 'user_' . $user_id);
            update_field('ucma_street', $fields['street'], 'user_' . $user_id);
            update_field('ucma_city', $fields['city'], 'user_' . $user_id);
            update_field('ucma_postcode', $fields['postcode'], 'user_' . $user_id);

            $wpdb->query('COMMIT');
        } catch (Error $e) {
            $wpdb->query('ROLLBACK');
            return wp_send_json_error(['error' => $e, 'status' => 500], 500);
        }

        return [
            'message' => $this->message->get("profile.update.success")
        ];
    }

    public function post_socials(WP_REST_Request $request)
    {
        $user_id = $request->user_id;

        $fields = $request->get_body();
        $fields = (array)json_decode($fields);

        global $wpdb;
        try {
            $wpdb->query('START TRANSACTION');
            update_field('ucma_facebook_url', Helper::isset($fields, 'facebook'), 'user_' . $user_id);
            update_field('ucma_linkedin_url', Helper::isset($fields, 'linkedin'), 'user_' . $user_id);
            update_field('ucma_instagram_url', Helper::isset($fields, 'instagram'), 'user_' . $user_id);
            update_field('ucma_twitter_url', Helper::isset($fields, 'twitter'), 'user_' . $user_id);

            $wpdb->query('COMMIT');
        } catch (Error $e) {
            $wpdb->query('ROLLBACK');
            return wp_send_json_error(['error' => $e, 'status' => 500], 500);
        }

        return [
            'message' => $this->message->get("profile.update.success")
        ];
    }

    public function post_detail(WP_REST_Request $request)
    {
        $user_id = $request->user_id;

        $fields = $request->get_body();
        $fields = (array)json_decode($fields);

        $validate = ValidationHelper::doValidate($fields, [
            "companyName" => "required",
            "employees" => "required",
            "phoneCode" => "required",
            "phoneNumber" => "required",
            "email" => "required",
        ]);
        if (!$validate['is_valid']) wp_send_json_error(['validation' => $validate['fields'], 'status' => 400], 400);

        global $wpdb;
        try {
            $wpdb->query('START TRANSACTION');
            $userdata = [
                'ID'            => $user_id,
                'email'         => $fields['email'],
                'last_name'     => $fields['lastName'],
            ];

            wp_update_user($userdata);
            update_field('ucma_employees', Helper::isset($fields, 'employees'), 'user_' . $user_id);
            update_field('ucma_phone_code', Helper::isset($fields, 'phoneCode'), 'user_' . $user_id);
            update_field('ucma_phone', Helper::isset($fields, 'phoneNumber'), 'user_' . $user_id);
            update_field('ucma_sector', Helper::isset($fields, 'sector'), 'user_' . $user_id);
            update_field('ucma_website_url', Helper::isset($fields, 'website'), 'user_' . $user_id);
            update_field('ucma_kvk_number', Helper::isset($fields, 'kvk'), 'user_' . $user_id);
            update_field('ucma_btw_number', Helper::isset($fields, 'btw'), 'user_' . $user_id);

            $wpdb->query('COMMIT');
        } catch (Error $e) {
            $wpdb->query('ROLLBACK');
            return wp_send_json_error(['error' => $e, 'status' => 500], 500);
        }

        return [
            'message' => $this->message->get("profile.update.success")
        ];
    }

    public function post_information(WP_REST_Request $request)
    {
        $user_id = $request->user_id;

        $fields = $request->get_body_params();

        $validate = ValidationHelper::doValidate($fields, [
            "sortDescription" => "required",
        ]);
        if (!$validate['is_valid']) wp_send_json_error(['validation' => $validate['fields'], 'status' => 400], 400);


        global $wpdb;
        try {
            $wpdb->query('START TRANSACTION');

            $galleries = ModelHelper::handle_uploads('gallery', 'test');

            update_field('ucma_short_decription', Helper::isset($fields, 'sortDescription'), 'user_' . $user_id);
            update_field('ucma_company_video_url', Helper::isset($fields, 'videoUrl'), 'user_' . $user_id);

            $current_gallery = maybe_unserialize(get_user_meta($user_id, 'ucma_gallery_photo'));
            $current_gallery = isset($current_gallery[0]) ? $current_gallery[0] : [];
            if ($galleries) {
                $gallery_ids = $current_gallery;
                foreach ($galleries as $key => $gallery) {
                    $gallery_ids[] = wp_insert_attachment($gallery['attachment'], $gallery['file']);
                }
                update_field('ucma_gallery_photo', $gallery_ids, 'user_' . $user_id);
            }

            $wpdb->query('COMMIT');
        } catch (Error $e) {
            $wpdb->query('ROLLBACK');
            return wp_send_json_error(['error' => $e, 'status' => 500], 500);
        }

        return [
            'message' => $this->message->get("profile.update.success")
        ];
    }

    public function delete_gallery(WP_REST_Request $request)
    {
        $user_id = $request->user_id;
        $gallery_id_index = $request->get_param('id');

        global $wpdb;
        try {
            $wpdb->query('START TRANSACTION');

            $current_gallery = maybe_unserialize(get_user_meta($user_id, 'ucma_gallery_photo'));
            $current_gallery = isset($current_gallery[0]) ? $current_gallery[0] : [];

            $gallery_index = explode("-", $gallery_id_index)[1];
            $gallery_id = explode("-", $gallery_id_index)[0];
            if (isset($current_gallery[$gallery_index]) && $current_gallery[$gallery_index] == $gallery_id) {
                unset($current_gallery[$gallery_index]);
            }
            update_field('ucma_gallery_photo', $current_gallery, 'user_' . $user_id);

            $wpdb->query('COMMIT');
        } catch (Error $e) {
            $wpdb->query('ROLLBACK');
            return wp_send_json_error(['error' => $e, 'status' => 500], 500);
        }

        return [
            'message' => $this->message->get("profile.update.success")
        ];
    }

    public function setup($request)
    {
        global $wpdb;

        try {
            $wpdb->query('START TRANSACTION');

            $updateData = [
                'ID' => $request['user_id'],
                'firstName' => $request['companyName']
            ];
            wp_update_user($updateData);

            /** Upload Gallery */
            $galleries = ModelHelper::handle_uploads('gallery', $request['user_id']);
            $current_gallery = maybe_unserialize(get_user_meta($request['user_id'], 'ucma_gallery_photo'));
            $current_gallery = isset($current_gallery[0]) ? $current_gallery[0] : [];
            if ($galleries) {
                $gallery_ids = $current_gallery;
                foreach ($galleries as $key => $gallery) {
                    $gallery_ids[] = wp_insert_attachment($gallery['attachment'], $gallery['file']);
                }
                update_field('ucma_gallery_photo', $gallery_ids, 'user_' . $request['user_id']);
            }

            /** Upload Image */
            $image = ModelHelper::handle_upload('image');
            if ($image) {
                $image_id = wp_insert_attachment($image['image']['attachment'], $image['image']['file']);
                update_field('ucma_image', $image_id, 'user_' . $request['user_id']);
            }

            /** Store Meta && ACF */
            update_field('ucma_company_email', $request['email'], 'user_' . $request['user_id']);
            update_field('ucma_company_name', $request['companyName'], 'user_' . $request['user_id']);
            update_field('ucma_phone_code', $request['phoneNumberCode'], 'user_' . $request['user_id']);
            update_field('ucma_phone', $request['phoneNumber'], 'user_' . $request['user_id']);
            update_field('ucma_country', $request['country'], 'user_' . $request['user_id']);
            update_field('ucma_street', $request['street'], 'user_' . $request['user_id']);
            update_field('ucma_city', $request['city'], 'user_' . $request['user_id']);
            update_field('ucma_postcode', $request['postCode'], 'user_' . $request['user_id']);
            update_field('ucma_employees', $request['employeesTotal'], 'user_' . $request['user_id']);
            update_field('ucma_sector', $request['sector'], 'user_' . $request['user_id']);
            update_field('ucma_kvk_number', $request['kvkNumber'], 'user_' . $request['user_id']);
            update_field('ucma_btw_number', $request['btwNumber'], 'user_' . $request['user_id']);
            update_field('ucma_website_url', $request['website'], 'user_' . $request['user_id']);
            update_field('ucma_linkedin_url', $request['linkedin'], 'user_' . $request['user_id']);
            update_field('ucma_facebook_url', $request['facebook'], 'user_' . $request['user_id']);
            update_field('ucma_instagram_url', $request['instagram'], 'user_' . $request['user_id']);
            update_field('ucma_twitter_url', $request['twitter'], 'user_' . $request['user_id']);
            update_field('ucma_short_decription', $request['shortDescription'], 'user_' . $request['user_id']);
            update_field('ucma_benefit', $request['secondaryEmploymentConditions'], 'user_' . $request['user_id']);
            update_field('ucma_company_video_url', $request['companyVideo'], 'user_' . $request['user_id']);

            $wpdb->query('COMMIT');
        } catch (Error $errors) {
            $wpdb->query('ROLLBACK');
            return [
                "message" => $this->message->get("company.profile.setup_failed"),
                "errors" => $errors,
                "status" => 500
            ];
        }

        return [
            "message" => $this->message->get("company.profile.setup_success"),
            "data" => $request,
            "status" => 200
        ];
    }

    // public function updatePhoto( WP_REST_Request $request )
    // {
    //     $params = $request->user_id;
    // }
}
