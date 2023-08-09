<?php

namespace Company\Vacancy;

use Constant\Message;
use Model\Company;
use Model\Term;
use Vacancy\Vacancy;
use WP_Term;

class VacancyController
{

    public $vacancyModel;
    public $termModel;
    public $company;
    private $_message;

    public function __construct()
    {
        $this->company = new Company;
        $this->vacancyModel = new Vacancy;
        $this->termModel = new Term;
        $this->_message = new Message;
    }

    public function getByStatus( $request )
    {
        $status = $request["status"];
        $vacancies = $this->vacancyModel->getByStatus( $status );
        return [
            "status" => 200,
            "data" => $vacancies,
            "message" => $this->_message->get("vacancy.get_all")
        ];
    }

    public function getTermCount( $request )
    {
        $this->company->setUserId($request["user_id"]);

        return [
            "status" => 200,
            "data" => [
                "open" => $this->company->getVacancyByStatus('open'),
                "close" => $this->company->getVacancyByStatus('close'),
                "declined" => $this->company->getVacancyByStatus('declined'),
                "processing" => $this->company->getVacancyByStatus('processing'),
            ],
        ];
    }
}