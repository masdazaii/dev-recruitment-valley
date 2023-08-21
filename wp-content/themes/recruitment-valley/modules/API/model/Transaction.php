<?php

class Transaction
{
    private $post_type = "transaction";

    private $post;

    private $transaction_id;

    private $transaction_stripe_id = "transaction_stripe_id";
    private $transaction_user_name = "transaction_user_name";
    private $transaction_package_name= "transaction_package_name";
    private $transaction_amount = "transaction_amount";
    private $transaction_user_id = "transaction_user_id";
    private $transaction_package_id = "transaction_pacakge_id";

    private $granted = "granted";

    public function __construct($transactionId = false)
    {
        if($transactionId)
        {
            $this->transaction_id = $transactionId;
            $transaction = get_post($transactionId);
            if($transaction)
            {
                $this->post = $transaction; 
            }else{
                throw new Exception( "Transaction was not valid", 400 );
            }
        }
    }

    public function storePost( $args )
    {
        $transaction = wp_insert_post([
            "post_title" => $args["title"],
            "post_type" => $this->post_type,
            "post_status" => "publish"
        ]);

        if($transaction)
        {
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
        return $this->getProp($this->transaction_user_id);
    }

    public function getPackageId()
    {
        return $this->getProp($this->transaction_package_id);
    }

    public function setTransactionStripeId( $stripeId )
    {
        return $this->setProp($this->transaction_stripe_id, $stripeId );
    }

    public function setUserName( $username)
    {
        return $this->setProp($this->transaction_user_name, $username );
    }

    public function setPackageName($packageName)
    {
        return $this->setProp($this->transaction_package_name, $packageName);
    }

    public function setTransactionAmount( $amount )
    {
        return $this->setProp($this->transaction_amount, $amount);
    }

    public function setUserId( $userId )
    {
        return $this->setProp($this->transaction_user_id, $userId);
    }

    public function setPackageId( $packageId )
    {
        return $this->setProp($this->transaction_package_id, $packageId);
    }

    public function getProp( $field )
    {
        return get_field($field, $this->transaction_id);
    }

    public function setProp( $field, $value )
    {
        return update_field($field, $value, $this->transaction_id);
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

}