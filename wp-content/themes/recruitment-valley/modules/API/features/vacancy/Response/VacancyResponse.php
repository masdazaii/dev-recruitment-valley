<?php

namespace Vacancy;

use Candidate\Profile\Candidate;
use DateTime;
use DateTimeImmutable;
use Model\Company;
use WP_Post;
use Helper\StringHelper;

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
                "thumbnail" => $company->getThumbnail(),
                "description" => $vacancyModel->getDescription(),
                // "postedDate" => date_format(new DateTime($vacancy->post_date_gmt), "Y-m-d H:i A")
                "postedDate" => date_format(new DateTime($vacancy->post_date_gmt), "d-m-Y H:i")
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
                "url" => $company->getSocialMedia($socmed)
            ];
        }

        $formattedResponse = [
            "id" => $this->vacancyCollection->ID,
            "isPaid" => $vacancyModel->getIsPaid(),
            "shortDescription" => $vacancyTaxonomy,
            "title" => $this->vacancyCollection->post_title, // later get company here
            // "isFavorite" => $candidate ? $candidate->isFavorite($this->vacancyCollection->post_author) : false, // Changed below
            "isFavorite" => $candidate ? $candidate->isFavorite($this->vacancyCollection->ID) : false,
            "company" =>  [
                "company_id" => $company->user_id,
                "logo" => $company->getThumbnail(),
                "name" => $company->getName(),
                "about" => $company->getDescription(),
                "maps" => "",
                "sector" => "",
                "totalEmployee" => $company->getTotalEmployees(),
                "tel" => $company->getPhoneCode() . $company->getPhone(),
                "email" => $company->getEmail(),
                "gallery" => $company->getGallery(),
                // "socialMedia" => [
                //     "facebook" => $company->getFacebook(),
                //     "twitter" => $company->getTwitter(),
                //     "linkedin" => $company->getLinkedin(),
                //     "instagram" => $company->getInstagram(),
                // ],
                "socialMedia" => $socialMediaResponse,
                "website" => $company->getWebsite()
            ], // later get company here
            "contents" => [
                "description" => $vacancyModel->getDescription(),
                "term" => $vacancyModel->getTerm(),
            ],
            "city" => $vacancyModel->getCity(),
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
            $vacancyTaxonomy = $vacancyModel->getTaxonomy(true);
            return [
                "id" => $vacancy->ID,
                "slug" => $vacancy->post_name,
                "name" => $vacancy->post_title,
                "thumbnail" => $vacancyModel->getThumbnail(),
                "description" => $vacancyModel->getDescription() !== "" ? StringHelper::shortenString($vacancyModel->getDescription(), 0, 500, '...') : "",
                "status" => $vacancyTaxonomy['status'][0]['name'] ?? null,
                "jobPostedDate" => $vacancy->post_date,
            ];
        }, $this->vacancyCollection);

        return $formattedResponse;
    }

    public function formatResponseUpdate()
    {
        $vacancyModel = new Vacancy($this->vacancyCollection->ID);

        $company = new Company($this->vacancyCollection->post_author);

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
            "gallery" => $company->getGallery(true),
            "reviews" => $vacancyModel->getReviews(),
            "steps" => $vacancyModel->getApplicationProcessStep(),
            "salaryStart" => $vacancyModel->getSalaryStart(),
            "salaryEnd" => $vacancyModel->getSalaryEnd(),
            "postedDate" => $vacancyModel->getPublishDate("Y-m-d H:i A"),
            "expiredDate" => $vacancyModel->getExpiredAt(),
            "applyFromThisPlatform" => $vacancyModel->getApplyFromThisPlatform(),
        ];

        $vacancyTax = $vacancyModel->getTax();

        $formattedResponse = array_merge($formattedResponse, $vacancyTax);

        return $formattedResponse;
    }
}
