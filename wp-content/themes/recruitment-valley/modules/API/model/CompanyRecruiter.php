<?php

namespace Model;

use Exception;
use Model\Company;
use WP_Error;
use WP_User;
use WP_User_Query;

class CompanyRecruiter extends BaseModel
{
    public $user;
    private const allowed_role = ["recruiter", "company-recruiter"];

    public const acf_recruiter_name     = "rv_urecruiter_name";
    // public const acf_recruiter_email    = "rv_urecruiter_email";
    public const acf_recruiter_sector   = "rv_urecruiter_sector";
    public const acf_recruiter_image    = "rv_urecruiter_image";
    public const acf_recruiter_phone_code       = "rv_urecruiter_phone_code";
    public const acf_recruiter_phone_number     = "rv_urecruiter__phone_number";
    public const acf_recruiter_country          = "rv_urecruiter_country";
    public const acf_recruiter_country_code     = "rv_urecruiter_country_code";
    public const acf_recruiter_city             = "rv_urecruiter_city";
    public const acf_recruiter_street           = "rv_urecruiter_street";
    public const acf_recruiter_postcode         = "rv_urecruiter_postcode";
    public const acf_recruiter_latitude         = "rv_urecruiter_latitude";
    public const acf_recruiter_longitude        = "rv_urecruiter_longitude";
    public const acf_recruiter_website_url      = "rv_urecruiter_website_url";
    public const acf_recruiter_linkedin_url     = "rv_urecruiter_linkedin_url";
    public const acf_recruiter_facebook_url     = "rv_urecruiter_facebook_url";
    public const acf_recruiter_instagram_url    = "rv_urecruiter_instagram_url";
    public const acf_recruiter_twitter_url      = "rv_urecruiter_twitter_url";
    public const acf_recruiter_kvk_number       = "rv_urecruiter_kvk_number";
    public const acf_recruiter_btw_number       = "rv_urecruiter_btw_number";
    public const acf_recruiter_short_description    = "rv_urecruiter_short_description";
    public const acf_recruiter_benefit              = "rv_urecruiter_benefit";
    public const acf_recruiter_employees            = "rv_urecruiter_employees";
    public const acf_recruiter_video_url            = "rv_urecruiter_video_url";
    public const acf_recruiter_gallery_photo        = "rv_urecruiter_gallery_photo";
    public const acf_recruiter_child_company        = "rv_urecruiter_child_company";
    public const acf_recruiter_is_fully_registered  = "rv_urecruiter_is_fully_registered";

    public const meta_user_otp_is_verified          = "otp_is_verified";

    public const acf_child_company_id             = "rv_urecruiter_child_company_id";
    public const acf_child_company_name           = "rv_urecruiter_child_company_name";
    public const acf_child_company_email          = "rv_urecruiter_child_company_email";
    public const acf_child_company_sector         = "rv_urecruiter_child_company_sector";
    public const acf_child_company_image          = "rv_urecruiter_child_company_image";
    public const acf_child_company_phone_code     = "rv_urecruiter_child_company_phone_code";
    public const acf_child_company_phone_number   = "rv_urecruiter_child_company_phone_number";
    public const acf_child_company_country        = "rv_urecruiter_child_company_country";
    public const acf_child_company_country_code   = "rv_urecruiter_child_company_country_code";
    public const acf_child_company_city           = "rv_urecruiter_child_company_city";
    public const acf_child_company_street         = "rv_urecruiter_child_company_street";
    public const acf_child_company_postcode       = "rv_urecruiter_child_company_postcode";
    public const acf_child_company_latitude       = "rv_urecruiter_child_company_latitude";
    public const acf_child_company_longitude      = "rv_urecruiter_child_company_longitude";
    public const acf_child_company_website_url    = "rv_urecruiter_child_company_website_url";
    public const acf_child_company_linkedin_url   = "rv_urecruiter_child_company_linkedin_url";
    public const acf_child_company_facebook_url   = "rv_urecruiter_child_company_facebook_url";
    public const acf_child_company_instagram_url  = "rv_urecruiter_child_company_instagram_url";
    public const acf_child_company_twitter_url    = "rv_urecruiter_child_company_twitter_url";
    public const acf_child_company_kvk_number     = "rv_urecruiter_child_company_kvk_number";
    public const acf_child_company_btw_number     = "rv_urecruiter_child_company_btw_number";
    public const acf_child_company_short_description  = "rv_urecruiter_child_company_short_description";
    public const acf_child_company_benefit            = "rv_urecruiter_child_company_benefit";
    public const acf_child_company_video_url          = "rv_urecruiter_child_company_video_url";
    public const acf_child_company_employees          = "rv_urecruiter_child_company_employees";

