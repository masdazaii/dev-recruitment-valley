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
                'invalid_token' => __('Ongeldige token', THEME_DOMAIN),
                'expired' => __('Verlopen', THEME_DOMAIN),
                'generate_token_success' => __('Token succes aangemaakt', THEME_DOMAIN),
                'generate_token_error' => __('Er iets misgegaan bij het aanmaken van een token', THEME_DOMAIN),
                'user_deleted' => __('User was not active', THEME_DOMAIN),
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
                    'success' =>  __("success update profile", THEME_DOMAIN),
                    'photo' => [
                        "success" => __("Profel foto is succesvol bewerkt", THEME_DOMAIN)
                    ],
                    'cv' => [
                        "success" =>  __("CV updated successfully", THEME_DOMAIN)
                    ],
                    'phone' => [
                        "already_exists" => __("Phone number already used.", THEME_DOMAIN)
                    ]
                ],
                'delete' => [
                    "success" => __("Success delete account", THEME_DOMAIN),
                    "fail" => __("Fail delete account", THEME_DOMAIN),
                    "user_not_found" => __("User not found", THEME_DOMAIN),
                    "password_missmatch" => __("Password missmatch", THEME_DOMAIN)
                ]
            ],
            'input' => [
                'invalid_input'   => __("Input is invalid.", THEME_DOMAIN),
                'failed_to_store' => __("Failed to store data.", THEME_DOMAIN),
            ],
            'contact' => [
                'invalid_input' => [
                    'phone_number_required' => __("Phone number is required.", THEME_DOMAIN),
                    'phone_number_code_required' => __("Phone number code is required.", THEME_DOMAIN),
                    'email_required' => __("Email is required.", THEME_DOMAIN),
                    'email_invalid' => __("Email is invalid.", THEME_DOMAIN),
                    'company_name_required' => __("Company name is required.", THEME_DOMAIN),
                    'company_sender_name_required' => __("Name is required.", THEME_DOMAIN),
                    'job_seeker_first_name_required' => __("First name is required.", THEME_DOMAIN),
                    'job_seeker_last_name_required' => __("Last name is required.", THEME_DOMAIN),
                    'message_required' => __("Message is required.", THEME_DOMAIN),
                ],
                'success'   => __("We have received your submission. We will reach back to you soon!", THEME_DOMAIN)
            ],
            'vacancy' => [
                "get_all" => __("Success get vacancies"),
                "not_found" => __("there is no vancancy found base on your criteria", THEME_DOMAIN),
                "term" => [
                    'get_term_success' => __("Success get vacancies' filters.", THEME_DOMAIN),
                    'show_term_success' => __("Success get vacancies terms.", THEME_DOMAIN),
                ],
                "create" => [
                    "free" => [
                        "success" => __("We have received your vacancy submission. We will review your vacancy in up to 2 business days", THEME_DOMAIN),
                        "fail" => __("Error creating free job", THEME_DOMAIN),
                    ],
                    "paid" => [
                        "success" => __("We have received your vacancy submission", THEME_DOMAIN),
                        "fail" => __("Error creating paid job", THEME_DOMAIN),

                    ]
                ],
                "update" => [
                    "free" => [
                        "success" => __("Update free job successfully", THEME_DOMAIN),
                        "fail" => __("Error creating free job", THEME_DOMAIN),
                    ],
                    "paid" => [
                        "success" => __("Update paid job successfully", THEME_DOMAIN),
                        "fail" => __("Error creating paid job", THEME_DOMAIN),

                    ]
                ],
                "trash" => [
                    "success" => __("Success, job already deleted", THEME_DOMAIN),
                    "fail" => __("There is an error, please contact administrator", THEME_DOMAIN),
                    "not_authorized" => __("You dont have permission to delete this job", THEME_DOMAIN)
                ]
            ],
            'candidate' => [
                "profile" => [
                    "get_success" => __("Success get candidate profile.", THEME_DOMAIN),
                    "delete_cv_success" => __("Candidate CV deleted.", THEME_DOMAIN),
                    "delete_cv_failed" => __("System error, failed to delete candidate CV.", THEME_DOMAIN),
                    "delete_cv_not_found" => __("Candidate didn't have CV uploaded.", THEME_DOMAIN),
                ],
                "apply_vacancy" => [
                    "apply_success" => __("Success apply this job.", THEME_DOMAIN),
                    "apply_failed" => __("Failed apply this job.", THEME_DOMAIN),
                    "expired_job" => __("Cannot apply, the job was expired", THEME_DOMAIN),
                    "already_apply" => __("You already apply to this job.", THEME_DOMAIN),
                    "cv_filetype_not_support" => __("Filetype not supported.", THEME_DOMAIN)
                ],
                "favorite" => [
                    "vacancy_not_found" => __("Vacancy not found.", THEME_DOMAIN),
                    "add_success" => __("Success add favorite jobs", THEME_DOMAIN),
                    "add_failed" => __("Failed add favorite jobs", THEME_DOMAIN),
                    "already_exists" => __("Already in your favorites", THEME_DOMAIN),
                    "empty" => __("Your have no favorites jobs", THEME_DOMAIN),
                    "get_success" => __("Success get favorite jobs", THEME_DOMAIN),
                    "delete_success" => __("Success delete favorite job", THEME_DOMAIN),
                    "delete_failed" => __("Failed delete favorite job", THEME_DOMAIN),
                    "apply_failed" => __("Failed apply this job.", THEME_DOMAIN),
                    "expired_job" => __("Failed, the job already expired", THEME_DOMAIN),
                ],
                "get" => [
                    "success" => __("success getting candidate data", THEME_DOMAIN),
                    "not_found" => __("candidate not found", THEME_DOMAIN)
                ],
                "change_email_request" => [
                    "success" => __("Success, change email request already sent to your email", THEME_DOMAIN),
                    "not_found" => __("candidate not found", THEME_DOMAIN),
                    "user_not_found" => __("User not found", THEME_DOMAIN),
                    "email_exist" => __("Email already exist", THEME_DOMAIN),
                    "invalid" => __("Invalid user input.", THEME_DOMAIN),
                ],
                "change_email" => [
                    "success" => __("Success, email changed successfully", THEME_DOMAIN),
                    "fail" => __("Error, something went wrong please contact administrartor", THEME_DOMAIN),
                    "already_used" => __("Token already used", THEME_DOMAIN),
                ]
            ],
            'company' => [
                "profile" => [
                    "setup_success" => __("Success setting up your profile", THEME_DOMAIN),
                    "setup_failed" => __("Failed setting up your profile", THEME_DOMAIN),
                    "setup_invalid" => __("Invalid user input.", THEME_DOMAIN),
                    "update_success" => __("Success update company profile", THEME_DOMAIN),
                    "update_failed" => __("Failed update company profile", THEME_DOMAIN),
                    "update_detail_success" => __("Success update company profile", THEME_DOMAIN),
                    "get_image_success" => __("Success get company image.", THEME_DOMAIN),
                    "get_success" => __("Success get company profile.", THEME_DOMAIN),
                    "get_credit" => [
                        'success' => __("success get company credit.", THEME_DOMAIN),
                    ],
                    'insufficient_credit' => __("Your credit is insufficient.", THEME_DOMAIN)
                ]
            ],
            'package' => [
                "package" => [
                    "get_success" => __("Success get all package.", THEME_DOMAIN),
                    "show_success" => __("Success get package.", THEME_DOMAIN),
                    "show_not_found" => __("Package with given slug not found.", THEME_DOMAIN),
                ]
            ],
            'system' => [
                'overall_failed' => __('System Error.', THEME_DOMAIN),
            ],
            'option' => [
                "company" => [
                    "employees_total" => [
                        "get_success" => __('Success get employees total option.', THEME_DOMAIN),
                    ]
                ]
            ],
            "package" => [
                "purchase" => [
                    'success' => __("Success creating payment url", THEME_DOMAIN),
                    "user_not_match" => __("user not match", THEME_DOMAIN),
                    "trans_not_found" => __("Transaction not found", THEME_DOMAIN)
                ],
                "create_payment" => [
                    "error" => __("something error when creating payment", THEME_DOMAIN)
                ]
            ],
            'sitemap' => [
                "get_success" => __("Success get all sitemaps", THEME_DOMAIN),
                "get_failed"  => __("Failed get all sitemaps", THEME_DOMAIN),
                "show_companies_success" => __("Success get companies sitemap", THEME_DOMAIN),
                "show_companies_failed" => __("Failed get companies sitemap", THEME_DOMAIN)
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
