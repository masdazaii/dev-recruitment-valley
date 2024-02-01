<?php

namespace Model;

use Exception;
use Model\Company;
use WP_Error;
use WP_User;
use WP_User_Query;

class CompanyRecruiter extends BaseModel
{
    protected $recruiter;
    private const allowed_role = ["recruiter"];

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
                $this->recruiter    = get_user_by('id', $this->id);
                if ($this->recruiter) {
                    if ($this->recruiter instanceof WP_Error) {
                        throw $this->recruiter;
                    } else if ($this->recruiter instanceof WP_User) {
                        if (!in_array($this->recruiter->roles[0], self::allowed_role)) {
                            trigger_error("Role not allowed in recruiter model!", E_USER_WARNING);
                        }

                        /** Make WP_User data as property */
                        foreach ($this->properties as $property) {
                            switch ($property) {
                                case 'email':
                                case 'user_email':
                                    $this->{'email'} = $this->recruiter->user_email;
                                    break;
                                case 'role':
                                case 'roles':
                                    $this->{'role'} = $this->recruiter->roles[0];
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

                $this->recruiter    = $user;
                $this->id           = $user->ID;

                /** Make WP_User data as property */
                foreach ($this->properties as $property) {
                    switch ($property) {
                        case 'email':
                        case 'user_email':
                            $this->{'email'} = $this->recruiter->user_email;
                            break;
                        case 'role':
                        case 'roles':
                            $this->{'role'} = $this->recruiter->roles[0];
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
                "role"      => 'recruiter'
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

    public function setVideoUrl(Mixed $value)
    {
        return $this->set(self::acf_recruiter_video_url, $value, 'acf');
    }

    public function setPhoneCode(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_phone_code, $value, 'acf');
    }

    public function setPhoneNumber(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_phone_number, $value, 'acf');
    }

    public function setCountry(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_country, $value, 'acf');
    }

    public function setCountryCode(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_country_code, $value, 'acf');
    }

    public function setCity(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_city, $value, 'acf');
    }

    public function setStreet(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_street, $value, 'acf');
    }

    public function setPostCode(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_postcode, $value, 'acf');
    }

    public function setLongitude(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_longitude, $value, 'acf');
    }

    public function setLatitude(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_latitude, $value, 'acf');
    }

    public function setTotalEmployees(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_employees, $value, 'acf');
    }

    public function setSector(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_sector, $value, 'acf');
    }

    public function setKvkNumber(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_kvk_number, $value, 'acf');
    }

    public function setBtwNumber(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_btw_number, $value, 'acf');
    }

    public function setWebsiteUrl(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_website_url, $value, 'acf');
    }

    public function setLinkedinUrl(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_linkedin_url, $value, 'acf');
    }

    public function setFacebookUrl(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_facebook_url, $value, 'acf');
    }

    public function setInstagramUrl(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_instagram_url, $value, 'acf');
    }

    public function setTwitterUrl(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_twitter_url, $value, 'acf');
    }

    public function setShortDescription(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_short_description, $value, 'acf');
    }

    public function setBenefit(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_benefit, $value, 'acf');
    }

    public function setIsFullRegistered(Mixed $value): mixed
    {
        return $this->set(self::acf_recruiter_is_fully_registered, $value, 'acf');
    }
}
