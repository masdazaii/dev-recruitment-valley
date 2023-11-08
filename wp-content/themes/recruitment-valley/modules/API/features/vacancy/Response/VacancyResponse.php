<?php

namespace Vacancy;

use Candidate\Profile\Candidate;
use DateTime;
use DateTimeImmutable;
use Model\Company;
use WP_Post;
use Helper\StringHelper;
use Helper\DateHelper;
use Model\Option;
use Constant\LanguageConstant;

class VacancyResponse
{
    public $vacancyCollection;

    private $userPayload;

    public function setCollection($vacancyCollection)
    {
        $this->vacancyCollection = $vacancyCollection;
    }

    public function setUserPayload($payload)
    {
        $this->userPayload = $payload;
    }

    public function format()
    {
        $defaultImageVacancyImport = get_field("import_api_default_image", "options");
        $option = new Option;
        $formattedResponse = array_map(function (WP_Post $vacancy) use ($defaultImageVacancyImport, $option) {
            $vacancyModel = new Vacancy($vacancy->ID);
            $company = new Company($vacancy->post_author);
            $vacancyTaxonomy = $vacancyModel->getTaxonomy(true);

            $thumbnail = null;
            if ($vacancyModel->checkImported()) {
                $thumbnail = $option->getDefaultImage('object');
            } else {
                if ($vacancyModel->checkIsForAnotherCompany()) {
                    if ($vacancyModel->checkUseExistingCompany()) {
                        $selectedCompany = $vacancyModel->getSelectedCompany();
                        if ($selectedCompany) {
                            $company = new Company($selectedCompany);
                            $thumbnail = $company->getThumbnail('object');
                        }
                    } else {
                        $thumbnail = $vacancyModel->getCustomCompanyLogo('object');
                    }
                } else {
                    $thumbnail = $company->getThumbnail('object');
                }
            }

            return [
                "id" => $vacancy->ID,
                "slug" => $vacancy->post_name,
                "name" => $vacancy->post_title,
                "country" => $vacancyModel->getCountry(),
                "countryCode" => $vacancyModel->getCountryCode(), // Added Line
                "city" => $vacancyModel->getCity(),
                "placementAddress" => $vacancyModel->getPlacementAddress(),
                "education" => $vacancyTaxonomy["education"]  ?? null,
                "employmentType" => $vacancyTaxonomy["type"] ?? null,
                "location" => $vacancyTaxonomy["location"] ?? null,
                "role" => $vacancyTaxonomy["role"] ?? null,
                "sector" => $vacancyTaxonomy["sector"] ?? null,
                "hoursPerWeek" => $vacancyTaxonomy["working-hours"] ?? null,
                // "salaryRange"=> "2500-3000",
                "salaryStart" => $vacancyModel->getSalaryStart(),
                "salaryEnd" => $vacancyModel->getSalaryEnd(),
                // "thumbnail" => $vacancyModel->checkImported() ? $option->getDefaultImage('object') : $company->getThumbnail('object'),
                "thumbnail" => $thumbnail,
                "description" => StringHelper::shortenString($vacancyModel->getDescription(), 0, 10000),
                // "postedDate" => date_format(new DateTime($vacancy->post_date_gmt), "Y-m-d H:i A")
                "postedDate" => DateHelper::doLocale($vacancy->post_date_gmt, 'nl_NL'),
                "isNew" => date('Y-m-d') === date('Y-m-d', strtotime($vacancy->post_date_gmt)),
                "experiences" => $vacancyTaxonomy["experiences"] ?? null,
                "status" => $vacancyTaxonomy["status"] ?? null,
            ];
        }, $this->vacancyCollection);

        return $formattedResponse;
    }

