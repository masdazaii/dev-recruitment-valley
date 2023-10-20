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
            'forgotPassword' => [
                "email" => ["required", "email"],
            ],
            'companySetupProfile' => [
                "companyName" => ["required"],
                "sector.*" => ["exists:term/sector/term_id/single"],
                // "sector.*" => [],
                "employeesTotal" => [],
                "phoneNumber" => [],
                "phoneNumberCode" => [],
                // "email" => ["required", "email"],
                "website" => [],
                "kvkNumber" => [],
                "btwNumber" => [],
                "facebook" => [],
                "instagram" => [],
                "linkedin" => [],
                "twitter" => [],
                "sector" => ["required"],
                "country" => ["required"],
                "street" => ["required"],
                "city" => ["required"],
                "postCode" => ["required"],
                "shortDescription" => ["required"],
                "secondaryEmploymentConditions" => [],
                "companyVideo" => ["max_file_size:10240000"],
                "gallery" => [],
                "image" => [],
                "longitude" => [],
                "latitude" => [],
                "countryCode" => []
            ],
            'companyUpdateDetail' => [
                "companyName" => ["required"],
                "sector.*" => [],
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
            'candidateSetupProfile' => [
                "firstName" => ["required"],
                "lastName" => ["required"],
                "dateOfBirth" => [], // Add : Date
                "phoneNumber" => [], // Add : Regex
                "phoneNumberCode" => [],
                "country" => ["required"],
                "city" => ["required"],
                "linkedinPage" => [], // Add : Url
            ],
            'candidateUpdateProfile' => [
                "firstName" => ["required"],
                "dateOfBirth" => [],
                "phoneNumber" => [],
                "phoneNumberCode" => [],
                "country" => ["required"],
                "city" => ["required"],
                "linkedinPage" => [],
            ],
            'candidateChangeEmail' => [
                'newEmail' => ["required", "email"] // Must add rules exists to check if email already used!
            ],
            'candidateChangepassword' => [
                'newPassword' => ["required"],
                'repeatNewPassword' => ["required"],
                'key' => ["required"]
            ],
            'vacancyCreateFree' => [
                "name" => ['required'],
                "description" => ["required"],
                "city" => ["required"],
                "placementAddress" => ["required"],
                "salaryStart" => ["numeric"],
                "salaryEnd" => ["numeric"],
                "sector.*" => ["required", "numeric"],
                "role.*" => ["required", "numeric"],
                "workingHours.*" => ["required", "numeric"],
                "location.*" => ["required", "numeric"],
                "education.*" => ["required", "numeric"],
                "employmentType.*" => ["required", "numeric"],
                "externalUrl" => ["url"],
                "experiences.*" => ["numeric"],
                "country" => [],
                "countryCode" => []
            ],
            'vacancyCreatePaid' => [
                "name" => ['required'],
                "country" => ["required"],
                "description" => ["required"],
                "city" => ['required'],
                "placementAddress" => ["required"],
                "salaryStart" => ["numeric"],
                "salaryEnd" => ["numeric"],
                "sector.*" => ["required", "numeric"],
                "role.*" => ["required", "numeric"],
                "workingHours.*" => ["required", "numeric"],
                "location.*" => ["required", "numeric"],
                "education.*" => ["required", "numeric"],
                "employmentType.*" => ["required", "numeric"],
                "externalUrl" => ["url"],
                "applicationProcedureTitle" => [],
                "applicationProcedureText" => [],
                "applicationProcedureSteps.*" => [],
                "video" => ["max_file_size:10240000"],
                "facebook" => ["url"],
                "linkedin" => ["url"],
                "instagram" => ["url"],
                "twitter" => ["url"],
                "review" => [],
                "experiences.*" => ["numeric"], // Added Line
                "countryCode" => []
            ],
            'vacancyUpdateFree' => [
                "description" => ["required"],
                "country" => ["required"],
                "city" => ["required"],
                "placementAddress" => ["required"],
                "salaryStart" => ["numeric"],
                "salaryEnd" => ["numeric"],
                "sector.*" => ["required", "numeric", "exists:term/sector/term_id/single"],
                "role.*" => ["required", "numeric", "exists:term/role/term_id/single"],
                "workingHours.*" => ["required", "numeric", "exists:term/working-hours/term_id/single"],
                "location.*" => ["required", "numeric", "exists:term/location/term_id/single"],
                "education.*" => ["required", "numeric", "exists:term/education/term_id/single"],
                "employmentType.*" => ["required", "numeric", "exists:term/type/term_id/single"],
                "externalUrl" => ["url"],
                "experiences.*" => ["numeric", "exists:term/experiences/term_id/single"], // Added Line
                "countryCode" => []
            ],
            'vacancyUpdatePaid' => [
                "city"              => ["required"],
                "placementAddress"  => ["required"],
                "description"       => ["required"],
                "terms"             => ["required"],
                "salaryStart"       => ["numeric"],
                "salaryEnd"         => ["numeric"],
                "externalUrl"       => ["url"],
                "video"             => ["max_file_size:10240000"],
                "facebook"          => ["url"],
                "linkedin"          => ["url"],
                "instagram"         => ["url"],
                "twitter"           => ["url"],
                "sector.*"          => ["required", "numeric", "exists:term/sector/term_id/single"],
                "role.*"            => ["required", "numeric", "exists:term/role/term_id/single"],
                "workingHours.*"    => ["required", "numeric", "exists:term/working-hours/term_id/single"],
                "location.*"        => ["required", "numeric", "exists:term/location/term_id/single"],
                "education.*"       => ["required", "numeric", "exists:term/education/term_id/single"],
                "employmentType.*"  => ["required", "numeric", "exists:term/type/term_id/single"],
                "experiences.*"     => ["required", "numeric", "exists:term/experiences/term_id/single"], // Added Line
                "applicationProcedureSteps" => [],
                "applicationProcedureTitle" => [],
                "applicationProcedureText"  => [],
                "review"                    => [],
                "country"                   => ["required"],
                "countryCode"               => []
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
            'userChangeEmail' => [
                'newEmail' => ["required", "email"] // Must add rules exists to check if email already used!
            ],
            'vacancyApplicants' => [
                'vacancy' => ["required", "exists:post/vacancy/post_id/single"],
            ],
            'vacancySingleApplicant' => [
                'application' => ["required", "exists:post/applicants/post_id/single"],
            ],
            // This one is admin submission
            'vacancyApproval' => [
                'vacancyID' => ["required", "exists:post/vacancy/post_id/single"],
                'approval'  => ["required", "in:rejected,approved"]
            ],
            'vacancyChangeRole' => [
                'vacancyID'     => ["required", "exists:post/vacancy/post_id/single"],
                'inputRole.*'   => ["exists:term/role/term_id/single"],
                'nonce'         => ["required"]
            ],
            'vacancyOptionValue'    => [
                'company'           => ['required', 'exists:user/user/ID'],
            ],
            'test' => [
                'test.*.name' => ["required", "mime:jpg,jpeg,png,bmp,gif,svg,webp"]
            ]
        ];

        $this->sanitizeRules = [
            'register' => [
                "email" => "email",
                "password" => null,
                "accountType" => "text"
            ],
            'login' => [
                "email" => "email",
                "password" => ""
            ],
            'forgotPassword' => [
                "email" => "email",
            ],
            'companySetupProfile' => [
                "companyName" => "text",
                "sector.*" => "text",
                "employeesTotal" => "text",
                "phoneNumber" => "text",
                "phoneNumberCode" => "text",
                // "email" => "email",
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
                "secondaryEmploymentConditions" => "",
                "companyVideo" => "text",
                "gallery" => "",
                "image" => "",
                "longitude" => "",
                "latitude" => "",
                "countryCode" => "text"
            ],
            'companyUpdateDetail' => [
                "companyName" => "text",
                "sector.*" => "text",
                "phoneNumberCode" => "text",
                "phoneNumber" => "text",
                "website" => "",
                "employees" => "text",
                "kvkNumber" => "text",
                "btwNumber" => "text",
            ],
            'companyUpdateInformation' => [
                "shortDescription" => "textarea",
                "videoUrl" => "",
                "secondaryEmploymentConditions" => "",
                "gallery" => ""
            ],
            "applyVacancy" => [
                'phoneNumberCode' => "text",
                'phoneNumber' => "text",
                'coverLetter' => "textarea",
                'vacancy' => "text",
            ],
            'candidateUpdateProfile' => [
                "firstName" => "text",
                "dateOfBirth" => "text",
                "phoneNumber" => "text",
                "phoneNumberCode" => "text",
                "country" => "text",
                "city" => "text",
                "linkedinPage" => "",
            ],
            "candidateChangeEmail" => [
                'newEmail' => "email"
            ],
            'candidateChangepassword' => [
                'newPassword' => "",
                'repeatNewPassword' => "",
                'key' => ""
            ],
            'vacancyCreateFree' => [
                "name" => "text",
                "description" => "ksespost",
                "city" => "text",
                "placementAddress" => "text",
                "salaryStart" => "text",
                "salaryEnd" => "text",
                "sector.*" => "text",
                "role.*" => "text",
                "workingHours.*" => "text",
                "location.*" => "text",
                "education.*" => "text",
                "employmentType.*" => "text",
                "externalUrl" => "",
                "experience.*" => "text",
                "country" => "text",
                "countryCode" => "text"
            ],
            'vacancyCreatePaid' => [
                "name" => "text",
                "description" => "ksespost",
                "city" => "text",
                "placementAddress" => "text",
                "terms" => "ksespost",
                "salaryStart" => "text",
                "salaryEnd" => "text",
                "sector.*" => "text",
                "role.*" => "text",
                "workingHours.*" => "text",
                "location.*" => "text",
                "education.*" => "text",
                "employmentType.*" => "text",
                "externalUrl" => "text",
                "applicationProcedureTitle" => "text",
                "applicationProcedureText" => "text",
                "applicationProcedureSteps.*" => "text",
                "video" => "",
                "facebook" => "",
                "linkedin" => "",
                "instagram" => "",
                "twitter" => "",
                "review" => "",
                "experience.*" => "text",
                "countryCode" => "text"
            ],
            'vacancyUpdateFree' => [
                "description"       => "ksespost",
                "city"              => "text",
                "placementAddress"  => "text",
                "salaryStart"       => "text",
                "salaryEnd"         => "text",
                "sector.*"          => "text",
                "role.*"            => "text",
                "workingHours.*"    => "text",
                "location.*"        => "text",
                "education.*"       => "text",
                "employmentType.*"  => "text",
                "externalUrl"       => "",
                "experience.*"      => "text",
                "countryCode"       => "text"
            ],
            'vacancyUpdatePaid' => [
                "city"              => "text",
                "placementAddress"  => "text",
                "description"       => "ksespost",
                "terms"             => "ksespost",
                "salaryStart"       => "text",
                "salaryEnd"         => "text",
                "externalUrl"       => "",
                "video"             => "",
                "facebook"          => "",
                "linkedin"          => "",
                "instagram"         => "",
                "twitter"           => "",
                "sector.*"          => "text",
                "role.*"            => "text",
                "workingHours.*"    => "text",
                "location.*"        => "text",
                "education.*"       => "text",
                "employmentType.*"  => "text",
                "experiences.*"     => "text", // Added Line
                "applicationProcedureSteps" => "",
                "applicationProcedureTitle" => "",
                "applicationProcedureText"  => "",
                "review."                    => "",
                "galleryJob"                => "",
                "galleryCompany"            => "",
                "country"                   => "text",
                "countryCode"               => "text"
            ],
            "userChangeEmail" => [
                'newEmail' => "email"
            ],
            // This is admin submission
            'vacancyApproval' => [
                'vacancyID' => "text",
                'approval'  => "text",
                'nonce'     => "text"
            ],
            'vacancyChangeRole' => [
                'vacancyID'     => "text",
                'inputRole.*'   => "text",
                'nonce'         => "text",
            ],
            'vacancyOptionValue'    => [
                'company'           => "text",
            ],
            /** When using rule other than these 2. Please add following sanitize rule in Validator.php. */
            "example" => [
                "email" => "email",
                "string" => "text", // Will use sanitize_text_field
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
