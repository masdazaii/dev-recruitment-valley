<?php

namespace Vacancy;

class VacancyCrudController
{
    public function create()
    {
        $vacancy = new Vacancy();
        return [
            "status" => 200,
            "data" => $vacancy->getPropeties()
        ];
    }

    public function show()
    {

    }
}