    public function formatSingle()
    {
        $vacancyModel = new Vacancy($this->vacancyCollection->ID);

        $candidate = $this->userPayload ? new Candidate($this->userPayload->user_id) : null;

        $company = new Company($this->vacancyCollection->post_author);
        $vacancyTaxonomy = $vacancyModel->getTaxonomy(false);

        /** Set social media response */
        $socialMedia = ["facebook", "linkedin", "instagram", "twitter"];

        $socialMediaResponse = [];
        foreach ($socialMedia as $key => $socmed) {
            $socialMediaResponse[$key] = [
                "id" => $key + 1,
                "type" => $socmed,
                "url" => $vacancyModel->getSocialMedia($socmed) != null || $vacancyModel->getSocialMedia($socmed) != "" ?
                    $vacancyModel->getSocialMedia($socmed) :
                    $company->getSocialMedia($socmed)
            ];
        }

        $jobVideo = $vacancyModel->getVideoUrl();
        $videoUrl = "";
        if ($jobVideo != "") {
            $videoUrl = strpos($vacancyModel->getVideoUrl(), "youtu") ? ["type" => "url", "url" => StringHelper::getYoutubeID($vacancyModel->getVideoUrl())] : ["type" => "file", "url" => $vacancyModel->getVideoUrl()]; // Added Line
        } else {
            $videoUrl = strpos($company->getVideoUrl(), "youtu") ? ["type" => "url", "url" => StringHelper::getYoutubeID($company->getVideoUrl())] : ["type" => "file", "url" => $company->getVideoUrl()]; // Added Line
        }

        /** Addition Feedback 01 Nov 2023 */
        /** Get language */
        $languageResponse = $vacancyModel->getLanguage();
        if ($languageResponse) {
            $vacancyTaxonomy[] = [
                'id' => $languageResponse['value'],
                'name' => LanguageConstant::get('fe', $languageResponse['value'])
            ];
        }
        /** End Addition Feedback 01 Nov 2023 */

        /** Changes start here */
        $isImported = $vacancyModel->checkImported();
        if ($isImported) {
            /** Get RV Administrator User data */
            $rvAdmin = get_user_by('email', 'adminjob@recruitmentvalley.com');

            $formattedResponse = [
                "id" => $this->vacancyCollection->ID,
                "isPaid" => $vacancyModel->getIsPaid(),
                "shortDescription" => $vacancyTaxonomy,
                "title" => $this->vacancyCollection->post_title,
                "isFavorite" => $candidate ? $candidate->isFavorite($this->vacancyCollection->ID) : false,
                "company" =>  [
                    "company_id" => $rvAdmin->ID,
                    "logo" => '',
                    "name" => $vacancyModel->getImportedCompanyName() ?? '',
                    "about" => '',
                    "sector" => $vacancyModel->getImportedCompanySector() ?? [],
                    "totalEmployee" => $vacancyModel->getImportedCompanyTotalEmployees() ?? '',
                    "tel" => '',
                    "email" => $vacancyModel->getImportedCompanyEmail() ?? '',
                    "gallery" => '',
                    "website" => '',
                    "city" => $vacancyModel->getImportedCompanyCity() ?? '',
                    "country" => $vacancyModel->getImportedCompanyCountry() ?? '',
                    "longitude" => $vacancyModel->getImportedCompanyLongitude() ?? '',
                    "latitude" => $vacancyModel->getImportedCompanyLatitude() ?? '',
                ],
                "socialMedia" => $socialMediaResponse,
                "contents" => [
                    "description" => $vacancyModel->getDescription(),
                    "term" => $vacancyModel->getTerm(),
                ],
                "country" => $vacancyModel->getCountry(),
                "city" => $vacancyModel->getCity(),
                "externalUrl" => $vacancyModel->getExternalUrl(),
                "placementAddress" => $vacancyModel->getPlacementAddress(),
                "videoId" => $videoUrl,
                "gallery" => $vacancyModel->getGallery(),
                "reviews" => $vacancyModel->getReviews(),
                "applicationProcessTitle" => $vacancyModel->getApplicationProcessTitle(),
                "applicationProcessDescription" => $vacancyModel->getApplicationProcessDescription(),
                "steps" => $vacancyModel->getApplicationProcessStep(),
                "salaryStart" => $vacancyModel->getSalaryStart(),
                "salaryEnd" => $vacancyModel->getSalaryEnd(),
                "postedDate" => $vacancyModel->getPublishDate("d-m-Y H:i"),
                "expiredDate" => $vacancyModel->getExpiredAt("d-m-Y H:i"),
                "applicationProcedure" => [
                    "title" => $vacancyModel->getApplicationProcessTitle(),
                    "text" => $vacancyModel->getApplicationProcessDescription(),
                    "steps" => $vacancyModel->getApplicationProcessStep()
                ],
                "longitude" => $vacancyModel->getPlacementAddressLongitude(),
                "latitude" => $vacancyModel->getPlacementAddressLatitude(),
            ];
        } else {
            if ($vacancyModel->checkIsForAnotherCompany()) {
                if ($vacancyModel->checkUseExistingCompany()) {
                    $selectedCompany = $vacancyModel->getSelectedCompany();
                    if ($selectedCompany) {
                        $company = new Company($selectedCompany);
                        $companyData = [
                            'id'    => $company->user_id,
                            'name'  => $company->getName(),
                            'about' => $company->getDescription(),
                            'logo'  => $company->getThumbnail(),
                            'sector'    => $company->getTerms('sector'),
                            'totalEmployee' => $company->getTotalEmployees(),
                            'tel'   => $company->getPhoneCode() . $company->getPhone(),
                            'email' => $company->getEmail(),
                            'gallery'   => $company->getGallery(true),
                            // 'socialMedia'   =>
                            //     'facebook' => $company->getFacebook(),
                            //     'twitter' => $company->getTwitter(),
                            //     'linkedin' => $company->getLinkedin(),
                            //     'instagram' => $company->getInstagram(),
                            // ],
                            // 'socialMedia'   => $socialMediaResponse,
                            'website'   => $company->getWebsite(),
                            // 'maps'  =>
                            'city'      => $company->getCity(),
                            'country'   => $company->getCountry(),
                            'countryCode'   => $company->getCountryCode(),
                            'longitude' => $company->getLongitude(),
                            'latitude'  => $company->getLatitude(),
                        ];
                    } else {
                        $companyData = [
                            'id'    => $vacancyModel->getSelectedCompany(),
                            'name'  => $vacancyModel->getCustomCompanyName(),
                            'about' => $vacancyModel->getCustomCompanyDescription(),
                            'logo'  => $vacancyModel->getCustomCompanyLogo('url'),
                            'sector'    => $vacancyModel->getCustomCompanySector('array'),
                            'totalEmployee' => $vacancyModel->getCustomCompanyTotalEmployees('label'),
                            'tel'   => $vacancyModel->getCustomCompanyPhoneCode('label') . $vacancyModel->getCustomCompanyPhoneNumber(),
                            'email' => $vacancyModel->getCustomCompanyEmail(),
                            'gallery'   => '',
                            'website'   => '',
                            'city'      => '',
                            'country'   => '',
                            'countryCode'   => '',
                            'longitude' => '',
                            'latitude'  => '',
                        ];
                    }
                } else {
                    $companyData = [
                        'id'    => $vacancyModel->getSelectedCompany(),
                        'name'  => $vacancyModel->getCustomCompanyName(),
                        'about' => $vacancyModel->getCustomCompanyDescription(),
                        'logo'  => $vacancyModel->getCustomCompanyLogo('url'),
                        'sector'    => $vacancyModel->getCustomCompanySector('array'),
                        'totalEmployee' => $vacancyModel->getCustomCompanyTotalEmployees('label'),
                        'tel'   => $vacancyModel->getCustomCompanyPhoneCode('label') . $vacancyModel->getCustomCompanyPhoneNumber(),
                        'email' => $vacancyModel->getCustomCompanyEmail(),
                        'gallery'   => '',
                        'website'   => '',
                        'city'      => '',
                        'country'   => '',
                        'countryCode'   => '',
                        'longitude' => '',
                        'latitude'  => '',
                    ];
                }
                // $companyID = $vacancyModel->checkUseExistingCompany() ? $vacancyModel->checkUseExistingCompany() : $company->user_id;
            } else {
                $companyData = [
                    'id'    => $company->user_id,
                    'name'  => $company->getName(),
                    'about' => $company->getDescription(),
                    'logo'  => $company->getThumbnail(),
                    'sector'    => $company->getTerms('sector'),
                    "totalEmployee" => $company->getTotalEmployees(),
                    "tel"   => $company->getPhoneCode() . $company->getPhone(),
                    "email" => $company->getEmail(),
                    "gallery"   => $company->getGallery(true),
                    // "socialMedia"   =>
                    //     "facebook" => $company->getFacebook(),
                    //     "twitter" => $company->getTwitter(),
                    //     "linkedin" => $company->getLinkedin(),
                    //     "instagram" => $company->getInstagram(),
                    // ],
                    // "socialMedia"   => $socialMediaResponse,
                    "website"   => $company->getWebsite(),
                    // "maps"  =>
                    "city"      => $company->getCity(),
                    "country"   => $company->getCountry(),
                    "countryCode"   => $company->getCountryCode(),
                    "longitude" => $company->getLongitude(),
                    "latitude"  => $company->getLatitude(),
                ];
            }

            /** Anggit's original response (unchanged) */
            $formattedResponse = [
                "id" => $this->vacancyCollection->ID,
                "isPaid" => $vacancyModel->getIsPaid(),
                "shortDescription" => $vacancyTaxonomy,
                "title" => $this->vacancyCollection->post_title,
                // "isFavorite" => $candidate ? $candidate->isFavorite($this->vacancyCollection->post_author) : false, // Changed below
                "isFavorite" => $candidate ? $candidate->isFavorite($this->vacancyCollection->ID) : false,
                "company" =>  [
                    /** Anggit's response */
                    // "company_id" => $company->user_id,
                    // "logo" => $company->getThumbnail(),
                    // "name" => $company->getName(),
                    // "about" => $company->getDescription(),
                    // "sector" => $company->getTerms('sector'),
                    // "totalEmployee" => $company->getTotalEmployees(),
                    // "tel" => $company->getPhoneCode() . $company->getPhone(),
                    // "email" => $company->getEmail(),
                    // "gallery" => $company->getGallery(true),
                    // // "socialMedia" => [
                    // //     "facebook" => $company->getFacebook(),
                    // //     "twitter" => $company->getTwitter(),
                    // //     "linkedin" => $company->getLinkedin(),
                    // //     "instagram" => $company->getInstagram(),
                    // // ],
                    // // "socialMedia" => $socialMediaResponse,
                    // "website" => $company->getWebsite(),
                    // // "maps" => "", // not needed - esa feedback 29-08-2023
                    // "city" => $company->getCity(),
                    // "country" => $company->getCountry(),
                    // "countryCode" => $company->getCountryCode(), // Added Line
                    // "longitude" => $company->getLongitude(),
                    // "latitude" => $company->getLatitude(),

                    /** Changes after vacancy for other company */
                    "company_id"    => $companyData['id'],
                    "logo"      => $companyData['logo'],
                    "name"      => $companyData['name'],
                    "about"     => $companyData['about'],
                    "sector"    => $companyData['sector'],
                    "totalEmployee" => $companyData['totalEmployee'],
                    "tel"       => $companyData['tel'],
                    "email"     => $companyData['email'],
                    "gallery"   => $companyData['gallery'],
                    // "socialMedia" => [
                    //     "facebook" => $company->getFacebook(),
                    //     "twitter" => $company->getTwitter(),
                    //     "linkedin" => $company->getLinkedin(),
                    //     "instagram" => $company->getInstagram(),
                    // ],
                    // "socialMedia" => $socialMediaResponse,
                    "website"   => $companyData['website'],
                    // "maps" => "", // not needed - esa feedback 29-08-2023
                    "city"      => $companyData['city'],
                    "country"   => $companyData['country'],
                    "countryCode"   => $companyData['countryCode'],
                    "longitude"     => $companyData['longitude'],
                    "latitude"      => $companyData['latitude']

                ], // later get company here
                "socialMedia" => $socialMediaResponse,
                "contents" => [
                    "description" => $vacancyModel->getDescription(),
                    "term" => $vacancyModel->getTerm(),
                ],
                "country" => $vacancyModel->getCountry(), // Added Line
                "countryCode" => $vacancyModel->getCountryCode(), // Added Line
                "city" => $vacancyModel->getCity(),
                "externalUrl" => $vacancyModel->getExternalUrl(),
                "placementAddress" => $vacancyModel->getPlacementAddress(),
                // "videoId" => $company->getVideoUrl(), // Changed below
                "videoId" => $videoUrl,
                "gallery" => $vacancyModel->getGallery(),
                "reviews" => $vacancyModel->getReviews(),
                "applicationProcessTitle" => $vacancyModel->getApplicationProcessTitle(),
                "applicationProcessDescription" => $vacancyModel->getApplicationProcessDescription(),
                "steps" => $vacancyModel->getApplicationProcessStep(),
                "salaryStart" => $vacancyModel->getSalaryStart(),
                "salaryEnd" => $vacancyModel->getSalaryEnd(),
                // "postedDate" => $vacancyModel->getPublishDate("Y-m-d H:i A"),
                "postedDate" => $vacancyModel->getPublishDate("d-m-Y H:i"),
                "expiredDate" => $vacancyModel->getExpiredAt("d-m-Y H:i"),
                "longitude" => $vacancyModel->getPlacementAddressLongitude(),
                "latitude" => $vacancyModel->getPlacementAddressLatitude(),
                "applicationProcedure" => [
                    "title" => $vacancyModel->getApplicationProcessTitle(),
                    "text" => $vacancyModel->getApplicationProcessDescription(),
                    "steps" => $vacancyModel->getApplicationProcessStep()
                ],
            ];
        }

        // $formattedResponse = get_field($vacancyModel->acf_application_process_step,$this->vacancyCollection->ID);

        return $formattedResponse;
    }