    protected $properties = ["user_email", "roles"];

    public function __construct(Mixed $user = null)
    {
        parent::__construct();

        $this->selector = 'user_';

        if (isset($user)) {
            if (is_numeric($user)) {
                $this->id   = $user;
                $this->user    = get_user_by('id', $this->id);
                if ($this->user) {
                    if ($this->user instanceof WP_Error) {
                        throw $this->user;
                    } else if ($this->user instanceof WP_User) {
                        if (!in_array($this->user->roles[0], self::allowed_role)) {
                            trigger_error("Role not allowed in recruiter model!", E_USER_WARNING);
                        }

                        /** Make WP_User data as property */
                        foreach ($this->properties as $property) {
                            switch ($property) {
                                case 'email':
                                case 'user_email':
                                    $this->{'email'} = $this->user->user_email;
                                    break;
                                case 'role':
                                case 'roles':
                                    $this->{'role'} = $this->user->roles[0];
                                    break;
                            }
                        }
                    } else {
                        throw new Exception("Failed to initialize model!");
                    }
                } else {
                    throw new Exception("User not found!");
                }
            } else if ($user instanceof WP_User) {
                if (!in_array($user->roles[0], self::allowed_role)) {
                    trigger_error("Role not allowed in recruiter model!", E_USER_WARNING);
                }

                $this->user    = $user;
                $this->id           = $user->ID;

                /** Make WP_User data as property */
                foreach ($this->properties as $property) {
                    switch ($property) {
                        case 'email':
                        case 'user_email':
                            $this->{'email'} = $this->user->user_email;
                            break;
                        case 'role':
                        case 'roles':
                            $this->{'role'} = $this->user->roles[0];
                            break;
                    }
                }
            }
        }
    }

    /**
     * Find User by given clue function
     *
     * @param String $by
     * @param Mixed $clue
     * @return self
     */
    public static function find(String $by, Mixed $clue)
    {
        if (in_array($by, ["id", "ID", "email", "login"])) {
            $user = get_user_by($by, $clue);
            return new self($user);
        } else if (in_array($by, ["slug", "code"])) {
            $recruiter = new CompanyRecruiter();
            if ($by == 'slug') {
            } else if ($by == 'code') {
                $filter = [];
                // } else {
                //     $filter['meta'] = [
                //         'relation'  => 'OR',
                //         [
                //             'key'     => $recruiter->prefix . $recruiter->acf_recruiter_code,
                //             'value'   => $clue,
                //             'compare' => '=',
                //         ],
                //         [
                //             'key'     => $recruiter->prefix . $recruiter->acf_recruiter_slug,
                //             'value'   => $clue,
                //             'compare' => '=',
                //         ]
                //     ];
            }
            $user = self::select($filter, []);
            if ($user->get_total() > 0) {
                return new self($user->get_results()[0]);
            } else {
                return new self();
            }
        } else {
            throw new Exception('First parameter must be one of : id | ID | slug | email | login.');
        }
    }

    /**
     * Select User by given filter and/or argument function
     *
     * @param array $filters
     * @param array $args
     * @return mixed
     */
    public static function select(array $filters = [], array $args = [])
    {
        $args = self::setArgumentRecruiter($filters, $args);
        $recruiters = new WP_User_Query($args);

        return $recruiters;
    }

