<?php

namespace Model;

use Exception;
use WP_Error;
use WP_Post;
use Model\Company;

/**
 * CPT Child company Model class
 *
 * IMPORTANT NOTE
 * Inherit Company Model only for the acf meta property.
 * Function won't work since this is CPT and company is a user role.
 *
 * $this->user is used as post object
 * $this->user_id is used as post_id
 * it written as user_id because Company is a user Model
 *
 */
class ChildCompany extends Company
{
    public const acf_child_company_email = 'rv_urecruiter_child_company_email';
    public const acf_child_company_owner = 'rv_urecruiter_child_company_owner';

    public const meta_child_company_uuid = 'rv_urecruiter_child_company_uuid';

    public function __construct(Mixed $childCompany = null)
    {
        $this->isChild = true;

        if ($childCompany) {
            if (is_numeric($childCompany)) {
                $this->user_id  = $childCompany;
                $this->user     = get_post($childCompany);
            } else if ($childCompany instanceof WP_Post) {
                $this->user_id  = $childCompany->ID;
                $this->user     = $childCompany;
            }
        }
    }

    public static function insert(array $data): self
    {
        $postModel      = new PostModel();
        $childCompany   = $postModel->create($data);
        if ($childCompany) {
            if ($childCompany instanceof WP_Error) {
                throw $childCompany;
            }

            return new self($childCompany);
        } else {
            return false;
        }
    }

    /**
     * Getter acf value function
     *
     * Override method in Company Model with same name.
     *
     * @param String $key
     * @param boolean $single or formatted value
     * @return void
     */
    public function getProp(String $key, Bool $single = false, String $type = 'acf')
    {
        $this->checkID();

        if ($type == 'acf') {
            return get_field($key, $this->user_id, $single);
        } else {
            return get_post_meta($this->user_id, $key, $single);
        }
    }

    /**
     * Setter acf value function
     *
     * Override method in Company Model with same name.
     *
     * @param String $acf_field
     * @param boolean $single
     * @return mixed Int|Bool on false
     */
    public function setProp($acf_field, $value, $repeater = false, $type = 'acf'): mixed
    {
        $this->checkID();

        if ($type == 'meta') {
            return update_post_meta($this->user_id, $acf_field, $value);
        } else {
            return update_field($acf_field, $value, $this->user_id);
        }
    }

    /**
     * Set Child Company Name function.
     *
     * Override method in Company Model with same name.
     *
     * @return mixed Int|Bool on false
     */
    public function setName(String $value): mixed
    {
        return $this->setProp($this->name, $value, false);
    }

    /**
     * Get Child Company Name function.
     *
     * Override method in Company Model with same name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->user->title;
    }

    /**
     * Get Child Company Email function.
     *
     * Override method in Company Model with same name.
     *
     * @return mixed
     */
    public function getEmail()
    {
        return $this->getProp(self::acf_child_company_email, true, 'acf');
    }

    /**
     * Get Phone Number function
     *
     * Get Company phone number.
     * Either it :
     * - code only with parameter               : code
     * - number only with parameter             : number
     * - complete Phone number with parameter   : full
     *
     * default is full
     *
     * @param string $result
     * @return Mixed
     */
    public function getPhoneNumber($result = 'full')
    {
        $this->checkID();
        switch ($result) {
            case 'code':
            case 'phonecode':
            case 'phone-code':
            case 'code-only':
                return $this->getPhoneCode();
                break;
            case 'number':
            case 'phonenumber':
            case 'phone-number':
            case 'number-only':
                return $this->getPhone();
                break;
            case 'full':
            default:
                return $this->getPhoneCode() . ' ' . $this->getPhone();
                break;
        }
    }

    /**
     * Get Company Recruiter name function
     *
     * @return mixed
     */
    public function getRecruiterCompanyOwner()
    {
        return $this->getProp(self::acf_child_company_owner, true, 'acf');
    }
}
