<?php

namespace Vacancy;

use Constant\Message;

class VacancyCrudController
{
    private $_message;

    public function __construct()
    {
        $this->_message = new Message;
    }

    public function createFreeJob( $request )
    {
        $vacancy = new Vacancy;

        $vacancy->setTitle($request["title"]);
        $vacancy->setDescription($request["description"]);
        $vacancy->setApplyFromThisPlatform($request["apply_from_this_platform"]); 
        // $vacancy = new Vacancy();
        // return [
        //     "status" => 200,
        //     "data" => $vacancy->getPropeties()
        // ];
        


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
                "status" => 404,
                "message" => $this->_message->get("vacancy.not_found"),
                "data" => $vacancies
            ];
        }
    }
}