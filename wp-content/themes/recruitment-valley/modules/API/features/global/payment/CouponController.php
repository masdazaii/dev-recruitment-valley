<?php

namespace Global;

use Constant\Message;
use Model\Coupon;
use Package;
use WP_Error;
use WP_Query;

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
        // try {
        //     $coupons = get_posts([
        //         'post_type'     => $this->_slug,
        //         'post_status'   => 'publish',
        //         // 'orderby'       => 'meta_value_num',
        //         // 'meta_key'      => 'rv_package_price',
        //         'order'         => "ASC"
        //     ]);
        //     print('<pre>' . print_r($coupons, true) . '</pre>');
        //     die;

        //     // Set response data
        //     $responseData = [];
        //     if (!empty($coupons) && is_array($coupons)) {
        //         foreach ($coupons as $coupon) {
        //             try {
        //                 $couponModel = new Coupon();

        //                 $responseData[] = [
        //                     'title' => $coupon->post_title,
        //                     'code'  => $
        //                 ];
        //             } catch (\Exception $e) {
        //                 error_log($e);
        //             }
        //         }
        //     }

        //     return [
        //         "status"    => 200,
        //         "message"   => $this->_message->get('coupon.get_success'),
        //         "data"      => $responseData
        //     ];
        // } catch (WP_Error $error) {
        //     return $error;
        // } catch (\Exception $e) {
        //     return $e;
        // } catch (\Throwable $th) {
        //     return $th;
        // }

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
        try {
            $coupon = new Coupon();
            $coupon->setByCode($request['coupon']);
            
            if($coupon->isExpired()) return [
                "status"    => 400,
                "message"   => "Coupon expired"
            ];

            $coupon->validate(["user_id" => $request['user_id']]);

            // Calculate Package Price
            $package    = new Package($request['packageId']);
            $price      = $package->getPrice();

            // Get Discount
            $discounType = $coupon->getDiscountType()['value'];
            if ($discounType == $coupon::DISCOUNT_TYPE_PERCENTAGE_VALUE) {
                $discount = $coupon->getDiscountValue();
                $discount = ((float)$discount / 100) * (float)$price;
            } else if ($discounType == $coupon::DISCOUNT_TYPE_FIX_AMOUNT_VALUE) {
                $discount = (float)$coupon->getDiscountValue();
            } else {
                $discount = 0;
            }

            $newPrice = (float)$price - $discount;

            return [
                "status"    => 200,
                "message"   => $this->_message->get('coupon.apply_success'),
                "data"      => [
                    "coupon"    => [
                        "title" => $coupon->getTitle(),
                        "discountType"  => $discounType,
                        "discountValue" => $coupon->getDiscountValue(),
                        "discountPrice" => $discount,
                    ],
                    "packageOldPrice" => (float)$price,
                    "packageNewPrice" => $newPrice
                ]
            ];
        } catch (\WP_Error $error) {
            return [
                "status"    => 400,
                "message"   => $error->get_error_message()
            ];
        } catch (\Exception $e) {
            return [
                "status"    => 400,
                "message"   => $e->getMessage()
            ];
        } catch (\Throwable $throw) {
            return [
                "status"    => 400,
                "message"   => $throw->getMessage()
            ];
        }

        return [
            "status"    => 200,
            "message"   => $this->_message->get('coupon.apply_success')
        ];
    }
}
