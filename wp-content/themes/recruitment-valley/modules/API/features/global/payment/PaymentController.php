<?php

namespace Global;

use Constant\Message;

class PaymentController
{
    protected $_message;

    public function __construct()
    {
        $this->_message = new Message();
    }

    public function get($request)
    {
        $data = get_posts([
            'post_type' => 'payment',
            'post_status' => 'publish'
        ]);

        $responseData = [];
        foreach ($data as $post) {
            $isFavorite = get_field('is_favorite', $post->ID) ?? false;
            $responseData[] = [
                'id' => $post->ID,
                'slug' => $post->post_name,
                'packageName' => $post->post_title,
                'packageDescription' => $post->post_content,
                'packagePrice' => get_field('rv_payment_price', $post->ID),
                'packageCreditQuantity' => get_field('rv_payment_credit_quantity', $post->ID),
                'isFavorite' => $isFavorite
            ];
        }

        return [
            "message" => $this->_message->get('payment.package.get_success'),
            "data" => $responseData,
            "status" => 200
        ];
    }

    public function show($request)
    {
        $package = get_page_by_path($request['slug'], OBJECT, 'payment');
        $isFavorite = get_field('is_favorite', $package->ID) ?? false;

        return [
            "message" => $this->_message->get('payment.package.show_success'),
            "data" => [
                'id' => $package->ID,
                'slug' => $package->post_name,
                'packageName' => $package->post_title,
                'packageDescription' => $package->post_content,
                'packagePrice' => get_field('rv_payment_price', $package->ID),
                'packageCreditQuantity' => get_field('rv_payment_credit_quantity', $package->ID),
                'isFavorite' => $isFavorite
            ],
            "status" => 200
        ];
    }
}
