<?php

namespace Global;

use Constant\Message;
use Exception;
use JWTHelper;
use Model\Company;
use Package;
use Stripe\Stripe;
use Stripe\StripeClient;
use Transaction;
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

    public function createPaymentUrl( WP_REST_Request $request )
    {

        $packageId = $request["packageId"];
        $userId = $request["user_id"];

        $package = new Package($packageId);
        $packagePrice = $package->getPrice();
        $pacakgeDescription = $package->getDescription();
        
        $secretKey = get_field( "stripe_secret_key", "option");
        $stripe = new \Stripe\StripeClient($secretKey);
        $amount = $packagePrice * 100;

        $company = new Company($userId);
        $package = new Package($packageId); 

        $transaction = new Transaction;
        $transactionTitle = "Transaction from ". $company->getName() . " Buy " . $package->getTitle();  

        $transactionId = $transaction->storePost(["title" => $transactionTitle]);

        if(is_wp_error($transactionId) || !$transactionId)
        {
            return [
                "status" => 500,
                "message" => "something error when creating payment"
            ];
        }

        $transaction->setUserName( $company->getName() );
        $transaction->setPackageName( $package->getTitle() );
        $transaction->setTransactionAmount( $amount/100 );
        $transaction->setUserId( $company->getId() );
        $transaction->setPackageId( $package->getPackageId() );

        $encodedTransactionID = JWTHelper::generate(
            [
                    "transaction_id" => $transaction->getTransactionId(),
                    "user_id" => $company->getId(),
                ], "+1 day");

        $session = $stripe->checkout->sessions->create([
			'line_items' => [
				[
					'price_data' => [
						'currency' => 'EUR', //current currency used by birdles
						'product_data' => [
							'name' => 'Pacakge Credit for user ' . $userId, // Product name, after the data provided it will be change by user filter name
						],
						'unit_amount' => $amount, // divide by 2 zero example ; 2000 it will converted to 20
					],
					'quantity' => 1, // for current situation it will always by one because its only one time product
				]
			],
			'mode' => 'payment',
			'success_url' => get_site_url() . "?token=" .$encodedTransactionID , // success url after payment
			'cancel_url' => get_site_url() . "?token=" .$encodedTransactionID, // fail redit=rect payment
            'metadata' => [
                "user_id" => $userId,
                "package" => $packageId
            ],
            "payment_method_types" => [
                "card", "ideal"
            ],
		]);

        $transaction->setTransactionStripeId( $session->id );

        return [
            "status" => 200,
            "data" => [
                "url" => $session->url,
            ],
            "message" => $this->_message->get("pacakge.purchase.success")
        ];
    }


    public function purchase( WP_REST_Request $request)
    {
        $token = $request->get_param('token');
        $decodedToken = JWTHelper::check($token);

        if(!isset($decodedToken->transaction_id) && !isset($decodedToken->user_id))
        {
            return $decodedToken;
        }

        if($request["user_id"] != $decodedToken->user_id)
        {
            return [
                "status" => 400,
                "message" => "user not match"
            ];
        }

        $transaction = new Transaction( $decodedToken->transaction_id);
        if($transaction instanceof Exception)
        {
            return [
                "status" => 400,
                "message" => "Transaction not found"
            ];
        }

        $secretKey = get_field( "stripe_secret_key", "option");
        $stripe = new StripeClient($secretKey);

        $checkoutSession = $stripe
                            ->checkout
                            ->sessions
                            ->retrieve($transaction->getTransactionStripeId());
                            
        if($checkoutSession->payment_status === "paid")
        {
            $user_id = $decodedToken->user_id;
            $company = new Company($user_id);
            $package = new Package($transaction->getPackageId());

            if(!$transaction->isGranted())
            {
                return [
                    "status" => 400,
                    "message"=> "This transaction already granting credit"
                ];
            }
            
            $transaction->granted();
            $company->grant($package->getCredit());

            return [
                "status" => 200,
                "message" => $package->getCredit() . "credit already added into your balance",
            ];
        }
    }
}
