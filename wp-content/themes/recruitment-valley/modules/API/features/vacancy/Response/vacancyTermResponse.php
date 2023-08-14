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
            "status" => $data['status'],
            "employmentType" => $data['type'],
            "hoursPerWeek" => $data['working-hours']
        ];

        return $response;
    }
}
