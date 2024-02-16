<?php

class Transaction
{
    private $post_type = "transaction";

    private $post;

    private $transaction_id;

    public $transaction_stripe_id = "transaction_stripe_id";
    private $transaction_user_name = "transaction_user_name";
    private $transaction_package_name = "transaction_package_name";
    private $transaction_amount = "transaction_amount";
    private $transaction_user_id = "transaction_user_id";
    private $transaction_package_id = "transaction_pacakge_id";

    private $_transaction_tax_amount = "transaction_tax_amount";
    private $_transaction_total_amount = "transaction_total_amount";

    private $_transaction_coupon_used = "transaction_coupon_used";
    private $_transaction_total_amount_before_coupon_discount = "transaction_total_amount_before_coupon_discount";

    private $granted = "granted";

    public function __construct($transactionId = false)
    {
        if ($transactionId) {
            $this->transaction_id = $transactionId;
            $transaction = get_post($transactionId);
            if ($transaction) {
                $this->post = $transaction;
            } else {
                throw new Exception("Transaction was not valid", 400);
            }
        }
    }

    public function storePost($args)
    {
        $transaction = wp_insert_post([
            "post_title" => $args["title"],
            "post_type" => $this->post_type,
            "post_status" => "publish"
        ]);

        if ($transaction) {
            $this->transaction_id = $transaction;
        }

        return $transaction;
    }

    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    public function getTransactionStripeId()
    {
        return $this->getProp($this->transaction_stripe_id);
    }

    public function getUserName()
    {
        return $this->getProp($this->transaction_user_name);
    }

    public function getPackageName()
    {
        return $this->getProp($this->transaction_package_name);
    }

    public function getTransactionAmount()
    {
        return $this->getProp($this->transaction_amount);
    }

    public function getUserId()
    {
        $user = $this->getProp($this->transaction_user_id);
        return $user["ID"];
    }

    public function getPackageId()
    {
        return $this->getProp($this->transaction_package_id);
    }

    public function setTransactionStripeId($stripeId)
    {
        return $this->setProp($this->transaction_stripe_id, $stripeId);
    }

    public function setUserName($username)
    {
        return $this->setProp($this->transaction_user_name, $username);
    }

    public function setPackageName($packageName)
    {
        return $this->setProp($this->transaction_package_name, $packageName);
    }

    public function setTransactionAmount($amount)
    {
        return $this->setProp($this->transaction_amount, $amount);
    }

    public function setUserId($userId)
    {
        return $this->setProp($this->transaction_user_id, $userId);
    }

    public function setPackageId($packageId)
    {
        return $this->setProp($this->transaction_package_id, $packageId);
    }

    public function getDate($format = "Y-m-d H:i:s")
    {
        $date = $this->post->post_date;
        $date = date_create($date);
        return date_format($date, $format);
    }

    public function getProp($field)
    {
        return get_field($field, $this->transaction_id);
    }

    public function setProp($field, $value)
    {
        return update_field($field, $value, $this->transaction_id);
    }

    public function setStatus($status)
    {
        $valid_statuses = array('pending', 'success', 'failed'); // Add more as needed

        if (in_array($status, $valid_statuses)) {
            $termExist = term_exists($status, 'payment_status');
            if($termExist)
            {
                // Set the new payment status term
                wp_set_post_terms($this->transaction_id, $termExist["term_id"], 'payment_status', false);
            }
        }
    }

    public function getStatus()
    {
        $status_terms = wp_get_post_terms($this->transaction_id, 'payment_status');
        if (!empty($status_terms)) {
            return $status_terms[0]->name;
        }
    }

    /**
     * isGranted
     * check if the transaction aready granted or not yet
     *
     * @return void
     */
    public function isGranted()
    {
        return get_post_meta($this->transaction_id, $this->granted, true) != true ? true : false;
    }

    /**
     * granted
     * make the transaction cannot be use again in the future
     *
     * @return void
     */
    public function granted()
    {
        update_post_meta($this->transaction_id, $this->granted, true);
    }
    
    /**
     * hasCoupon
     * check whether transaction have coupon or no
     * 
     * @return void
     */
    public function hasCoupon()
    {
        $couponData = $this->getProp($this->_transaction_coupon_used);
        return $couponData ? true : false; 
    }

    public function getTotalAmountBeforeDiscount()
    {
        return $this->getProp($this->_transaction_total_amount_before_coupon_discount);
    }

    public function setTaxAmount($amount)
    {
        return $this->setProp($this->_transaction_tax_amount, $amount);
    }

    public function setTotalAmount($amount)
    {
        return $this->setProp($this->_transaction_total_amount, $amount);
    }

    public function getTaxAmount()
    {
        return $this->getProp($this->_transaction_tax_amount);
    }
    public function getTotalAmount()
    {
        return $this->getProp($this->_transaction_total_amount);
    }

    public function setTransactionAmountBeforeCoupon($value)
    {
        return $this->setProp($this->_transaction_total_amount_before_coupon_discount, $value);
    }

    public function setCouponData($value)
    {
        return $this->setProp($this->_transaction_coupon_used, $value);
    }
}
