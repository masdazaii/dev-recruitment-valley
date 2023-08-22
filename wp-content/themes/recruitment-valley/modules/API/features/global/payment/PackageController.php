<?php

namespace Global;

use Constant\Message;
use Error;
use Exception;
use JWTHelper;
use Model\Company;
use Package;
use ResponseHelper;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Webhook;
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

    public function createPaymentUrl(WP_REST_Request $request)
    {

        $packageId = $request["packageId"];
        $userId = $request["user_id"];

        $package = new Package($packageId);
        $packagePrice = $package->getPrice();
        $pacakgeDescription = $package->getDescription();

        $secretKey = get_field("stripe_secret_key", "option");
        $stripe = new \Stripe\StripeClient($secretKey);
        $amount = $packagePrice * 100;

        $company = new Company($userId);
        $package = new Package($packageId);

        $transaction = new Transaction;
        $transactionTitle = "Transaction from " . $company->getName() . " Buy " . $package->getTitle();

        $transactionId = $transaction->storePost(["title" => $transactionTitle]);

        if (is_wp_error($transactionId) || !$transactionId) {
            return [
                "status" => 500,
                "message" => "something error when creating payment"
            ];
        }

        $transaction->setUserName($company->getName());
        $transaction->setPackageName($package->getTitle());
        $transaction->setTransactionAmount($amount / 100);
        $transaction->setUserId($company->getId());
        $transaction->setPackageId($package->getPackageId());
        $transaction->setStatus("pending");

        $encodedTransactionID = JWTHelper::generate(
            [
                "transaction_id" => $transaction->getTransactionId(),
                "user_id" => $company->getId(),
            ],
            "+1 day"
        );

        $stripeMetadata = [
            "transaction_id" => $transaction->getTransactionId(),
            "user_id" => $company->getId(),
            "package_id" => $package->getPackageId()
        ];

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
            'success_url' => "https://dev-recruitment-valley.vercel.app/werkgever/tegoed/succes" . "?token=" . $encodedTransactionID, // success url after payment
            'cancel_url' => "https://dev-recruitment-valley.vercel.app/werkgever/tegoed" . "?token=" . $encodedTransactionID, // fail redit=rect payment
            'payment_intent_data' => [
                "metadata" => $stripeMetadata
            ],
            "payment_method_types" => [
                "card", "ideal"
            ],
        ]);

        $transaction->setTransactionStripeId($session->id);

        return [
            "status" => 200,
            "data" => [
                "url" => $session->url,
            ],
            "message" => $this->_message->get("pacakge.purchase.success")
        ];
    }


    public function purchase(WP_REST_Request $request)
    {
        $token = $request->get_param('token');
        $decodedToken = JWTHelper::check($token);

        if (!isset($decodedToken->transaction_id) && !isset($decodedToken->user_id)) {
            return $decodedToken;
        }

        if ($request["user_id"] != $decodedToken->user_id) {
            return [
                "status" => 400,
                "message" => "user not match"
            ];
        }

        $transaction = new Transaction($decodedToken->transaction_id);
        if ($transaction instanceof Exception) {
            return [
                "status" => 400,
                "message" => "Transaction not found"
            ];
        }

        $package = new Package($transaction->getPackageId());
        $user = get_user_by('ID', $transaction->getUserId());

        $secretKey = get_field("stripe_secret_key", "option");
        $stripe = new StripeClient($secretKey);

        $checkoutSession = $stripe
            ->checkout
            ->sessions
            ->retrieve($transaction->getTransactionStripeId());

        return [
            "status" => 200,
            "data" => [
                "package" => [
                    "price" => intval($package->getPrice()),
                    "credit" => intval($package->getCredit()),
                    "pricePerCredit" => $package->getPrice() / $package->getCredit(),
                ],
                "status" => $transaction->getStatus(),
                "date" => $transaction->getDate("d F Y"),
                "transactionId" => $transaction->getTransactionId(),
                "transactionStripeId" => $transaction->getTransactionStripeId()
            ]
        ];

        // if($checkoutSession->payment_status === "paid")
        // {
        //     $user_id = $decodedToken->user_id;
        //     $company = new Company($user_id);
        //     $package = new Package($transaction->getPackageId());

        //     if(!$transaction->isGranted())
        //     {
        //         return [
        //             "status" => 400,
        //             "message"=> "This transaction already granting credit"
        //         ];
        //     }

        //     $transaction->granted();
        //     $company->grant($package->getCredit());

        //     return [
        //         "status" => 200,
        //         "message" => $package->getCredit() . "credit already added into your balance",
        //     ];
        // }
    }


    public function onWebhookTrigger(WP_REST_Request $request)
    {
        $secretKey = get_field("stripe_secret_key", "option");
        $stripe = new \Stripe\StripeClient($secretKey);

        $payload = @file_get_contents('php://input');
        $endpoint_secret = 'whsec_02c7938964b4c50fc49380728f70538105c68b52df5a50da93130db4e6023ebf';

        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            $response = [
                "status" => 400,
                "message" => $e->getMessage()
            ];

            error_log(json_encode($response));

            return $response;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            $response = [
                "status" => 400,
                "message" => $e->getMessage()
            ];

            error_log(json_encode($response));

            return $response;
        }

        error_log($event->type);

        switch ($event->type) {
            case "charge.succeeded":
                return $this->onPaymentSuccess($event);
            case "payment_intent.payment_failed":
                return $this->onPaymentFail($event);
            default:
                return [
                    "status" => 400,
                    "message" => "event not registered"
                ];
        }
    }

    public function onPaymentSuccess($data)
    {
        error_log("payment success webhook triggerred");

        error_log(json_encode($data));

        $transaction_data = $data->data->object;

        if ($transaction_data->status === "succeeded" && $transaction_data->paid) {
            $user_id = $transaction_data->metadata->user_id;
            $company = new Company($user_id);
            $transaction = new Transaction($transaction_data->metadata->transaction_id);
            $package = new Package($transaction->getPackageId());

            if (!$transaction->isGranted()) {
                return [
                    "status" => 400,
                    "message" => "This transaction already granting credit"
                ];
            }

            $transaction->setStatus("success");
            $transaction->granted();
            $company->grant($package->getCredit());

            return [
                "status" => 200,
                "message" => "Success granting credit"
            ];
        }

        return [
            "status" => 400,
            "message" => "payment fail"
        ];
    }

    public function onPaymentFail($data)
    {
        error_log("payment fail webhook triggerred");

        error_log(json_encode($data));

        $transaction = new Transaction($data->data->object->metadata->transaction_id);

        $transaction->setStatus("failed");

        return [
            "status" => 400,
            "message" => "payment fail triggerred"
        ];
    }
}
