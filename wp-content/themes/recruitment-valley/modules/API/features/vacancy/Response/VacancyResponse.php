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
                "taxonomy" => $vacancyTaxonomy,
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
                "logo" => "",
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
            "videoId" => "",
            "gallery" => [],
            "reviews" => [],
            "steps" => [],
            // "salaryStart" => $vacancyModel->getSalaryStart(),
            // "salaryEnd" => $vacancyModel->getSalaryEnd(),
            "thumbnail" => $vacancyModel->getThumbnail(),
            "description" => $vacancyModel->getDescription(),
        ];

        return $formattedResponse;
    }
}
