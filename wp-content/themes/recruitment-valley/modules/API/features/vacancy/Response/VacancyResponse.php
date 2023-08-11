<?php

namespace Vacancy;

use WP_Post;

class VacancyResponse
{
    public $vacancyCollection;

    // public function __construct()
    // {
    //     $this->vacancyCollection = $vacancyCollection;
    // }

    public function setCollection($vacancyCollection)
    {
        $this->vacancyCollection = $vacancyCollection;
    }

    public function format()
    {
        $formattedResponse = array_map(function (WP_Post $vacancy) {
            $vacancyModel = new Vacancy($vacancy->ID);
            $vacancyTaxonomy = $vacancyModel->getTaxonomy(true);
            return [
                "id" => $vacancy->ID,
                "slug" => $vacancy->post_name,
                "name" => $vacancy->post_title,
                "city" => $vacancyModel->getCity(),
                "education" => $vacancyTaxonomy["education"]  ?? null,
                "employmentType" => $vacancyTaxonomy["type"] ?? null,
                "location" => $vacancyTaxonomy["location"] ?? null,
                "role" => $vacancyTaxonomy["role"] ?? null,
                "sector" => $vacancyTaxonomy["sector"] ?? null,
                "hoursPerWeek" => $vacancyTaxonomy["working-hours"] ?? null,
                // "salaryRange"=> "2500-3000",
                "salaryStart" => $vacancyModel->getSalaryStart(),
                "salaryEnd" => $vacancyModel->getSalaryEnd(),
                "thumbnail" => $vacancyModel->getThumbnail(),
                "description" => $vacancyModel->getDescription(),
            ];
        }, $this->vacancyCollection);

        return $formattedResponse;
    }

    public function formatSingle()
    {
        $vacancyModel = new Vacancy($this->vacancyCollection->ID);
        $vacancyTaxonomy = $vacancyModel->getTaxonomy(false);
        $formattedResponse = [
            "id" => $this->vacancyCollection->ID,
            "isPaid" => $vacancyModel->getIsPaid(),
            "shortDescription" => $vacancyTaxonomy,
            "title" => $this->vacancyCollection->post_title,
            "company" =>  [
                "logo" => "https://picsum.photos/200/300",
                "name" => "",
                "about" => "",
                "maps" => "",
                "sector" => "",
                "totalEmployee" => "",
                "tel" => "",
                "email" => "",
                "socialMedia" => [],
                "website" => ""
            ], // later get company here
            "contents" => [
                "description" => $vacancyModel->getDescription(),
                "term" => $vacancyModel->getTerm(),
            ],
            "videoId" => $vacancyModel->getVideoUrl(),
            "gallery" => $vacancyModel->getGallery(),
            "reviews" => $vacancyModel->getReviews(),
            "steps" => $vacancyModel->getApplicationProcessStep(),
            "salaryStart" => $vacancyModel->getSalaryStart(),
            "salaryEnd" => $vacancyModel->getSalaryEnd(),
            "thumbnail" => $vacancyModel->getThumbnail(),
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
                "employmentType" => $vacancyTaxonomy["type"] ?? null,
                "location" => $vacancyTaxonomy["location"] ?? null,
                "sector" => $vacancyTaxonomy["sector"] ?? null,
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
                "status" => $vacancyTaxonomy['status'] ?? null,
                "thumbnail" => $vacancyModel->getThumbnail(),
                "description" => $vacancyModel->getDescription(),
                "jobPostedDate" => $vacancy->post_date,
            ];
        }, $this->vacancyCollection);

        return $formattedResponse;
    }
}
