<?php

namespace Constant;

class Message
{
    public $list;

    public function __construct()
    {
        $this->list = [
            'auth' => [
                'unauthenticate' => __('Unauthorized', THEME_DOMAIN),
                'invalid_token' => __('Token ongeldig', THEME_DOMAIN),
                'expired' => __('Verlopen', THEME_DOMAIN),
                'generate_token_success' => __('Token succes aangemaakt', THEME_DOMAIN),
                'generate_token_error' => __('Er iets misgegaan bij het aanmaken van een token', THEME_DOMAIN),
                'user_deleted' => __('User was not active', THEME_DOMAIN),
                'reactivation_sent' => __('Je account is niet actief. Bekijk je email om je account opnieuw te activeren.', THEME_DOMAIN),
                'forgot_password' => [
                    "required_email" => __('Email is nodig', THEME_DOMAIN),
                    "email_sent" => __('Email is verstuurd', THEME_DOMAIN),
                    "email_not_sent" => __('Email is niet verstuurd', THEME_DOMAIN),
                    "failed" => __('Failed making forgot password request.', THEME_DOMAIN)
                ],
                "reset_password" => [
                    "new_password_required" => __("Nieuw Wachtwoord nodig", THEME_DOMAIN),
                    "repeat_password_required" => __("Herhaal het nieuwe wachtwoord", THEME_DOMAIN),
                    "key_required" => __("Sleutel vereist", THEME_DOMAIN),
                    "password_missmatch" => __("Je wachtwoord komt niet overeen", THEME_DOMAIN),
                    "incorrect_key" => __("Ongeldige sleutel", THEME_DOMAIN),
                    'success' => __("Wachtwoord succesvol reset", THEME_DOMAIN),
                ],
                "change_password" => [
                    "match_old_password" => __("Je wachtwoord mag niet hetzelfde zijn als je oude wachtwoord", THEME_DOMAIN),
                    "password_not_match" => __("Je wachtwoord komt niet overeen.", THEME_DOMAIN),
                    "success" => __("Success, password changed", THEME_DOMAIN)
                ],
                'not_found_user' => __("Gebruiker niet gevonden", THEME_DOMAIN),
                'unfinish_registration' => __("Registratie is incompleet.", THEME_DOMAIN),
                'invalid_credential' => __("Credentials zijn ongeldig.", THEME_DOMAIN),
                'required_credential' => __("Credentials zijn vereist.", THEME_DOMAIN),
                'login_success' => __("Succesvol ingelogd.", THEME_DOMAIN),
                'logout_success' => __("Succesvol uitgelogd.", THEME_DOMAIN),
                'token_not_provided' => __("Token is niet meegegeven.", THEME_DOMAIN),
                'incorrect_password' => __("Verkeerd wachtwoord.", THEME_DOMAIN),
                'login_failed' => __("Login Failed", THEME_DOMAIN),
            ],
            'registration' => [
                'email_required' => __("Email is verplicht.", THEME_DOMAIN),
                'email_wrong' => __("Vul a.u.b. een geldige email in.", THEME_DOMAIN),
                'role_wrong' => __("Selecteer een geldige rol.", THEME_DOMAIN),
                'email_invalid' => __("Email is incorrect.", THEME_DOMAIN),
                'otp_required' => __("OTP is vereist.", THEME_DOMAIN),
                'otp_invalid' => __("OTP is ongeldig.", THEME_DOMAIN),
                'otp_expired' => __("OTP is verlopen.", THEME_DOMAIN),
                'new_otp_success' => __("Nieuwe OTP is succesvol verzonden.", THEME_DOMAIN),
                'new_otp_failed' => __("Nieuwe OTP is niet succesvol verzonden.", THEME_DOMAIN),
                'failed_verify_otp' => __("Het is niet gelukt om de OTP te verifiëren.", THEME_DOMAIN),
                'success_verify_otp' => __("OTP is geverifieerd!.", THEME_DOMAIN),
                'already_registered_user' => __("Gebruiker is al geregistreerd.", THEME_DOMAIN),
                'registration_success' => __("We hebben de OTP code naar je email verzonden.", THEME_DOMAIN),
                'not_registered' => __("Een gebruiker met het opgegeven email adres is nog niet geregistreerd.", THEME_DOMAIN)
            ],
            'profile' => [
                'setup' => [
                    'success' =>  __("Profiel success bewerkt", THEME_DOMAIN),
                    'failed' =>  __("Profiel is niet met success bewerkt", THEME_DOMAIN),
                ],
                'update' => [
                    'success' =>  __("Profel is succesvol bewerkt", THEME_DOMAIN),
                    'photo' => [
                        "success" => __("Profiel foto is succesvol geupdate", THEME_DOMAIN)
                    ],
                    'cv' => [
                        "success" =>  __("CV is succesvol geupdate", THEME_DOMAIN)
                    ],
                    'phone' => [
                        "already_exists" => __("Telefoonnummer is al geregistreerd.", THEME_DOMAIN)
                    ]
                ],
                'delete' => [
                    "success" => __("Succes, wachtwoord is gewijzigd", THEME_DOMAIN),
                    "fail" => __("Account verwijderen mislukt", THEME_DOMAIN),
                    "user_not_found" => __("Gebruiker niet gevonden", THEME_DOMAIN),
                    "password_missmatch" => __("Je wachtwoord komt niet overeen", THEME_DOMAIN)
                ],
                "change_password" => [
                    "incorrect_password" => __("Incorrect wachtwoord", THEME_DOMAIN),
                    "new_password_missmatch" => __("nieuw wachtwoord komt niet overeen", THEME_DOMAIN),
                    "success" => __("Succes, wachtwoord is gewijzigd", THEME_DOMAIN),
                ],
                "account" => [
                    "still_active" => __("Gebruiker nog steeds actief.", THEME_DOMAIN),
                    "reactive_success" => __("Gebruiker reactiveert success.", THEME_DOMAIN),
                    "reactive_failed" => __("Opnieuw activeren van gebruiker mislukt.", THEME_DOMAIN),
                    "delete_success" => __("Gebruiker succesvol verwijderd.", THEME_DOMAIN)
                ]
            ],
            'input' => [
                'invalid_input'   => __("Input is incorrect.", THEME_DOMAIN),
                'failed_to_store' => __("Opslaan niet gelukt.", THEME_DOMAIN),
            ],
            'contact' => [
                'invalid_input' => [
                    'phone_number_required' => __("Telefoonnummer is verplicht.", THEME_DOMAIN),
                    'phone_number_code_required' => __("Landcode is verplicht.", THEME_DOMAIN),
                    'email_required' => __("Email is verplicht.", THEME_DOMAIN),
                    'email_invalid' => __("Email is onjuist.", THEME_DOMAIN),
                    'company_name_required' => __("Bedrijfsnaam is verplicht.", THEME_DOMAIN),
                    'company_sender_name_required' => __("Naam is verplicht.", THEME_DOMAIN),
                    'job_seeker_first_name_required' => __("Voornaam is verplicht.", THEME_DOMAIN),
                    'job_seeker_last_name_required' => __("Achternaam is verplicht.", THEME_DOMAIN),
                    'message_required' => __("Bericht is verplicht.", THEME_DOMAIN),
                ],
                'success'   => __("We hebben je sollicitatie ontvangen en we nemen spoedig contact op!", THEME_DOMAIN)
            ],
            'vacancy' => [
                "get_all" => __("Vacatures gevonden"),
                "not_found" => __("Er zijn geen vacatures beschikbaar op basis van jouw zoekcriteria", THEME_DOMAIN),
                "term" => [
                    'get_term_success' => __("Success get vacancies' filters.", THEME_DOMAIN),
                    'show_term_success' => __("Success get vacancies terms.", THEME_DOMAIN),
                ],
                "create" => [
                    "free" => [
                        "success" => __("We hebben je vacature ontvangen. Het kan tot 2 werkdagen duren voordat je vacature online staat", THEME_DOMAIN),
                        "fail" => __("Er is iets misgegaan bij het aanmaken van een gratis vacature", THEME_DOMAIN),
                    ],
                    "paid" => [
                        "success" => __("We hebben je vacature ontvangen", THEME_DOMAIN),
                        "fail" => __("Er is iets misgegaan bij het aanmaken van een premium vacature", THEME_DOMAIN),
                    ],
                    "notification" => [
                        "submitted" => __("Vacature ingediend!", THEME_DOMAIN),
                        "approved"  => __("De vacature is goedgekeurd!", THEME_DOMAIN),
                        "approved_body" => __("Congratulations! your post has been published successfully.", THEME_DOMAIN)
                    ]
                ],
                "update" => [
                    "free" => [
                        "success" => __("Gratis vacature is geupdate", THEME_DOMAIN),
                        "fail" => __("Het is niet gelukt om een gratis vacature aan te maken", THEME_DOMAIN),
                        "on_process" => __("Kan vacature niet updaten terwijl vacature nog in behandeling is.", THEME_DOMAIN)
                    ],
                    "paid" => [
                        "success" => __("Premium vacature is geupdate", THEME_DOMAIN),
                        "fail" => __("Het is niet gelukt om een premium vacature aan te maken", THEME_DOMAIN),

                    ]
                ],
                "trash" => [
                    "success" => __("Succes, vacature is al verwijderd", THEME_DOMAIN),
                    "fail" => __("Er is iets misgegaan. Neem a.u.b. contact op met onze technische afdeling", THEME_DOMAIN),
                    "not_authorized" => __("Je hebt geen rechten om deze vacature te verwijderen", THEME_DOMAIN)
                ]
            ],
            'candidate' => [
                "profile" => [
                    "get_success" => __("Kandidaat profiel succesvol ingeladen.", THEME_DOMAIN),
                    "delete_cv_success" => __("Kandidaat CV is verwijderd.", THEME_DOMAIN),
                    "delete_cv_failed" => __("Systeemfout. Het is niet gelukt om de CV van de kandidaat te verwijderen.", THEME_DOMAIN),
                    "delete_cv_not_found" => __("Kandidaat heeft geen CV geupload.", THEME_DOMAIN),
                ],
                "apply_vacancy" => [
                    "apply_success" => __("Succesvol op deze vacature gesolliciteerd.", THEME_DOMAIN),
                    "apply_failed" => __("Het is niet gelukt om op deze vacature te solliciteren.", THEME_DOMAIN),
                    "expired_job" => __("Solliciteren is niet mogelijk. De vacature is verlopen", THEME_DOMAIN),
                    "already_apply" => __("Je hebt al op deze functie gesolliciteerd.", THEME_DOMAIN),
                    "cv_filetype_not_support" => __("bestandstype wordt niet ondersteund.", THEME_DOMAIN)
                ],
                "favorite" => [
                    "vacancy_not_found" => __("Vacature niet gevonden.", THEME_DOMAIN),
                    "add_success" => __("Succesvol toegevoegd als favoriet", THEME_DOMAIN),
                    "add_failed" => __("Het is niet gelukt om deze vacature toe te voegen als favoriet", THEME_DOMAIN),
                    "already_exists" => __("Deze vacature zit al in je favorieten", THEME_DOMAIN),
                    "empty" => __("Je hebt geen favoriete vacatures", THEME_DOMAIN),
                    "get_success" => __("Succesvol favoriete vacatures ingeladen", THEME_DOMAIN),
                    "delete_success" => __("Succesvol favoriete vacatures verwijderd", THEME_DOMAIN),
                    "delete_failed" => __("Het is niet gelukt om deze vacature uit je favorieten te verwijderen", THEME_DOMAIN),
                    "apply_failed" => __("Solliciteren is niet gelukt.", THEME_DOMAIN),
                    "expired_job" => __("Niet gelukt, deze vacature is al verlopen", THEME_DOMAIN),
                ],
                "get" => [
                    "success" => __("Data van kandidaat is succesvol ingeladen", THEME_DOMAIN),
                    "not_found" => __("Kandidaat niet gevonden", THEME_DOMAIN)
                ],
                "change_email_request" => [
                    "success" => __("Succes, verzoek om email te wijzigen is naar je email verzonden", THEME_DOMAIN),
                    "not_found" => __("Kandidaat niet gevonden", THEME_DOMAIN),
                    "user_not_found" => __("Gebruiker niet gevonden", THEME_DOMAIN),
                    "email_exist" => __("Email bestaat al", THEME_DOMAIN),
                    "invalid" => __("Ongeldige gebruiker data.", THEME_DOMAIN),
                ],
                "change_email" => [
                    "success" => __("Succes, email is aangepast", THEME_DOMAIN),
                    "fail" => __("Er is iets misgegaan. Neem contact op met onze technische dienst", THEME_DOMAIN),
                    "already_used" => __("Token is al gebruikt", THEME_DOMAIN),
                ]
            ],
            'company' => [
                "profile" => [
                    "setup_success" => __("Profiel succesvol aangemaakt", THEME_DOMAIN),
                    "setup_failed" => __("Profiel is niet succesvol aangemaakt", THEME_DOMAIN),
                    "setup_invalid" => __("Ongeldige gebruiker invoer.", THEME_DOMAIN),
                    "update_success" => __("Bedrijfsprofiel is succesvol geupdate", THEME_DOMAIN),
                    "update_failed" => __("Failed update company profile", THEME_DOMAIN),
                    "update_detail_success" => __("Success update company profile", THEME_DOMAIN),
                    "get_image_success" => __("Bedrijfsfoto is succesvol ingeladen.", THEME_DOMAIN),
                    "get_success" => __("Bedrijfsprofiel is succesvol geupdate.", THEME_DOMAIN),
                    "get_credit" => [
                        'success' => __("succes, bedrijfskrediet toegevoegd.", THEME_DOMAIN),
                    ],
                    'insufficient_credit' => __("Uw krediet is onvoldoende.", THEME_DOMAIN)
                ],
                'vacancy' => [
                    'repost_success' => __("Vacature succesvol opnieuw geplaatst.", THEME_DOMAIN),
                    'repost_can_not' => __("U kunt een vacature niet opnieuw plaatsen.", THEME_DOMAIN),
                    'repost_no_permission' => __("Gebruiker heeft geen toestemming om deze vacature opnieuw te plaatsen.", THEME_DOMAIN)
                ]
            ],
            'package' => [
                "package" => [
                    "get_success" => __("Alle pakketten succesvol ingeladen.", THEME_DOMAIN),
                    "show_success" => __("Pakket succesvol ingeladen.", THEME_DOMAIN),
                    "show_not_found" => __("Pakket met deze url slug is niet gevonden.", THEME_DOMAIN),
                    "something_error" => __("er is een fout opgetreden bij het aanmaken van de betaling", THEME_DOMAIN),
                    "trx_not_found" => __("Transactie niet gevonden", THEME_DOMAIN),
                    "success_grant" => __("Succes met het verlenen van krediet", THEME_DOMAIN),
                    "payment_fail" => __("betaling mislukt", THEME_DOMAIN),
                    "success_get_receipt" => __("Success ontvang ontvangstbewijs", THEME_DOMAIN),
                ],
                "payment" => [
                    "trigger_payment_fail" => __("betalingsfout geactiveerd", THEME_DOMAIN),
                    "payment_fail" => __("Betaling mislukt", THEME_DOMAIN),
                    "granting_credit_success" => __("Succes met het verlenen van krediet", THEME_DOMAIN),
                    "granting_credit_already" => __("Deze transactie verleent al krediet", THEME_DOMAIN),
                ],
                "webhook" => [
                    "event_not_registered" => __("evenement is niet geregistreerd", THEME_DOMAIN)
                ]
            ],
            'system' => [
                'overall_failed' => __('Systeemfout.', THEME_DOMAIN),
            ],
            'option' => [
                "company" => [
                    "employees_total" => [
                        "get_success" => __('Werknemersaantal succesvol ingeladen.', THEME_DOMAIN),
                    ]
                ]
            ],
            "package" => [
                "purchase" => [
                    'success' => __("Betaallink is succesvol aangemaakt", THEME_DOMAIN),
                    "user_not_match" => __("Gebruiker niet gevonden", THEME_DOMAIN),
                    "trans_not_found" => __("Transactie niet gevonden", THEME_DOMAIN)
                ],
                "create_payment" => [
                    "error" => __("er is een fout opgetreden bij het aanmaken van de betaling", THEME_DOMAIN)
                ]
            ],
            'sitemap' => [
                "get_success" => __("Succes, ontvang alle sitemaps", THEME_DOMAIN),
                "get_failed"  => __("Kan niet alle sitemaps ophalen", THEME_DOMAIN),
                "show_companies_success" => __("Succes krijg bedrijven sitemap", THEME_DOMAIN),
                "show_companies_failed" => __("Kan de sitemap van bedrijven niet ophalen", THEME_DOMAIN)
            ],
            'job_alert' => [
                "email_alert_success" => __("Bedankt voor het versturen van de job alert aanmaken!", THEME_DOMAIN),
                "email_alert_failed" => __("Kan zich niet aanmelden voor jobwaarschuwing of e-mail verzenden.", THEME_DOMAIN),
            ],
            'other' => [
                'invalid_post' => __("ongeldig bericht", THEME_DOMAIN),
            ],
            'notification' => [
                'get_success' => __("Succes ontvang meldingen!", THEME_DOMAIN),
                'delete_success' => __("Melding verwijderd!", THEME_DOMAIN),
                'delete_failed' => __("Kennisgeving niet verwijderd!", THEME_DOMAIN),
                'read_all_success' => __("Succes met het lezen van alle meldingen!", THEME_DOMAIN),
                'read_all_failed' => __("Niet alle meldingen gelezen!", THEME_DOMAIN),
            ],
            'coupon' => [
                'get_success'   => __("Success get coupons!", THEME_DOMAIN),
                'get_failed'    => __("Failed get coupons!", THEME_DOMAIN),
                'not_found'     => __("Coupon not found!", THEME_DOMAIN),
                'expired'       => __("Coupon already expired!", THEME_DOMAIN),
                'not_available' => __("Coupon is no longer available!", THEME_DOMAIN),
                'run_out'       => __("Coupons have run out!", THEME_DOMAIN),
                'read_single_success' => __("Bericht is al gelezen", THEME_DOMAIN),
                'read_not_authorize' => __("Bericht is al gelezen", THEME_DOMAIN),
                'read_already' => __("Bericht is al gelezen", THEME_DOMAIN),
                'read_single_failed' => __("Er is iets misgegaan bij het lezen van de notificatie", THEME_DOMAIN),
            ]
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
