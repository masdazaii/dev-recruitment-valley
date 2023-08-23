<?php

namespace Vacancy\Term;

class vacancyTermResponse
{
    public function format($data)
    {
        $response = [
            "education" => $data['education'],
            "location" => $data['location'],
            "role" => $data['role'],
            "sector" => $data['sector'],
            "employmentType" => $data['type'],
            "hoursPerWeek" => $data['working-hours'],
            "experiences" => $data['experiences']
        ];

        return $response;
    }
}
