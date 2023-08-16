<?php

namespace Global;

use Constant\Message;
use WP_REST_Request;

require_once(get_template_directory() . '/vendor/autoload.php');

class PackageController
{
    protected $_message;

    public function __construct()
    {
        $this->_message = new Message();
    }

    public function get($request)
    {
        $data = get_posts([
            'post_type' => 'package',
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
                'packagePrice' => get_field('rv_package_price', $post->ID),
                'packageCreditQuantity' => get_field('rv_package_credit_quantity', $post->ID),
                'isFavorite' => $isFavorite
            ];
        }

        return [
            "message" => $this->_message->get('package.package.get_success'),
            "data" => $responseData,
            "status" => 200
        ];
    }

    public function show($request)
    {
        $package = get_page_by_path($request['slug'], OBJECT, 'package');
        $isFavorite = get_field('is_favorite', $package->ID) ?? false;

        return [
            "message" => $this->_message->get('package.package.show_success'),
            "data" => [
                'id' => $package->ID,
                'slug' => $package->post_name,
                'packageName' => $package->post_title,
                'packageDescription' => $package->post_content,
                'packagePrice' => get_field('rv_package_price', $package->ID),
                'packageCreditQuantity' => get_field('rv_package_credit_quantity', $package->ID),
                'isFavorite' => $isFavorite
            ],
            "status" => 200
        ];
    }

//     public function purchase( WP_REST_Request $request )
//     {

//         $jobId = $request["vacancyId"];
        
        
//         $secretKey = get_field( "stripe_secret_key", "option");
//         $stripe = new \Stripe\StripeClient($secretKey);

//         $session = $stripe->checkout->sessions->create([
// 			'line_items' => [
// 				[
// 					'price_data' => [
// 						'currency' => 'EUR', //current currency used by birdles
// 						'product_data' => [
// 							'name' => 'SMS Broadcast for user ID #' . $usrID, // Product name, after the data provided it will be change by user filter name
// 						],
// 						'unit_amount' => $amount, // divide by 2 zero example ; 2000 it will converted to 20
// 					],
// 					'quantity' => 1, // for current situation it will always by one because its only one time product
// 				]
// 			],
// 			'mode' => 'payment',
// 			'success_url' => $url_success, // success url after payment
// 			'cancel_url' => $url_failed, // fail redit=rect payment
// // 			'invoice_creation' => [
// // 				'enabled' => true,
// // 				'invoice_data' => [
// // 					'description' => "
// // Product promoted				: {$product}
// // Sender						: {$sender}
// // Reach						: {$reach}
// // Business or Company Name		: {$cname}
// // Custumer ID					: {$usrID}
// // ABN							: {$ABN}
// // 				  ",
// // 					'metadata' => ['order' => 'order-xyz'],
// // 					'rendering_options' => ['amount_tax_display' => 'include_inclusive_tax'],
// // 					'custom_fields' => [
// // 						[
// // 							'name' => 'ABN',
// // 							'value' => '60 655 532 620',
// // 						]
// // 					]
// // 				],
// // 			],
// 			'automatic_tax' => [
// 				'enabled' => true,
// 			],
// 		]);
    // }

}
