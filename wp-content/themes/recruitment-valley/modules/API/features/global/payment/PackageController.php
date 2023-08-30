<?php

namespace Global;

use BD\Emails\Email;
use Constant\Message;
use Error;
use Exception;
use Helper\EmailHelper;
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
            'post_status' => 'publish',
            'orderby'   => 'meta_value_num',
            'meta_key' => 'rv_package_price',
            'order' => "ASC"
        ]);

        $responseData = [];
        foreach ($data as $post) {
            $isFavorite = get_field('is_favorite', $post->ID) ?? false;

            $package = new Package($post->ID);
            $responseData[] = [
                'id' => $post->ID,
                'slug' => $post->post_name,
                'packageName' => $post->post_title,
                'packageDescription' => $post->post_content,
                'packagePrice' => $package->getPrice(),
                'packageCreditQuantity' => $package->getCredit() < 0 ? "unlimited" : $package->getCredit(),
                'pricePerCredit' => $package->getPricePerVacany(),
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
                        'tax_behavior' => "exclusive",

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
            'metadata' => $stripeMetadata,
            'invoice_creation' => [
                'enabled' => true,
                'invoice_data' => [
                    'rendering_options' => ['amount_tax_display' => 'exclude_tax'],
                ],
            ],
            'automatic_tax' => [
                'enabled' => true,
            ],
            "payment_method_types" => [
                "card", "ideal"
            ],
        ]);

        $transaction->setTransactionStripeId($session->id);

        EmailHelper::sendPaymentConfirmation($transactionId);

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
                    /** Added line start here */
                    "taxAmount" => $transaction->getTaxAmount(),
                    "totalPayment" => $transaction->getTotalAmount()
                ],
                "status" => $transaction->getStatus(),
                "date" => $transaction->getDate("d F Y"),
                "transactionId" => $transaction->getTransactionId(),
                "transactionStripeId" => $transaction->getTransactionStripeId()
            ]
        ];
    }


    public function onWebhookTrigger(WP_REST_Request $request)
    {
        $secretKey = get_field("stripe_secret_key", "option");
        $stripe = new \Stripe\StripeClient($secretKey);

        $payload = @file_get_contents('php://input');
        // $endpoint_secret = 'whsec_3Z07iu7314TUwmpuohrnAEuV6BwcgcoT';

        /** Local */
        // $endpoint_secret = 'whsec_02c7938964b4c50fc49380728f70538105c68b52df5a50da93130db4e6023ebf';

        /** Staging */
        $endpoint_secret = 'whsec_3Z07iu7314TUwmpuohrnAEuV6BwcgcoT';

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
                return $this->onChargeSucceeded($event);
            case "checkout.session.completed":
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

        if ($transaction_data->status === "complete" && $transaction_data->payment_status == "paid") {
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

            $this->sendInvoice($transaction_data->invoice, $company->getEmail());

            error_log("granting user");
            $transaction->setStatus("success");
            $transaction->granted();

            /** Anggit's syntax start here */
            // $company->grant($package->getCredit());

            /** Changes start here */
            // Set acf tax and total amount
            $transaction->setTaxAmount((float)$transaction_data->total_details->amount_tax / 100);
            $transaction->setTotalAmount((float)$transaction_data->amount_total / 100);

            // Get unlimited package data
            $unlimitedPackage = get_field('rv_package_credit_quantity', $transaction->getPackageId(), true);
            // Check if purchased package is unlimited or not
            if ($unlimitedPackage == -1) {
                // if true, set user meta
                $company->grantUnlimited();
            } else {
                // if false, set user credit
                $company->grant($package->getCredit());
            }
            /** Changes end here */

            $user = get_user_by('id', $user_id);

            $args = [
                'client.name' => $user->display_name,
                'price.total' => $transaction->getTransactionAmount(),
                'transaction.number' => $transaction->getTransactionStripeId(),
                'transaction.package' => $transaction->getPackageName(),
                'transaction.date'  => $transaction->getDate('j F Y'),
            ];

            $site_title = get_bloginfo('name');
            Email::send(
                $user->user_email,
                sprintf(__('Betaling gelukt - %s', "THEME_DOMAIN"), $site_title),
                $args,
                'payment-package-success.php'
            );

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

    public function sendInvoice($invoiceId, $email = false)
    {
        error_log("start sending invoice");

        $secretKey = get_field("stripe_secret_key", "option");
        \Stripe\Stripe::setApiKey($secretKey);

        try {
            // Retrieve the invoice
            $invoice = \Stripe\Invoice::retrieve($invoiceId);

            // Update customer email
            if ($email) {
                $customer = \Stripe\Customer::retrieve($invoice->customer);
                $customer->email = $email;
                $customer->save();
            }

            // Send the invoice
            $invoice->sendInvoice();
            error_log("invoice {$invoice->id} already sent to customer");
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Sent invoice error : " . $e->getMessage());
        }
    }

    public function onChargeSucceeded($data)
    {
        error_log("send receipt start");

        error_log(json_encode($data));

        return [
            "status" => 200,
            "message" => "Success get receipt"
        ];
    }

    // public function sendReceipt($paymentIntentId, $email = false)
    // {
    //     $secretKey = get_field("stripe_secret_key", "option");
    //     \Stripe\Stripe::setApiKey($secretKey);

    //     try {
    //         $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

    //         $paymentIntent->sendInvoice();
    //         error_log("invoice {$invoice->id} already sent to customer");

    //     } catch (\Stripe\Exception\ApiErrorException $e) {
    //         error_log("Sent invoice error : ". $e->getMessage());
    //     }
    // }
}
