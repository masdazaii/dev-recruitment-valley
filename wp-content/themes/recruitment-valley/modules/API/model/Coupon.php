<?php
namespace Model;
use Exception;
use WP_Query;
class Coupon
{
    public $prefix = "coupon";
    public $slugCPT = "coupon";

    const STATUS_AVAILABLE_VALUE = "available";
    const STATUS_AVAILABLE_LABEL = "Available";

    const STATUS_RUN_OUT_VALUE = "run_out";
    const STATUS_RUN_OUT_LABLE = "Run Out";

    const STATUS_EXPIRED_VALUE = "expired";
    const STATUS_EXPIRED_LABEL = "Expired";

    const DISCOUNT_TYPE_FIX_AMOUNT_VALUE = "fix_amount";
    const DISCOUNT_TYPE_FIX_AMOUNT_LABEL = "Fix Amount";
    const DISCOUNT_TYPE_PERCENTAGE_VALUE = "percentage";
    const DISCOUNT_TYPE_PERCENTAGE_LABEL = "Percentage";
    
    public $couponID;
    public $post;
    public $porperties;
    public $status = "status";
    public $expiredAt = "expired_at";
    public $code = "code";
    public $discount_value = "discount_value";
    public $discount_type = "discount_type";
    public $description = "description";
    private $metaUsedCount = "used_count";
    private $metaUsedBy = "used_by";
    public function __construct($coupon_id = false)
    {
        if ($coupon_id) {
            $this->couponID = $coupon_id;
            $this->post = get_post($coupon_id);
            $this->porperties = get_fields($coupon_id);
            if (!$this->post) {
                throw new Exception("Coupon not found", 400);
            }
        }
    }
    /**
     * Setter function
     *
     * Set acf / meta / post data
     *
     * @return mixed
     */
    public function set($key, $value, $type = 'acf'): mixed
    {
        return  update_field($key, $value, $this->couponID);
    }
    /**
     * Getter function
     *
     * @param string $key   : acf / meta key
     * @param string $value : value to store
     * @return mixed : either object, array
     */
    public function get($key): mixed
    {
        $key = $this->prefix . '_' . $key;
        if ($this->porperties && is_array($this->porperties) && array_key_exists($key, $this->porperties)) {
            return $this->porperties[$key];
        }
        return get_field($key, $this->couponID, true);
    }
    public function getMeta($key, $single = true): mixed
    {
        return get_post_meta($this->couponID, $key, $single);
    }
    public function getExpiredAt(): int
    {
        $date = $this->get($this->expiredAt);
        return strtotime($date);
    }
    public function getCode(): string
    {
        return $this->get($this->code);
    }
    public function getDiscountType(): array
    {
        return $this->get($this->discount_type);
    }
    public function getDescription(): string | null
    {
        return $this->get($this->description);
    }
    public function getStatus(): array
    {
        return $this->get($this->status);
    }
    public function getDiscountValue(): int
    {
        return $this->get($this->discount_value);
    }
    public function getTitle(): string
    {
        if ($this->post) {
            return $this->post->post_title;
        } else {
            throw new Exception("Please specify the coupon!");
        }
    }
    /**
     * Set coupon by coupon code function
     *
     * @param string $code
     * @return void
     */
    public function setByCode($code)
    {
        if (isset($code)) {
            $coupon = new WP_Query([
                'post_type'     => $this->slugCPT,
                'numberposts'   => 1,
                'meta_query'    => [
                    [
                        'key'   => $this->prefix . '_' . $this->code,
                        'value' => $code,
                        'compare'   => '='
                    ]
                ]
            ]);
            if ($coupon->post_count > 0) {
                $this->couponID = $coupon->posts[0]->ID;
                $this->post     = $coupon->posts[0];
                return true;
            } else {
                throw new Exception("Coupon not found!");
            }
        } else {
            throw new Exception("Specify the coupon code!");
        }
    }
    
    public function getUsedCount()
    {
        $metaValue = $this->getMeta($this->metaUsedCount);
        if (empty($metaValue) || !$metaValue) {
            return 0;
        }
        return (int)$metaValue;
    }

    public function getUsedBy()
    {
        $metaValue = $this->getMeta($this->metaUsedBy);
        if (empty($metaValue) || !$metaValue) {
            return [];
        }
        return $metaValue;
    }

    public function getProperties()
    {
        return $this->porperties;
    }

    public function validate( $args )
    {
        if(count($this->porperties["coupon_rule"]) === 0) return true;

        foreach ($this->porperties["coupon_rule"] as $rule ) {
            switch($rule["acf_fc_layout"]){
                case "coupon_max_used" :
                    $this->maxUse( $rule );
                case "coupon_max_used_per_user" :
                    $this->maxUsePerUser($rule, $args["user_id"]);
            }
        }
    }

    private function maxUse( $rule )
    {
        $coupon_used = get_post_meta($this->couponID, 'used', true) ?? 0;
        if( $coupon_used >= (int) $rule["count"] )
        {
            throw new Exception("Coupon reached use limit", 400);
        }

        return true;
    }

    private function maxUsePerUser( $rule , $user_id )
    {
        $used_by = get_post_meta($this->couponID, 'used_by', true);
        $used_by = maybe_unserialize( $used_by );

        $used_count = 0;
        foreach ($used_by as $uid) {
            if((int) $user_id == (int) $uid)
            {
                $used_count++;
            }
        }

        if($used_count >= (int) $rule["count"])
        {
            throw new Exception("You already reached the coupon limit used", 400);
        }

        return true;
    }
}