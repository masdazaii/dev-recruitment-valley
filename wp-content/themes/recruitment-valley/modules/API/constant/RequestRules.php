<?php

namespace Constant;

class RequestRules
{
    public $rules;
    public $sanitizeRules;

    public function __construct()
    {
        $this->rules = [
            'register' => [
                "email" => ["required", "email"],
                "password" => ["required"],
                "accountType" => ["required", "in:candidate,company"]
            ],
            'login' => [
                "email" => ["required", "email"],
                "password" => ["required"]
            ],
            'companySetupProfile' => [
                "companyName" => ["required"], // Recruiter
                "sector.*" => ["exists:term/sector/term_id/single"], // 1
                // "sector.*" => [], // 1
                "employeesTotal" => ["numeric"], // 1
                "phoneNumber" => ["required"], // 8967321123
                "phoneNumberCode" => ["required"], // 62
                // "email" => ["required", "email"], // company@email.com
                "website" => [], //                  https://website.com
                "kvkNumber" => [], //                129380
                "btwNumber" => [], //                129380
                "facebook" => [], //                 https://facebook.com
                "instagram" => [], //                https://instagram.com
                "linkedin" => [], //                 https://linkedin.com
                "twitter" => [], //                  https://twitter.com
                "country" => ["required"], //                  Netherland
                "street" => ["required"], //               jl.kemerdekaan
                "city" => ["required"], //                 Amsterdam
                "postCode" => ["required"], //                 91823E
                "shortDescription" => ["required"], //                 This is my company
                "secondaryEmploymentConditions" => [], //                <div>WYSIWYG</div>
                "companyVideo" => [], //                  https://youtube.com/watch?v=asdfasdf
                "gallery" => []
            ],
            'companyUpdateDetail' => [
                "companyName" => ["required"],
                "sector.*" => ["required"],
                "phoneNumberCode" => ["required"],
                "phoneNumber" => ["required"],
                "website" => ["required", "url"],
                "employees" => ["requred"],
                "kvkNumber" => [],
                "btwNumber" => [],
            ],
            'companyUpdateInformation' => [
                "shortDescription" => ["required"],
                "videoUrl" => [],
                "secondaryEmploymentConditions" => [],
                // "gallery" => []
            ],
            'candidateUpdateProfile' => [
                "firstName" => ["required"],
                "dateOfBirth" => ["required"],
                "phoneNumber" => ["required"],
                "phoneNumberCode" => ["required"],
                "country" => ["required"],
                "city" => ["required"],
                "linkedinPage" => ["required"],
            ],
            'candidateChangeEmail' => [
                'newEmail' => ["required", "email"] // Must add rules exists to check if email already used!
            ],
            'candidateChangepassword' => [
                'newPassword' => ["required"],
                'repeatNewPassword' => ["required"],
                'key' => ["required"]
            ],
            'addFavorite' => [
                /**
                 * exists parameter : {{ 1 }}/{{ 2 }}/{{ 3 }}/{{ 4 }},{{5}}
                 * not_exists parameter : {{ 1 }}/{{ 2 }}/{{ 3 }}/{{ 4 }},{{5}}
                 * 1 : value must be one of this : "user" / "post"
                 * 2 : value must be one of this : "meta" / "acf" / post-type slug
                 * 3 : value must be : meta_key / acf field name / field to compare
                 * 4 : value is type of value in database, must be one of :
                 *       "single" = anything that result of the query is a single value, like post_id / post_name
                 *       "array" = anything that result of the query is an array, like user meta with meta_key "favorite_vacancy"
                 * 5 : Selector to get spesific meta, example user_id to get user meta, this is optional
                 * */
                'vacancyId' => ["required", "exists:post/vacancy/post_id/single"]
            ],
            'paymentPackage' => [
                'slug' => ["required", "exists:post/package/post_name/single"]
            ],
            'applyVacancy' => [
                'phoneNumberCode' => ["required"],
                'phoneNumber' => ["required"],
                'coverLetter' => [],
                'vacancy' => ["required", "exists:post/vacancy/post_id/single"],
            ],
            'test' => [
                'test.*.name' => ["required"]
            ]
        ];

        $this->sanitizeRules = [
            'register' => [
                "email" => "email",
                "password" => null,
                "accountType" => "text"
            ],
            /** When using rule other than these 2. Please add following sanitize rule in Validator.php. */
            "example" => [
                "email" => "email",
                "string" => "text", // Will use sanitize_text_field
            ],
            "applyVacancy" => [
                'phoneNumberCode' => "text",
                'phoneNumber' => "text",
                'coverLetter' => "textarea",
                'vacancy' => "text",
            ],
            "candidateChangeEmail" => [
                'newEmail' => "email"
            ],
            'companySetupProfile' => [
                "companyName" => "text",
                "sector.*" => "text",
                "employeesTotal" => "text",
                "phoneNumber" => "text",
                "phoneNumberCode" => "text",
                "email" => "email",
                "website" => "text",
                "kvkNumber" => "text",
                "btwNumber" => "text",
                "facebook" => "text",
                "instagram" => "text",
                "linkedin" => "text",
                "twitter" => "text",
                "country" => "text",
                "street" => "text",
                "city" => "text",
                "postCode" => "text",
                "shortDescription" => "textarea",
                "secondaryEmploymentConditions" => "textarea",
                "companyVideo" => "text",
                "gallery" => ""
            ],
            'companyUpdateInformation' => [
                "shortDescription" => "textarea",
                "videoUrl" => "",
                "secondaryEmploymentConditions" => "textarea",
                "gallery" => ""
            ],
        ];
    }

    public function get(String $rule)
    {
        if ($rule && $rule !== "" && array_key_exists($rule, $this->rules)) {
            return $this->rules[$rule];
        } else {
            return false;
        }
    }

    public function getSanitizeRule(String $rule)
    {
        if ($rule && $rule !== "" && array_key_exists($rule, $this->sanitizeRules)) {
            return $this->sanitizeRules[$rule];
        } else {
            return [];
        }
    }
}
