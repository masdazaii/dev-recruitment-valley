<?php

namespace Vacancy;

use Constant\Message;
use WP_Post;

class VacancyCrudController
{
    private $_message;

    public function __construct()
    {
        $this->_message = new Message;
    }

    public function show()
    {

    }

    public function getAll( $request )
    {
        $params = $request;

        // $page = $params["page"];

        // $search = $params["search"];

        // $city = $params["city"];

        // $education = $params["education"];

        // $role = $params["role"];

        // $sector = $params["sector"];

        // $hoursPerWeek = $params["hoursPerWeek"];

        // $salaryStart = $params["salaryStart"];

        // $salaryEnd = $params["salaryEnd"];

        // $vacancy = new Vacancy;

        // $postsPerPage = $params["postPerPage"];

        $args = [
            "post_type" => "vacancy",
            "numberposts" => -1,
            "offset" => 10,
            "order" => "ASC",
            "post_status" => "publish",
            // "paged" => $page
        ];

        $vacancies = get_posts( $args );

        if(count($vacancies) > 0)
        {
            return [
                "status" => 200,
                "message" => $this->_message->get("vacancy.get_all"),
                "data" => $vacancies
            ];
        }else{
            return [
                "status" => 200,
                "message" => $this->_message->get("vacancy.not_found"),
                "data" => $vacancies
            ];
        }
    }

    public function get( $request )
    {
        $vacancy = new Vacancy;

        $vacancySlug = $request['vacancy_slug'];

        $vacancy = get_page_by_path($vacancySlug, OBJECT, 'vacancy');

        if($vacancy instanceof WP_Post)
        {
            return [
                "status" => 200,
                "message" => $this->_message->get("vacancy.get_all"),
                "data" => $vacancy
            ];
        }else{
            return [
                "status" => 404,
                "message" => $this->_message->get("vacancy.not_found"),
                // "data" => []
            ];
        }
    }

    public function createFree( $request )
    {
        $payload = [
            "title" => $request["name"],
            // "sector" => $request["sector"],
            // "role" => $request["role"],
            "description" => $request["description"],
            // "type" => $request["type"],
            // "location" => $request["location"],
            // "education" => $request["education"],
            // "workingHours" => $request["workingHours"],
            "salary_start" => $request["salaryStart"],
            "salary_end" => $request["salaryEnd"],
            "external_url" => $request["externalUrl"],
            "apply_from_this_platform" => isset($request["externalUrl"]) ? true : false,
            "is_paid" => false,
            "user_id" => $request["user_id"],
            "taxonomy" => [
                "sector" => $request["sector"],
                "role" => $request["role"],
                "working-hours" => $request["workingHours"],
                "location" => $request["location"],
                "education" => $request["education"],
                "type" => $request["employmentType"],
                "status" => [31] // set free job become pending category
            ],
        ];

        try {
            $vacancyModel = new Vacancy;

            $vacancyModel->storePost($payload);
            $vacancyModel->setTaxonomy($payload["taxonomy"]);
            $vacancyModel->setProp($vacancyModel->acf_description, $payload["description"]);
            $vacancyModel->setProp($vacancyModel->acf_is_paid, $payload["is_paid"]);
            $vacancyModel->setProp($vacancyModel->acf_salary_start, $payload["salary_start"]);
            $vacancyModel->setProp($vacancyModel->acf_salary_end, $payload["salary_end"]);
            $vacancyModel->setProp($vacancyModel->acf_apply_from_this_platform, $payload["apply_from_this_platform"]);
            
            if($payload["apply_from_this_platform"])
            {
                $vacancyModel->setProp($vacancyModel->acf_external_url, $payload["external_url"]);
            }

            return [
                "status" => 201,
                "message" => $this->_message->get("vacancy.create.free.success"),
            ];
        } catch (\Throwable $th) {
            return [
                "status" => 500,
                "message" => $this->_message->get("vacancy.create.fail"),
            ];
        } catch (\WP_Error $e)
        {
            return [
                "status" => 500,
                "message" => $this->_message->get("vacancy.create.fail"),
            ];
        }


    }

    public function createPaid( $request )
    {

    }
}