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
    const ACCOUNT_PASSWORD_FORGOT = "account.password_forgot";

    const PAYMENT_SUCCESSFULL = "payment.successfull";
    const PAYMENT_CONFIRMATION = "payment.confirmation";

    public function __construct()
    {
        $this->list = [
            "vacancy" => [
                "rejected"  => __("Helaas is je vacature afgewezen. Maak je geen zorgen, je kunt ze opnieuw plaatsen door onze instructies te volgen.", THEME_DOMAIN),
                "submitted" => __("Uw vacature is succesvol verzonden. Wacht maximaal 2 x 24 uur op verificatie van onze beheerder.", THEME_DOMAIN),
                "published" => __("Gefeliciteerd! Uw bericht is succesvol gepubliceerd.", THEME_DOMAIN),
                "expired_1" => __("vervalt morgen. Maak je geen zorgen, je kunt verlengen met een premium post!", THEME_DOMAIN),
                "expired_3" => __("vervalt over 3 dagen.", THEME_DOMAIN),
                "expired_7" => __("vervalt over zeven dagen.", THEME_DOMAIN),
                "expired_5" => __("vervalt over 5 dagen.", THEME_DOMAIN),
                "expired"   => __("is verlopen!", THEME_DOMAIN),
                "applied"   => __("Er is een kandidaat die op uw functie solliciteert: :", THEME_DOMAIN)
            ],
            "payment" => [
                "successfull" => __("Gefeliciteerd! Uw betaling is succesvol verwerkt.", THEME_DOMAIN),
                "confirmation" => __("Hierbij bevestigen wij uw aankoop Recruitment Valley. Bedankt voor uw vertrouwen. De factuur sturen wij u later per e-mail toe.", THEME_DOMAIN),
            ],
            "account" => [
                "created" => __("Gefeliciteerd, u bent klaar met het registreren van uw account! Uw account is nu klaar voor gebruik.", THEME_DOMAIN),
                "password_reset" => __("Uw wachtwoord is gereset!", THEME_DOMAIN),
                "password_change" => __("Uw wachtwoord is gewijzigd!", THEME_DOMAIN),
                "password_forgot" => __("Er is een verzoek om het wachtwoord van uw account opnieuw in te stellen.", THEME_DOMAIN)
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
