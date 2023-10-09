<?php

namespace Global;

use Constant\Message;
use Model\Coupon;

class CouponController
{
    private $_slug;
    private $_message;

    public function __construct()
    {
        $this->_slug = 'coupon';
        $this->_message = new Message();
    }

    public function list($request)
    {
        $coupons = [
            [
                "title"         => "Coupon 1",
                "code"          => "QWERTY123456",
                "description"   => "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Eius, adipisci.",
                "expiredAt"     => strtotime("2023-10-09"),
                "discount"      => [
                    "type"      => [
                        "value" => "percentage",
                        "label" => "Percentage"
                    ],
                    "value"     => "10"
                ],
                "status"        => [
                    "value"     => "available",
                    "label"     => "Available"
                ]
            ],
            [
                "title"         => "Coupon 2",
                "code"          => "A1S2D3F4",
                "description"   => "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Eius, adipisci.",
                "expiredAt"     => strtotime("2023-10-10"),
                "discount"      => [
                    "type"      => [
                        "value" => "fixed",
                        "label" => "Fixed Price"
                    ],
                    "value"     => "11.99"
                ],
                "status"        => [
                    "value"     => "available",
                    "label"     => "Available"
                ]
            ]
        ];

        $filters = [
            'status'    => $request['status'],
            'type'      => $request['type']
        ];

        /** Get Coupons */
        // $coupons = get_posts([
        //     'post_type'     => $this->_slug,
        //     'post_status'   => 'publish',
        //     // 'orderby'       => 'meta_value_num',
        //     // 'meta_key'      => 'rv_package_price',
        //     'order'         => "ASC"
        // ]);

        return [
            "status"    => 200,
            "message"   => $this->_message->get('coupon.get_success'),
            "data"      => $coupons
        ];
    }

    public function show($request)
    {
        $coupon = [
            "title"         => "Coupon 1",
            "code"          => "QWERTY123456",
            "description"   => "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Eius, adipisci.",
            "expiredAt"     => strtotime("2023-10-09"),
            "discount"      => [
                "type"      => [
                    "value" => "percentage",
                    "label" => "Percentage"
                ],
                "value"     => "10"
            ],
            "status"        => [
                "value"     => "available",
                "label"     => "Available"
            ]
        ];

        return [
            "status"    => 200,
            "message"   => $this->_message->get('coupon.get_success'),
            "data"      => $coupon
        ];
    }

    /**
     * Get currently used coupon function
     *
     * @return array
     */
    public function inUse($request)
    {
        /** Get currently used coupon */
        // $couponID = get_user_meta($request['user_id'], 'coupon_id', true);

        /** Get Coupon */
        // $coupon = new Coupon($couponID);
        // $couponData = [
        //     "title"         => $coupon->getTitle(),
        //     "code"          => $coupon->getCode(),
        //     "description"   => $coupon->getDescription(),
        //     "expiredAt"     => $coupon->getExpiredAt('timestamp'),
        //     "discount"      => [
        //         "type"      => $coupon->getDiscountType(),
        //         "value"     => $coupon->getDiscountValue()
        //     ],
        //     "status"        => $coupon->getStatus()
        // ];

        $coupon = [
            "title"         => "Coupon 1",
            "code"          => "QWERTY123456",
            "description"   => "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Eius, adipisci.",
            "expiredAt"     => strtotime("2023-10-09"),
            "discount"      => [
                "type"      => [
                    "value" => "percentage",
                    "label" => "Percentage"
                ],
                "value"     => "10"
            ],
            "status"        => [
                "value"     => "available",
                "label"     => "Available"
            ]
        ];

        return [
            "status"    => 200,
            "message"   => $this->_message->get('coupon.get_success'),
            "data"      => $coupon
        ];
    }

    /**
     * Apply Coupon function
     *
     * @param array $request
     * @return array
     */
    public function apply($request)
    {
        /** Check Expiry */

        /** Get Coupon */

        /** Set Coupon & User meta */

        /** Decrease Coupon Availability */

        return [
            "status"    => 200,
            "message"   => $this->_message->get('coupon.apply_success')
        ];
    }
}