    public function formatCompany()
    {
        $formattedResponse = array_map(function (WP_Post $vacancy) {
            $vacancyModel = new Vacancy($vacancy->ID);
            $vacancyTaxonomy = $vacancyModel->getTaxonomy(true);

            return [
                "id" => $vacancy->ID,
                "name" => $vacancy->post_title,
                "employmentType" => $vacancyTaxonomy["type"] ?? [],
                "location" => $vacancyTaxonomy["location"] ?? [],
                "sector" => $vacancyTaxonomy["sector"] ?? [],
                "vacancyType" => $vacancyModel->getIsPaid() ? "Paid" : "Free",
                "expiredAt" => $vacancyModel->getExpiredAt("d/m/Y"),
                "status" => $vacancyTaxonomy["status"][0]["name"] ?? null,
                "slug" => $vacancy->post_name
            ];
        }, $this->vacancyCollection);

        return $formattedResponse;
    }

    public function formatFavorite()
    {
        $formattedResponse = array_map(function (WP_Post $vacancy) {
            $vacancyModel = new Vacancy($vacancy->ID);
            $company = new Company($vacancyModel->getAuthor()); // Added Line

            $vacancyTaxonomy = $vacancyModel->getTaxonomy(true);
            return [
                "id" => $vacancy->ID,
                "slug" => $vacancy->post_name,
                "name" => $vacancy->post_title,
                // "image" => $vacancyModel->getThumbnail(), // Changed below
                "image" => $company->getThumbnail('object'),
                "description" => $vacancyModel->getDescription() !== "" ? StringHelper::shortenString($vacancyModel->getDescription(), 0, 500, '...') : "",
                "status" => $vacancyTaxonomy['status'][0]['name'] ?? null,
                // "jobPostedDate" => $vacancy->post_date, // Changed below
                "jobPostedDate" => DateHelper::doLocale($vacancy->post_date, 'nl_NL')
            ];
        }, $this->vacancyCollection);

        return $formattedResponse;
    }

