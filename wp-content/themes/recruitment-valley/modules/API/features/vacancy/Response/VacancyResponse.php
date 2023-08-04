<?php

namespace Vacancy;

class VacancyResponse
{
    public $vacancyCollection;

    // public function __construct()
    // {
    //     $this->vacancyCollection = $vacancyCollection;    
    // }

    public function setCollection( $vacancyCollection )
    {
        $this->vacancyCollection = $vacancyCollection;
    }

    public function format()
    {
        $formattedResponse = array_map(function($vacancy){
            $vacancyModel = new Vacancy($vacancy->ID);
            $vacancyModel = $vacancyModel->getAcfProperties();
            $vacancyModel["taxonomy"] = $this->addTaxonomy($vacancy->ID);
            return $vacancyModel;
        }, $this->vacancyCollection);
        
        return $formattedResponse;
    }

    private function addTaxonomy( $vacancy_id )
    {
        $taxonomies = get_post_taxonomies($vacancy_id);

        return $taxonomies;
    }

}