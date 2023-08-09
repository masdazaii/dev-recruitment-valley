<?php

namespace Company\Profile;

use Constant\Message;
use Error;
use Helper;
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

    public function get(WP_REST_Request $request)
    {
        $user_id = $request->user_id;
        $user_data = ProfileModel::user_data($user_id);
        $user_data_acf = Helper::isset($user_data, 'acf');
        $galleries = Helper::isset($user_data_acf, 'ucma_gallery_photo') ?? [];
        $galleries = array_map(function ($gallery) {
            return [
                'id' => $gallery['ID'],
                'title' => $gallery['title'],
                'url' => $gallery['url'],
            ];
        }, $galleries);

        return [
            'detail' => [
                'email' => Helper::isset($user_data, 'user_email'),
                'phoneNumber' => Helper::isset($user_data_acf, 'ucma_phone_code') . " " . Helper::isset($user_data_acf, 'ucma_phone'),
                'address' => Helper::isset($user_data_acf, 'ucma_city') . ", " . Helper::isset($user_data_acf, 'ucma_country'),
                'kvk' => Helper::isset($user_data_acf, 'ucma_kvk_number'),
                'sector' => Helper::isset($user_data_acf, 'ucma_sector'),
                'website' => Helper::isset($user_data_acf, 'ucma_website_url'),
                'employees' => Helper::isset($user_data_acf, 'ucma_employees'),
                'btw' => Helper::isset($user_data_acf, 'ucma_btw_number'),
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
}