    /** Set arguments for wp_query  */
    private static function setArgumentRecruiter(array $filters = [], array $args = []): array
    {
        if (empty($args)) {
            $args = [
                "number"    => $filters['perPage'] ?? -1,
                "paged"     => $filters['page'] ?? 1,
                "offset"    => $filters['offset'] ?? 0,
                "orderby"   => $filters['orderBy'] ?? "login",
                "order"     => $filters['sort'] ?? 'ASC',
                "role"      => 'company-recruiter'
            ];
        }

        if (!empty($filters)) {
            if (array_key_exists('perPage', $filters)) {
                $args['number'] = $filters['perPage'];
            }

            if (array_key_exists('page', $filters)) {
                $args['paged'] = $filters['page'];
            }

            if (array_key_exists('offset', $filters)) {
                $args['offset'] = $filters['offset'];
            }

            if (array_key_exists('role', $filters)) {
                $args['role'] = $filters['role'];
            }

            if (array_key_exists('search', $filters)) {
                $args['search'] = $filters['search'];
            } else if (array_key_exists('s', $filters)) {
                $args['search'] = $filters['s'];
            }

            if (array_key_exists('search_columns', $filters)) {
                $args['search_columns'] = $filters['search_columns'];
            }

            if (array_key_exists('orderBy', $filters)) {
                if (is_array($filters['orderBy'])) {
                    $args['meta_key']   = $filters['orderBy']['key'];
                    $args['orderby']    = $filters['orderBy']['by'];

                    if (isset($filters['orderBy']['type'])) {
                        $args['meta_type']  = $filters['orderBy']['type'];
                    }
                } else {
                    $args['orderby'] = $filters['orderBy'];
                }
            }

            if (array_key_exists('sort', $filters)) {
                $args['order'] = $filters['sort'];
            }

            if (array_key_exists('include', $filters)) {
                if (is_array($filters['include'])) {
                    $args['include'] = $filters['include'];
                } else {
                    $args['include'] = [$filters['include']];
                }
            }

            if (array_key_exists('exclude', $filters)) {
                if (is_array($filters['exclude'])) {
                    $args['exclude'] = $filters['exclude'];
                } else {
                    $args['exclude'] = [$filters['exclude']];
                }
            }

            if (array_key_exists('meta', $filters)) {
                $args['meta_query'] = $filters['meta'];
            }

            if (array_key_exists('fields', $filters)) {
                $args['fields'] = $filters['fields'];
            }
        }

        return $args;
    }

    /**
     * Set company recruiter name function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setName(Mixed $value)
    {
        return $this->set(self::acf_recruiter_name, $value, 'acf');
    }

    /**
     * Get company recruiter name function
     *
     * @return Mixed
     */
    public function getName()
    {
        // print('<pre>' . print_r($this->get(self::acf_recruiter_name, true, 'acf'), true) . '</pre>');
        // print('<pre>' . print_r(get_field(self::acf_recruiter_name, "user_{$this->id}", true), true) . '</pre>');
        print('<pre>' . print_r(get_field("rv_urecruiter_name", "user_{$this->id}", true), true) . '</pre>');
        return $this->get(self::acf_recruiter_name, true, 'acf');
    }

    /**
     * Set gallery acf value function
     *
     * @param Mixed $value
     * @return Mixed
     */
    public function setGallery(Mixed $value)
    {
        return $this->set(self::acf_recruiter_gallery_photo, $value, 'acf');
    }

