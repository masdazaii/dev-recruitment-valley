<?php

namespace Vacancy;

use Candidate\Profile\Candidate;
use DateTime;
use DateTimeImmutable;
use Model\Company;
use WP_Post;
use Helper\StringHelper;
use Helper\DateHelper;

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
        $formattedResponse = array_map(function (WP_Post $vacancy) {
            $vacancyModel = new Vacancy($vacancy->ID);
            $company = new Company($vacancy->post_author);
            $vacancyTaxonomy = $vacancyModel->getTaxonomy(true);

            return [
                "id" => $vacancy->ID,
                "slug" => $vacancy->post_name,
                "name" => $vacancy->post_title,
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
                "thumbnail" => $company->getThumbnail('object'),
                "description" => StringHelper::shortenString($vacancyModel->getDescription(), 0, 10000),
                // "postedDate" => date_format(new DateTime($vacancy->post_date_gmt), "Y-m-d H:i A")
                "postedDate" => DateHelper::doLocale($vacancy->post_date_gmt, 'nl_NL'),
                "isNew" => date('Y-m-d') === date('Y-m-d', strtotime($vacancy->post_date_gmt))
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

        $formattedResponse = [
            "id" => $this->vacancyCollection->ID,
            "isPaid" => $vacancyModel->getIsPaid(),
            "shortDescription" => $vacancyTaxonomy,
            "title" => $this->vacancyCollection->post_title,
            // "isFavorite" => $candidate ? $candidate->isFavorite($this->vacancyCollection->post_author) : false, // Changed below
            "isFavorite" => $candidate ? $candidate->isFavorite($this->vacancyCollection->ID) : false,
            "company" =>  [
                "company_id" => $company->user_id,
                "logo" => $company->getThumbnail(),
                "name" => $company->getName(),
                "about" => $company->getDescription(),
                "sector" => $company->getTerms('sector'),
                "totalEmployee" => $company->getTotalEmployees(),
                "tel" => $company->getPhoneCode() . $company->getPhone(),
                "email" => $company->getEmail(),
                "gallery" => $company->getGallery(true),
                // "socialMedia" => [
                //     "facebook" => $company->getFacebook(),
                //     "twitter" => $company->getTwitter(),
                //     "linkedin" => $company->getLinkedin(),
                //     "instagram" => $company->getInstagram(),
                // ],
                // "socialMedia" => $socialMediaResponse,
                "website" => $company->getWebsite(),
                // "maps" => "", // not needed - esa feedback 29-08-2023
                "city" => $company->getCity(),
                "country" => $company->getCountry(),
                "longitude" => $company->getLongitude(),
                "latitude" => $company->getLatitude(),
            ], // later get company here
            "socialMedia" => $socialMediaResponse,
            "contents" => [
                "description" => $vacancyModel->getDescription(),
                "term" => $vacancyModel->getTerm(),
            ],
            "city" => $vacancyModel->getCity(),
            "externalUrl" => $vacancyModel->getExternalUrl(),
            "placementAddress" => $vacancyModel->getPlacementAddress(),
            // "videoId" => $company->getVideoUrl(), // Changed below
            "videoId" => $company->getVideoUrl() ? StringHelper::getYoutubeID($company->getVideoUrl()) : null, // Added Line
            "gallery" => $vacancyModel->getGallery(),
            "reviews" => $vacancyModel->getReviews(),
            "steps" => $vacancyModel->getApplicationProcessStep(),
            "salaryStart" => $vacancyModel->getSalaryStart(),
            "salaryEnd" => $vacancyModel->getSalaryEnd(),
            // "postedDate" => $vacancyModel->getPublishDate("Y-m-d H:i A"),
            "postedDate" => $vacancyModel->getPublishDate("d-m-Y H:i"),
            "expiredDate" => $vacancyModel->getExpiredAt("d-m-Y H:i")
        ];

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

        $formattedResponse = [
            "id" => $this->vacancyCollection->ID,
            "isPaid" => $vacancyModel->getIsPaid(),
            // "shortDescription" => $vacancyTaxonomy,
            "title" => $this->vacancyCollection->post_title,
            "contents" => [
                "description" => $vacancyModel->getDescription(),
                "term" => $vacancyModel->getTerm(),
            ],
            "city" => $vacancyModel->getCity(),
            "placementAddress" => $vacancyModel->getPlacementAddress(),
            "videoId" => $company->getVideoUrl(), // Added Line
            "gallery" => $vacancyModel->getGallery(),
            "reviews" => $vacancyModel->getReviews(),
            "salaryStart" => $vacancyModel->getSalaryStart(),
            "salaryEnd" => $vacancyModel->getSalaryEnd(),
            "postedDate" => $vacancyModel->getPublishDate("Y-m-d H:i A"),
            "expiredDate" => $vacancyModel->getExpiredAt(),
            "socialMedia" => $socialMediaResponse,
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
