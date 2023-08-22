<?php

namespace Company\Profile;

use Constant\Message;
use Error;
use Exception;
use Helper;
use Helper\ValidationHelper;
use Model\Company;
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

        if ($galleries) {
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
        }

        /** Added line Start here */
        $image = Helper::isset($user_data_acf, 'ucma_image') ?? [];
        if (!empty($image)) {
            $image = [
                'id' => $image['ID'],
                'title' => $image['title'],
                'url' => $image['url']
            ];
        }

        // $sectors = Helper::isset($user_data_acf, 'ucma_sector');
        $terms = get_terms([
            'taxonomy' => 'sector',
            'include' => Helper::isset($user_data_acf, 'ucma_sector') ?? []
        ]);
        $termsResponse = [];
        foreach ($terms as $term) {
            $termsResponse[] = [
                'label' => $term->name,
                'value' => $term->term_id,
            ];
        }
        /** Added line End here */

        // $socialMedia = [
        //     [
        //         "type" => "facebook",
        //         "url" => "ucma_facebook_url"
        //     ],
        //     [
        //         "type" => "linkedin",
        //         "url" => "ucma_linkedin_url"
        //     ],
        //     [
        //         "type" => "instagram",
        //         "url" => "ucma_instagram_url"
        //     ],
        //     [
        //         "type" => "twitter",
        //         "url" => "ucma_twitter_url"
        //     ],
        // ];

        // $socialMediaResponse = [];
        // foreach ($socialMedia as $key => $socmed) {
        //     $socialMediaResponse[$key] = [
        //         "id" => $key + 1,
        //         "type" => $socmed["type"],
        //         "url" => Helper::isset($user_data_acf, $socmed["url"])
        //     ];
        // }

        $addressResponse = '';
        if (Helper::isset($user_data_acf, 'ucma_city') !== null || Helper::isset($user_data_acf, 'ucma_country') !== null) {
            $addressResponse .= Helper::isset($user_data_acf, 'ucma_city') . ', ' ?? '';
            $addressResponse .= Helper::isset($user_data_acf, 'ucma_country') ?? '';
        }

        return [
            'detail' => [
                'email' => Helper::isset($user_data, 'user_email'),
                'address' => $addressResponse,
                'kvk' => Helper::isset($user_data_acf, 'ucma_kvk_number'),
                // 'phoneNumber' => Helper::isset($user_data_acf, 'ucma_phone_code') . " " . Helper::isset($usxer_data_acf, 'ucma_phone'), // Changed, look below
                // 'sector' => Helper::isset($user_data_acf, 'ucma_sector'), // Changed, look below
                'website' => Helper::isset($user_data_acf, 'ucma_website_url'),
                'employees' => Helper::isset($user_data_acf, 'ucma_employees'),
                'btw' => Helper::isset($user_data_acf, 'ucma_btw_number'),

                /** Added/Changed Line start here */
                'phoneNumberCode' => Helper::isset($user_data_acf, 'ucma_phone_code'), // Changed, look below
                'phoneNumber' => Helper::isset($user_data_acf, 'ucma_phone'), // Changed, look below
                'sector' => $termsResponse,
                'companyName' => Helper::isset($user_data_acf, 'ucma_company_name'),
                'image' => $image,
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
                'street' => Helper::isset($user_data_acf, 'ucma_street'),
                'city' => Helper::isset($user_data_acf, 'ucma_city'),
                'postcode' => Helper::isset($user_data_acf, 'ucma_postcode'),
            ],
            'information' => [
                'shortDescription' => Helper::isset($user_data_acf, 'ucma_short_decription'),
                'secondaryEmploymentConditions' => Helper::isset($user_data_acf, 'ucma_benefit'),
                'videoUrl' => Helper::isset($user_data_acf, 'ucma_company_video_url'),
                'gallery' => $galleries
            ],
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

    public function updateDetail($request)
    {
        global $wpdb;
        try {
            $wpdb->query('START TRANSACTION');

            /** Update ACF */
            update_field('ucma_company_name', $request['companyName'], 'user_' . $request['user_id']);
            update_field('ucma_sector', $request['sector'], 'user_' . $request['user_id']);
            update_field('ucma_phone_code', $request['phoneNumberCode'], 'user_' . $request['user_id']);
            update_field('ucma_phone', $request['phoneNumber'], 'user_' . $request['user_id']);
            update_field('ucma_website_url', $request['website'], 'user_' . $request['user_id']);
            update_field('ucma_employees', $request['employees']['value'], 'user_' . $request['user_id']);
            update_field('ucma_kvk_number', $request['kvkNumber'], 'user_' . $request['user_id']);
            update_field('ucma_btw_number', $request['btwNumber'], 'user_' . $request['user_id']);

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
            "status" => 200,
            "message" => $this->message->get("company.profile.update_success")
        ];
    }

    public function post_information(WP_REST_Request $request)
    {
        $user_id = $request->user_id;

        $fields = $request->get_body_params();

        $validate = ValidationHelper::doValidate($fields, [
            "shortDescription" => "required",
        ]);
        if (!$validate['is_valid']) wp_send_json_error(['validation' => $validate['fields'], 'status' => 400], 400);


        global $wpdb;
        try {
            $wpdb->query('START TRANSACTION');

            // $galleries = ModelHelper::handle_uploads('gallery', 'test'); // Change below this
            $galleries = ModelHelper::handle_uploads('gallery', $user_id); // Changes!

            update_field('ucma_short_decription', Helper::isset($fields, 'shortDescription'), 'user_' . $user_id);
            update_field('ucma_company_video_url', Helper::isset($fields, 'videoUrl'), 'user_' . $user_id);
            update_field('ucma_benefit', Helper::isset($fields, 'secondaryEmploymentConditions'), 'user_' . $user_id); // Added Line

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
            'message' => $this->message->get("profile.update.success"),
            'status' => 200,
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

            $gallery_id = explode("-", $gallery_id_index)[0];

            /** Old syntax start here */
            // $gallery_index = explode("-", $gallery_id_index)[1];
            // if (isset($current_gallery[$gallery_index]) && $current_gallery[$gallery_index] == $gallery_id) {
            //     unset($current_gallery[$gallery_index]);
            // }

            /** Changes start here */
            $galleryIndex = array_search($gallery_id, $current_gallery);
            if (isset($galleryIndex) && $galleryIndex >= 0) {
                unset($current_gallery[$galleryIndex]);
            }
            update_field('ucma_gallery_photo', $current_gallery, 'user_' . $user_id);

            $wpdb->query('COMMIT');
        } catch (Error $e) {
            $wpdb->query('ROLLBACK');
            return wp_send_json_error(['error' => $e, 'status' => 500], 500);
        }

        return [
            'status' => 200,
            'message' => $this->message->get("profile.update.success")
        ];
    }

    // public function setup(WP_REST_Request $request)
    public function setup($request)
    {
        global $wpdb;

        try {
            $wpdb->query('START TRANSACTION');

            // $updateData = [
            //     'ID' => $request['user_id'],
            //     'firstName' => $request['companyName']
            // ];
            // wp_update_user($updateData);

            /** Upload Gallery */
            $galleries = ModelHelper::handle_uploads('gallery', $request['user_id']);
            // $galleries = ModelHelper::handle_uploads('gallery', 'test');

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
            // update_field('ucma_company_email', $request['email'], 'user_' . $request['user_id']);
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
            update_field('ucma_is_full_registered', 1, 'user_' . $request['user_id']);

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
            "status" => 201
        ];
    }

    public function updatePhoto(WP_REST_Request $request)
    {
        global $wpdb;

        try {
            $wpdb->query('START TRANSACTION');

            $image = ModelHelper::handle_upload('image');
            if ($image) {
                // Get previous image
                $currentImage = get_field('ucma_image', 'user_' . $request['user_id']) ?? [];

                $imageID = wp_insert_attachment($image['image']['attachment'], $image['image']['file']);
            }

            update_field('ucma_image', $imageID, 'user_' . $request['user_id']);

            $wpdb->query('COMMIT');
        } catch (Error $errors) {
            $wpdb->query('ROLLBACK');
            return [
                "message" => $this->message->get("company.profile.update_image_failed"),
                "errors" => $errors,
                "status" => 500
            ];
        }

        // Delete old image
        if (isset($currentImage) && !empty($currentImage)) {
            wp_delete_attachment($currentImage['ID']);
        }

        return [
            "status" => 200,
            "message" => $this->message->get("company.profile.update_success")
        ];
    }

    public function getPhoto($request)
    {
        $image = get_field('ucma_image', 'user_' . $request['user_id']) ?? [];
        if (!empty($image)) {
            $image = [
                'id' => $image['ID'],
                'title' => $image['title'],
                'url' => $image['url']
            ];
        }

        return [
            'message' => $this->message->get('company.profile.get_image_success'),
            'data' => $image,
            'status' => 200
        ];
    }

    // public function updatePhoto( WP_REST_Request $request )
    // {
    //     $params = $request->user_id;
    // }

    public function getCredit(WP_REST_Request $request)
    {
        try {
            $company = new Company($request["user_id"]);

            return [
                "status" => 200,
                "message" => "success get company credit",
                "data" => [
                    "credit" => $company->getCredit()
                ]
            ];
        } catch (\Exception $e) {
            return ["status" => $e->getCode(), "message" => $e->getMessage()];
        }
    }

    private function _validateGallery($request)
    {
        $currentGallery = maybe_unserialize(get_user_meta($request['user_id'], 'ucma_gallery_photo'));
        $countRequestFile = count($_FILES['gallery']['name']);

        if ((count($currentGallery[0]) + $countRequestFile) > 10) {
            return [
                'is_valid' => false,
                'errors' => [
                    'You\'ve reached limit for uploaded gallery. Maximum stored gallery is 10 files.'
                ]
            ];
        } else {
            return [
                'is_valid' => true,
                'errors' => []
            ];
        }
    }
}
