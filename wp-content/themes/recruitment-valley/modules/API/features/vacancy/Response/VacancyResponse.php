<?php

namespace Vacancy;

use DateTime;
use DateTimeImmutable;
use Model\Company;
use WP_Post;

class VacancyResponse
{
    public $vacancyCollection;

    public function setCollection($vacancyCollection)
    {
        $this->vacancyCollection = $vacancyCollection;
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
                "postedDate" => date_format(new DateTime($vacancy->post_date_gmt), "Y-m-d H:i A")
            ];
        }, $this->vacancyCollection);

        return $formattedResponse;
    }

    public function formatSingle()
    {
        $vacancyModel = new Vacancy($this->vacancyCollection->ID);

        $company = new Company($this->vacancyCollection->post_author);
        $vacancyTaxonomy = $vacancyModel->getTaxonomy(false);
        $formattedResponse = [
            "id" => $this->vacancyCollection->ID,
            "isPaid" => $vacancyModel->getIsPaid(),
            "shortDescription" => $vacancyTaxonomy,
            "title" => $this->vacancyCollection->post_title,
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
                "socialMedia" => [
                    "facebook" => $company->getFacebook(),
                    "twitter" => $company->getTwitter(),
                    "linkedin" => $company->getLinkedin(),
                    "instagram" => $company->getInstagram(),
                ],
                "website" => $company->getWebsite()
            ], // later get company here
            "contents" => [
                "description" => $vacancyModel->getDescription(),
                "term" => $vacancyModel->getTerm(),
            ],
            "city" => $vacancyModel->getCity(),
            "placementAddress" => $vacancyModel->getPlacementAddress(),
            "videoId" => $company->getVideoUrl(),
            "gallery" => $company->getGallery(),
            "reviews" => $vacancyModel->getReviews(),
            "steps" => $vacancyModel->getApplicationProcessStep(),
            "salaryStart" => $vacancyModel->getSalaryStart(),
            "salaryEnd" => $vacancyModel->getSalaryEnd(),
            "postedDate" => $vacancyModel->getPublishDate("Y-m-d H:i A")
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
                "expiredAt" => strtotime($vacancyModel->getExpiredAt()),
                "status" => $vacancyTaxonomy["status"][0]["name"] ?? null,
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
                "description" => $vacancyModel->getDescription(),
                "status" => $vacancyTaxonomy['status'][0]['name'] ?? null,
                "jobPostedDate" => $vacancy->post_date,
            ];
        }, $this->vacancyCollection);

        return $formattedResponse;
    }
}
