<?php

namespace Global\Rss;

use Company\Vacancy\VacancyController;

class RssService
{
    private $rssController;
    private $vacancyController;

    public function __construct()
    {
        $this->rssController = new RssController;
        $this->vacancyController = new VacancyController;
    }

    public function get()
    {
        $defaultValue = [
            "page" => 1,
            "search" => '', 
            "salaryStart" => 0,
            "salaryEnd" => 10000,
            "perPage" => 10,
        ];

        $vacancies = $this->vacancyController->getAll( $defaultValue );
        echo $this->rssController->convert($vacancies);
    }
} 