    public function formatResponseUpdate()
    {
        $vacancyModel = new Vacancy($this->vacancyCollection->ID);

        $company = new Company($this->vacancyCollection->post_author);

        /** Set social media response */
        $socialMedia = ["facebook", "linkedin", "instagram", "twitter"];

        $socialMediaResponse = [];
        foreach ($socialMedia as $key => $socmed) {
            $socialMediaResponse[$socmed] = $vacancyModel->getSocialMedia($socmed);
        }

        $jobVideo = $vacancyModel->getVideoUrl();
        $videoUrl = "";
        if ($jobVideo != "") {
            if (is_array($vacancyModel->getVideoUrl())) {
            } else {
                $videoUrl = strpos($vacancyModel->getVideoUrl(), "youtu") ? ["type" => "url", "url" => $vacancyModel->getVideoUrl()] : ["type" => "file", "url" => $vacancyModel->getVideoUrl()]; // Added Line
            }
        } else {
            $videoUrl = strpos($company->getVideoUrl(), "youtu") ? ["type" => "url", "url" => $company->getVideoUrl()] : ["type" => "file", "url" => $company->getVideoUrl()]; // Added Line
        }

        $formattedResponse = [
            "id" => $this->vacancyCollection->ID,
            "isPaid" => $vacancyModel->getIsPaid(),
            // "shortDescription" => $vacancyTaxonomy,
            "title" => $this->vacancyCollection->post_title,
            "contents" => [
                "description" => $vacancyModel->getDescription(),
                "term" => $vacancyModel->getTerm(),
            ],
            "country" => [$vacancyModel->getCountry('object')], // Added Line
            "countryCode" => $vacancyModel->getCountryCode(), // Added Line
            "city" => [$vacancyModel->getCity('object')],
            "placementAddress" => $vacancyModel->getPlacementAddress(),
            "videoId" => $videoUrl,
            "gallery" => $vacancyModel->getGallery(),
            "reviews" => $vacancyModel->getReviews(),
            "salaryStart" => $vacancyModel->getSalaryStart(),
            "salaryEnd" => $vacancyModel->getSalaryEnd(),
            "postedDate" => $vacancyModel->getPublishDate("Y-m-d H:i A"),
            "expiredDate" => $vacancyModel->getExpiredAt(),
            "socialMedia" => $socialMediaResponse,
            "longitude" => $vacancyModel->getPlacementAddressLongitude(),
            "latitude" => $vacancyModel->getPlacementAddressLatitude(),
            "applicationProcedure" =>  [
                "title" => $vacancyModel->getApplicationProcessTitle(),
                "text" => $vacancyModel->getApplicationProcessDescription(),
                "steps" =>  $vacancyModel->getApplicationProcessStep(),
            ],
            "applyFromThisPlatform" => [
                "externalUrl" => $vacancyModel->getExternalUrl()
            ]
        ];

        $vacancyTax = $vacancyModel->getTax();

        /** Changes Start here
         * convert key to camelCase
         */
        $taxonomy = [];
        if (is_array($vacancyTax)) {
            foreach ($vacancyTax as $key => $value) {
                if ($key === 'working-hours') {
                    $camelKey = 'hoursPerWeek';
                } else {
                    $camelKey = StringHelper::convertCamelCase($key, '-', 'string');
                }

                $taxonomy[$camelKey] = $value;
            }
        }

        // $formattedResponse = array_merge($formattedResponse, $vacancyTax); // Changed Below
        $formattedResponse = array_merge($formattedResponse, $taxonomy);

        return $formattedResponse;
    }
}