    /**
     * Get gallery acf value function
     *
     * @param String $results value must be one of : raw | object | url
     * @return Mixed
     */
    public function getGallery(String $results = 'raw')
    {
        $galery = $this->get(self::acf_recruiter_gallery_photo, true, 'acf');

        if (!$galery || !isset($galery)) {
            return [];
        }

        switch (strtolower($results)) {
            case 'object':
                if (is_array($galery)) {
                    $galery = array_map(function ($attachmentId) {
                        return [
                            "id"    => $attachmentId,
                            "url"   => wp_get_attachment_url($attachmentId),
                            "title" => get_the_title($attachmentId)
                        ];
                    }, $galery);
                }

                return $galery;
                break;
            case 'raw':
                if (is_array($galery)) {
                    $galery = array_map(function ($attachmentId) {
                        return wp_get_attachment_url($attachmentId);
                    }, $galery);
                }

                return $galery;
                break;
            default:
                return $galery;
                break;
        }
    }

    public function setImage(Mixed $value)
    {
        return $this->set(self::acf_recruiter_image, $value, 'acf');
    }

    /**
     * Set VideoUrl ACF Value function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setVideoUrl(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_video_url, $value, 'acf');
    }

    /**
     * Get VideoUrl function
     *
     * @param boolean $single
     * @return Mixed
     */
    public function getVideoUrl(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_video_url, $single, 'acf');
    }

    /**
     * Set phone code function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setPhoneCode(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_phone_code, $value, 'acf');
    }

    /**
     * Fet Phone Code function
     *
     * @param Bool $single
     * @return mixed
     */
    public function getPhoneCode(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_phone_code, $single, 'acf');
    }

    /**
     * Set Phone Number function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setPhoneNumber(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_phone_number, $value, 'acf');
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
    public function getPhoneNumber(String $result = 'full', Bool $single = true)
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
                return $this->get(self::acf_recruiter_phone_number, $single, 'acf');
                break;
            case 'full':
            default:
                return $this->getPhoneCode() . ' ' . $this->get(self::acf_recruiter_phone_number, $single, 'acf');
                break;
        }
    }

    /**
     * Set Recruiter Country acf function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setCountry(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_country, $value, 'acf');
    }

    /**
     * Get Recruiter Country acf function
     *
     * @param Boolean $single / formatted
     * @return mixed
     */
    public function getCountry(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_country, $single, 'acf');
    }

    /**
     * Set Recruiter Country Code acf function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setCountryCode(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_country_code, $value, 'acf');
    }

    /**
     * Get Recruiter Country Code acf function
     *
     * @param Bool $single
     * @return mixed
     */
    public function getCountryCode(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_country_code, $single, 'acf');
    }

    /**
     * Set Recruiter City acf function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setCity(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_city, $value, 'acf');
    }

    /**
     * Get Recruiter City acf function
     *
     * @param Boolean $single / formatted
     * @return mixed
     */
    public function getCity(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_city, $single, 'acf');
    }

    /**
     * Set Recruiter Street acf function
     *
     * @param Boolean $single / formatted
     * @return mixed
     */
    public function setStreet(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_street, $value, 'acf');
    }

    /**
     * Get Recruiter Street acf function
     *
     * @param Boolean $single / formatted
     * @return mixed
     */
    public function getStreet(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_street, $single, 'acf');
    }

    /**
     * Get Recruiter Post Code acf function
     *
     * @param Boolean $single / formatted
     * @return mixed
     */
    public function setPostCode(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_postcode, $value, 'acf');
    }

    /**
     * Get Recruiter PostCode acf function
     *
     * @param Boolean $single / formatted
     * @return mixed
     */
    public function getPostCode(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_postcode, $single, 'acf');
    }

    /**
     * Set Longitude ACF function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setLongitude(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_longitude, $value, 'acf');
    }

    /**
     * Get Longitude ACF value function
     *
     * @param Bool $single
     * @return mixed
     */
    public function getLongitude(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_longitude, $single, 'acf');
    }

    /**
     * Set Latitude ACF function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setLatitude(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_latitude, $value, 'acf');
    }

    /**
     * Get Latitude ACF value function
     *
     * @param Bool $single
     * @return mixed
     */
    public function getLatitude(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_latitude, $single, 'acf');
    }

    /**
     * Set total employees function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setTotalEmployees(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_employees, $value, 'acf');
    }

    /**
     * Get total employees function
     *
     * @param Bool $single
     * @return mixed
     */
    public function getTotalEmployees(String $result = 'object', Bool $single = true): mixed
    {
        $totalEmployees = $this->get(self::acf_recruiter_employees, $single, 'acf');

        if ($totalEmployees) {
            switch (strtolower($result)) {
                case 'object':
                    return [
                        "value" => $totalEmployees,
                        "label" => $totalEmployees,
                    ];
                    break;
                case 'raw':
                default:
                    return $totalEmployees;
                    break;
            }
        } else {
            return $totalEmployees;
        }
    }

    /**
     * Set sector ACF function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setSector(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_sector, $value, 'acf');
    }

    /**
     * Get sector ACF function
     *
     * @param string $result value must be one of : object | name or label | raw. default is object
     * @param boolean $single
     * @return mixed
     */
    public function getSector(String $result = 'object', Bool $single = true): mixed
    {
        $sectors = $this->get(self::acf_recruiter_sector, $single, 'acf');

        if ($sectors) {
            switch (strtolower($result)) {
                case 'object':
                    $selectedTerms = get_terms([
                        'taxonomy'      => 'sector',
                        'include'       => $sectors ?? [],
                        'hide_empty'    => false
                    ]);

                    $resultTerms = [];
                    foreach ($selectedTerms as $term) {
                        $resultTerms[] = [
                            'label' => $term->name,
                            'value' => $term->term_id,
                        ];
                    }

                    return $resultTerms;
                    break;
                case 'label':
                case 'name':
                    $selectedTerms = get_terms([
                        'taxonomy'      => 'sector',
                        'include'       => $sectors ?? [],
                        'hide_empty'    => false
                    ]);

                    $resultTerms = [];
                    foreach ($selectedTerms as $term) {
                        $resultTerms[] = [
                            'label' => $term->name,
                        ];
                    }

                    return $resultTerms;
                    break;
                case 'raw':
                default:
                    break;
            }
        } else {
            return null;
        }
    }

    /**
     * Set KvK Number ACF function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setKvkNumber(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_kvk_number, $value, 'acf');
    }

    /**
     * Get KvK Number ACF function
     *
     * @param Bool $single
     * @return mixed
     */
    public function getKvkNumber(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_kvk_number, $single, 'acf');
    }

    /**
     * Set BTW Number ACF function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setBtwNumber(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_btw_number, $value, 'acf');
    }

    /**
     * Get BTW Number ACF function
     *
     * @param Bool $single
     * @return mixed
     */
    public function getBtwNumber(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_btw_number, $single, 'acf');
    }

    /**
     * Set Website URL acf function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setWebsiteUrl(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_website_url, $value, 'acf');
    }

    /**
     * Get Website URL acf function
     *
     * @param Bool $single
     * @return mixed
     */
    public function getWebsiteUrl(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_website_url, $single, 'acf');
    }

    /**
     * Set Linkedin URL ACF function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setLinkedinUrl(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_linkedin_url, $value, 'acf');
    }

    /**
     * Get Linkedin URL acf function
     *
     * @param boolean $single
     * @return mixed
     */
    public function getLinkedinUrl(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_linkedin_url, $single, 'acf');
    }


    /**
     * Set Facebook URL acf function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setFacebookUrl(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_facebook_url, $value, 'acf');
    }

    /**
     * Get Facebook URL acf function
     *
     * @param boolean $single
     * @return mixed
     */
    public function getFacebookUrl(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_facebook_url, $single, 'acf');
    }

    /**
     * Set Instagram URL ACF function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setInstagramUrl(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_instagram_url, $value, 'acf');
    }

    /**
     * Get Instagram URL acf function
     *
     * @param boolean $single
     * @return mixed
     */
    public function getInstagramUrl(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_instagram_url, $single, 'acf');
    }

    /**
     * Set  URL ACF function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setTwitterUrl(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_twitter_url, $value, 'acf');
    }

    /**
     * Get Twittert URL acf function
     *
     * @param boolean $single
     * @return mixed
     */
    public function getTwitterUrl(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_twitter_url, $single, 'acf');
    }

    /**
     * Set Short Description ACF value function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setShortDescription(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_short_description, $value, 'acf');
    }

    /**
     * Get Short Description ACF value function
     *
     * @param Bool $single
     * @return mixed
     */
    public function getShortDescription(Bool $single = true): mixed
    {
        return $this->set(self::acf_recruiter_short_description, $single, 'acf');
    }

    /**
     * Set Benefit / secondaryEmploymentConditions ACF value function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setBenefit(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_benefit, $value, 'acf');
    }

    /**
     * Get Benefit / secondaryEmploymentConditions ACF value function
     *
     * @param Bool $single
     * @return mixed
     */
    public function getBenefit(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_benefit, $single, 'acf');
    }

    /**
     * Set is fully registered ACF value function
     *
     * @param Mixed $value
     * @return mixed
     */
    public function setIsFullRegistered(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_is_fully_registered, $value, 'acf');
    }

    /**
     * Get is fully registered ACF value function
     *
     * @param Bool $single
     * @return mixed
     */
    public function getIsFullRegistered(Bool $single = true): mixed
    {
        return $this->get(self::acf_recruiter_is_fully_registered, $single, 'acf');
    }

    /**
     * Get Image function
     *
     * In company model is named as get Thumbnail,
     * that's why here is writter as thumbnail to minimalize confusion.
     *
     * @param string $result
     * @return mixed
     */
    public function getThumbnail(String $result = 'object'): mixed
    {
        $attachment = $this->get(self::acf_recruiter_image, true);

        if ($attachment) {
            switch (strtolower($result)) {
                case 'object':
                    if (!empty($attachment)) {
                        if (is_array($attachment)) {
                            return [
                                'id'    => $attachment['ID'],
                                'title' => $attachment['title'],
                                'url'   => $attachment['url']
                            ];
                        } else if (is_numeric($attachment)) {
                            return [
                                'id'    => $attachment,
                                'title' => get_the_title($attachment),
                                'url'   => wp_get_attachment_url($attachment)
                            ];
                        } else if (is_string($attachment)) {
                            $attachmentID = attachment_url_to_postid($attachment);
                            return [
                                'id'    => $attachmentID,
                                'title' => get_the_title($attachmentID),
                                'url'   => $attachment
                            ];
                        }
                    } else {
                        return NULL;
                    }
                    break;
                case 'url':
                    if (is_array($attachment)) {
                        return $attachment['url'];
                    } else if (is_numeric($attachment)) {
                        return wp_get_attachment_url($attachment);
                    } else if (is_string($attachment)) {
                        return $attachment;
                    }
                    break;
                case 'title':
                    if (is_array($attachment)) {
                        return $attachment['title'];
                    } else if (is_numeric($attachment)) {
                        return get_the_title($attachment);
                    } else if (is_string($attachment)) {
                        $attachmentID = attachment_url_to_postid($attachment);
                        return get_the_title($attachmentID);
                    }
                    break;
                case 'raw':
                default:
                    return $attachment;
                    break;
            }
        }

        return NULL;
    }

    /**
     * Get Image function
     *
     * In company model is named as get Thumbnail,
     * that's why here is writter as thumbnail to minimalize confusion.
     *
     * @param string $result
     * @return mixed
     */
    public function getImage(String $result = 'object'): mixed
    {
        return $this->getThumbnail($result);
    }

    /**
     * Set OTP is verified meta function
     *
     * This is actually set with value :
     * - false  : new user register wait auth/register is hit.
     * - true   : new user validate their otp.
     *
     * @param Bool $value
     * @return mixed
     */
    public function setOTPIsVerified(Bool $value): mixed
    {
        return $this->set(self::meta_user_otp_is_verified, ($value == true ? 1 : 0), 'meta', false);
    }
}
