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
    const VACANCY_APPLIED = "vacancy.applied";

    const ACCOUNT_CREATED = "account.created";
    const ACCOUNT_PASSWORD_RESET = "account.password_reset";
    const ACCOUNT_PASSWORD_CHANGE = "account.password_change";

    const PAYMENT_SUCCESSFULl = "payment.successfull";
    const PAYMENT_CONFIRMATION = "payment.confirmation";

    public function __construct()
    {
        $this->list = [
            "vacancy" => [
                "rejected",
                "submitted",
                "published",
                "expired_1",
                "expired_3",
                "expired_7",
                "expired",
                "applied"
            ],
            "payment" => [
                "successfull",
                "confirmation"
            ],
            "account" => [
                "created",
                "password_reset",
                "password_change",
            ],
        ];
    }
}