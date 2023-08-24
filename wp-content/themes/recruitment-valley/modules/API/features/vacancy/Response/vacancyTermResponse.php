<?php

namespace Vacancy\Term;

class vacancyTermResponse
{
    public function format($data)
    {
        $response = [
            "education" => array_key_exists('education', $data) ? $data['education'] : [],
            "location" => array_key_exists('location', $data) ? $data['location'] : [],
            "role" => array_key_exists('role', $data) ? $data['role'] : [],
            "sector" => array_key_exists('sector', $data) ? $data['sector'] : [],
            "employmentType" => array_key_exists('type', $data) ? $data['type'] : [],
            "hoursPerWeek" => array_key_exists('working-hours', $data) ? $data['working-hours'] : [],
            "experiences" => array_key_exists('experiences', $data) ? $data['experiences'] : []
        ];

        return $response;
    }
}
