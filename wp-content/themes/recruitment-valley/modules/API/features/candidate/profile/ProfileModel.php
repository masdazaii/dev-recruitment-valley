<?php

namespace Candidate\Profile;

use Constant\Message;
use Exception;
use WP_User;

class Candidate
{
    public $user;

    public $user_id;

    private $dateOfBirth = "ucaa_date_of_birth";
    private $phone = "ucaa_phone";
    private $phoneCode = "ucaa_phone_code";
    private $country = "ucaa_country";
    private $city = "ucaa_city";
    private $linkedin = "ucaa_linkedin_url_page";
    private $isFullRegistered = "ucaa_is_full_registered";
    private $cv = "ucaa_cv";
    private $image = "ucaa_image";

    public function __construct( $user_id = false)
    {
        if($user_id) {
            $user = get_user_by('id', $user_id);
            if(!$user)
            {
                $message = new Message;
                throw new Exception($message->get('candidate.get.not_found'), 400);
            }
            $this->user_id = $user_id;
            $this->user = get_user_by('id', $user_id);
        }
    }

    public function setuserId($user_id)
    {

    }

    public function setUser(WP_User $user)
    {

    }

    public function getFirstName()
    {
        return $this->user->first_name;
    }

    public function getLastName()
    {
        return $this->user->last_name;
    }

    public function getEmail()
    {
        return $this->user->user_email;
    }

    public function getRole()
    {
        return $this->user->roles[0];
    }

    public function getPhoneNumber()
    {
        return $this->getProp($this->phone);
    }

    public function getPhoneNumberCode()
    {
        return $this->getProp($this->phoneCode);
    }

    public function getCountry()
    {
        return $this->getProp($this->country);
    }

    public function getCity()
    {
        return $this->getProp($this->city);
    }

    public function getCv()
    {
        return $this->getProp($this->cv);
    }

    public function getImage()
    {
        $image = $this->getProp($this->image);
        return $image ? $image["url"] : null;
    }
    
    public function getDateOfBirth()
    {
        return $this->getProp($this->dateOfBirth);
    }

    public function getLinkedinPage()
    {
        return $this->getProp($this->linkedin);
    }

    public function getProp($acf_field, $single = true)
    {
        return get_field($acf_field, 'user_'.$this->user_id, $single);
    }
}