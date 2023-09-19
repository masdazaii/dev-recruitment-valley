<?php

namespace constant;

class NotificationConstant
{
    public $list;

    const VACANCY_REJECTED = "vacancy.rejected";
    const VACANCY_SUBMITTED = "vacancy.submitted";
    const VACANCY_PUBLISHED = "vacancy.published";
    const VACANCY_EXPIRED = "vacancy.expired";
    const VACANCY_EXPIRED_1 = "vacancy.expired_1";
    const VACANCY_EXPIRED_3 = "vacancy.expired_3";
    const VACANCY_EXPIRED_7 = "vacancy.expired_7";
    const VACANCY_EXPIRED_5 = "vacancy.expired_5";
    const VACANCY_APPLIED = "vacancy.applied";

    const ACCOUNT_CREATED = "account.created";
    const ACCOUNT_PASSWORD_RESET = "account.password_reset";
    const ACCOUNT_PASSWORD_CHANGE = "account.password_change";

    const PAYMENT_SUCCESSFULL = "payment.successfull";
    const PAYMENT_CONFIRMATION = "payment.confirmation";

    public function __construct()
    {
        $this->list = [
            "vacancy" => [
                "rejected"  => __("Unfortunately, your job post was rejected. Don\'t worry, you can repost them again by following our instruction.", THEME_DOMAIN),
                "submitted" => __("Your job post has successfully sent, please wait a maximum of 2x24 hours for verifications from our admin.", THEME_DOMAIN),
                "published" => __("Congratulations! your post has ben published successfully.", THEME_DOMAIN),
                "expired_1" => __("will expire tommorow. Don't worry, you can extend with a premium post!", THEME_DOMAIN),
                "expired_3" => __("will expire in 3 days.", THEME_DOMAIN),
                "expired_7" => __("will expire in 7 days.", THEME_DOMAIN),
                "expired_5" => __("will expire in 5 days.", THEME_DOMAIN),
                "expired"   => __("has expired!", THEME_DOMAIN),
                "applied"   => __("There is a candidate apply to your job :", THEME_DOMAIN)
            ],
            "payment" => [
                "successfull" => __("Payment success.", THEME_DOMAIN),
                "confirmation" => __("Payment confirmation.", THEME_DOMAIN),
            ],
            "account" => [
                "created" => __("Congratulations! your account is created.", THEME_DOMAIN),
                "password_reset" => __("Your password is changed!.", THEME_DOMAIN),
                "password_change" => __("Your password is changed!.", THEME_DOMAIN),
            ],
        ];
    }

    public function get($message_location): string|array
    {
        $keys = explode('.', $message_location);
        $message = $this->list;

        foreach ($keys as $key) {
            if (isset($message[$key])) {
                $message = $message[$key];
            } else {
                return ""; // Key not found, return null or any other default value you prefer.
            }
        }

        return $message;
    }
}
