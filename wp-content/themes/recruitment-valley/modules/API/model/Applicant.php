<?php

namespace Model;

class Applicant
{
    private $applicant_id;

    public $cover_letter = "cover_letter";
    public $phone_number = "phone_number";
    public $phone_number_code = "phone_code_area";

    private $_acf_candidate     = "applicant_candidate";
    private $_acf_apply_date    = "apply_date";

    public function __construct($applicant_id = false)
    {
        $this->applicant_id = $applicant_id;
    }

    public function getCoverLetter()
    {
        return $this->getProp($this->cover_letter);
    }

    public function getPhoneNumber()
    {
        return $this->getProp($this->phone_number);
    }

    public function getPhoneNumberCode()
    {
        return $this->getProp($this->phone_number_code);
    }

    public function getProp($acf_field, $single = false)
    {
        return get_field($acf_field,  $this->applicant_id, $single);
    }

    public function getCandidate()
    {
        return $this->getProp($this->_acf_candidate, true);
    }

    public function getApplyDate()
    {
        return $this->getProp($this->_acf_apply_date, true);
    }
}
