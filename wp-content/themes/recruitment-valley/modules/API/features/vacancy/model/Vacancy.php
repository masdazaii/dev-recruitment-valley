<?php

namespace Vacancy;

use DateTime;
use Exception;
use Helper;
use Helper\Maphelper;
use WP_Error;
use WP_Post;
use WP_Query;

class Vacancy
{
    public $vacancy = 'vacancy';

    public $vacancy_id;

    public $title;

    public $description;
    public $city;
    public $country;
    public $term;
    public $apply_from_this_platform;
    public $location;
    public $application_process_title;
    public $application_process_description;
    public $application_process_step;
    public $video_url;
    public $facebook_url;
    public $linkedin_url;
    public $instagram_url;
    public $twitter_url;
    public $gallery;
    public $reviews;
    public $is_paid;
    public $salary_start;
    public $salary_end;

    public $thumbnail;
    public $desscription;

    // acf field
    public $acf_description = "description";
    public $acf_term = "term";
    public $acf_placement_city = "placement_city";
    public $acf_is_paid = "is_paid";
    public $acf_apply_from_this_platform = "apply_from_this_platform";
    public $acf_application_process_title = "application_process_title";
    public $acf_application_process_description = "application_process_description";
    public $acf_application_process_step = "application_process_step";
    public $acf_video_url = "video_url";
    public $acf_facebook_url = "facebook_url";
    public $acf_linkedin_url = "linkedin_url";
    public $acf_instagram_url = "instagram_url";
    public $acf_twitter_url = "twitter_url";
    public $acf_gallery = "gallery";
    public $acf_reviews = "reviews";
    // public $acf_country = "country"; // Changed Below
    public $acf_country = 'rv_vacancy_country';
    public $acf_salary_start = "salary_start";
    public $acf_salary_end = "salary_end";
    public $acf_external_url = "external_url";
    public $acf_expired_at = "expired_at";
    public $acf_placement_address = "placement_address";
    public $acf_city_latitude = "city_latitude";
    public $acf_city_longitude = "city_longitude";
    public $acf_placement_address_latitude = "placement_address_latitude";
    public $acf_placement_address_longitude = "placement_address_longitude";
    public $acf_distance_from_city = "distance_from_city";
    public $acf_country_code = "rv_vacancy_country_code";

    /** 01 11 2023 added acf */
    private $acf_rv_vacancy_language = "rv_vacancy_language";

    /** ACF for imported vacancy */
    public $_acf_is_imported = "rv_vacancy_is_imported";
    private $_acf_imported_vacancy_source_id = "rv_vacancy_imported_source_id";
    private $_acf_imported_company_name = "rv_vacancy_imported_company_name";
    private $_acf_imported_company_city = "rv_vacancy_imported_company_city";
    private $_acf_imported_company_country = "rv_vacancy_imported_company_country";
    private $_acf_imported_company_email = "rv_vacancy_imported_company_email";
    private $_acf_imported_company_city_longitude = "rv_vacancy_imported_company_city_longitude";
    private $_acf_imported_company_city_latitude = "rv_vacancy_imported_company_city_latitude";
    private $_acf_imported_company_sector = "rv_vacancy_imported_company_sector";
    private $_acf_imported_company_total_employees = "rv_vacancy_imported_company_total_employees";

    private $_acf_imported_approved_by      = "rv_vacancy_imported_approved_by";
    private $_acf_imported_approved_status  = "rv_vacancy_imported_approval_status";

    /** ACF For Vacancy is for another company (Vonq case) */
    private $_acf_rv_vacancy_is_for_another_company = "rv_vacancy_is_for_another_company";
    private $_acf_rv_vacancy_use_existing_company   = "rv_vacancy_use_existing_company";
    private $_acf_rv_vacancy_selected_company       = "rv_vacancy_selected_company";
    private $_acf_rv_vacancy_custom_company_logo    = "rv_vacancy_custom_company_logo";
    private $_acf_rv_vacancy_custom_company_name    = "rv_vacancy_custom_company_name";
    private $_acf_rv_vacancy_custom_company_sector  = "rv_vacancy_custom_company_sector";
    private $_acf_rv_vacancy_custom_company_email   = "rv_vacancy_custom_company_email";
    private $_acf_rv_vacancy_custom_company_phone_code      = "rv_vacancy_custom_company_phone_code";
    private $_acf_rv_vacancy_custom_company_phone_number    = "rv_vacancy_custom_company_phone_number";
    private $_acf_rv_vacancy_custom_company_total_employees = "rv_vacancy_custom_company_total_employees";
    private $_acf_rv_vacancy_custom_company_description     = "rv_vacancy_custom_company_description";
    private $_acf_rv_vacancy_custom_company_country     = "rv_vacancy_custom_company_country";
    private $_acf_rv_vacancy_custom_company_city        = "rv_vacancy_custom_company_city";
    private $_acf_rv_vacancy_custom_company_address     = "rv_vacancy_custom_company_address";
    private $_acf_vacancy_custom_company_longitude      = "rv_vacancy_custom_company_longitude";
    private $_acf_vacancy_custom_company_latitude       = "rv_vacancy_custom_company_latitude";

    private $_taxonomies = ["sector", "role", "type", "education", "working-hours", "status", "location", "experiences"];

    private const status_slug_open          = 'open';
    private const status_slug_processing    = 'processing';

    /** Vacancy Meta */
    private $_meta_rv_vacancy_unused_data   = 'rv_vacancy_unused_data';
    private $_meta_rv_vacancy_imported_at   = 'rv_vacancy_imported_at';
    public $meta_rv_vacancy_approved_at     = 'rv_vacancy_approved_at';
    public $meta_rv_vacancy_source        = 'rv_vacancy_source';
    public $meta_rv_vacancy_jobfeed_is_expired = 'rv_vacancy_jobfeed_is_expired';
    public $meta_rv_vacancy_original_api_term = 'rv_vacancy_original_api_term';

    public function __construct($vacancy_id = false)
    {
        if ($vacancy_id) {
            $this->vacancy_id = $vacancy_id;
            $this->vacancy = get_post($vacancy_id);
        }
    }

    public function setId($vacancyId)
    {
        $this->vacancy_id = $vacancyId;
    }

    // Setter methods
    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setTitle($term)
    {
        $this->term = $term;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function setApplyFromThisPlatform($apply_from_this_platform)
    {
        $this->apply_from_this_platform = $apply_from_this_platform;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function setApplicationProcessTitle($application_process_title)
    {
        $this->application_process_title = $application_process_title;
    }

    public function setApplicationProcessDescription($application_process_description)
    {
        $this->application_process_description = $application_process_description;
    }

    public function setApplicationProcessStep($application_process_step)
    {
        $steps = [];

        foreach ($application_process_step as $key => $step) {
            array_push($steps, [
                "recruitment_step" => $step
            ]);
        }

        update_field($this->acf_application_process_step, $steps, $this->vacancy_id);
    }

    public function setVideoUrl($video_url)
    {
        return $this->setProp($this->acf_video_url, $video_url);
    }

    public function setFacebookUrl($facebook_url)
    {
        $this->facebook_url = $facebook_url;
    }

    public function setLinkedinUrl($linkedin_url)
    {
        $this->linkedin_url = $linkedin_url;
    }

    public function setInstagramUrl($instagram_url)
    {
        $this->instagram_url = $instagram_url;
    }

    public function setTwitterUrl($twitter_url)
    {
        $this->twitter_url = $twitter_url;
    }

    public function setGallery($gallery)
    {
        $this->gallery = $gallery;
    }

    /**
     * setStatus
     * accepted status => declined , close, open, processing
     *
     * @param  mixed $status
     * @return void
     */
    public function setStatus($status)
    {
        $termExist = term_exists($status, 'status');
        if ($termExist) {
            return wp_set_post_terms($this->vacancy_id, $termExist["term_id"], 'status');
        }
    }

    public function setReviews($reviews)
    {
        // $existing_repeater_data = get_field($this->acf_reviews, $this->vacancy_id, true);
        // Add the new data to the existing repeater data

        // $updated_repeater_data = $existing_repeater_data ? array_merge($existing_repeater_data, $reviews) : $reviews;
        // Update the repeater field with the new data
        return update_field($this->acf_reviews, $reviews, $this->vacancy_id);
    }

    public function setTaxonomy($taxonomies)
    {
        foreach ($taxonomies as $taxonomy => $terms) {
            wp_set_post_terms($this->vacancy_id, $terms, $taxonomy);
        }
    }

    // Getter methods
    public function getDescription()
    {
        return $this->getProp($this->acf_description);
    }

    public function getTitle()
    {
        $vacancy = get_post($this->vacancy_id);
        return $vacancy->post_title;
    }

    public function getTerm()
    {
        return $this->getProp($this->acf_term);
    }

    public function getIsPaid(): bool
    {
        // return $this->getProp($this->acf_is_paid); // changed below due to error when returning null
        return $this->getProp($this->acf_is_paid) ?? false;
    }

    public function getPlacementAddress()
    {
        return $this->getProp($this->acf_placement_address);
    }

    public function getApplyFromThisPlatform()
    {
        return $this->getProp($this->acf_apply_from_this_platform);
    }

    public function getApplicationProcessTitle()
    {
        return $this->getProp($this->acf_application_process_title);
    }

    public function getApplicationProcessDescription()
    {
        return $this->getProp($this->acf_application_process_description);
    }

    public function getApplicationProcessStep()
    {
        $steps = $this->getProp($this->acf_application_process_step);
        if (!is_array($steps)) return [];

        return array_map(function ($step) {
            static $i = 0;
            $result = [
                'id' => $i,
                'title' => $step['recruitment_step']
            ];
            $i++;
            return $result;
        }, $steps);
    }

    public function getIsNew()
    {
        $post_date = $this->getPublishDate();
        return strtotime($post_date) < time();
    }

    public function getVideoUrl()
    {
        return $this->getProp($this->acf_video_url) ?? "";
    }

    public function getFacebookUrl()
    {
        return $this->getProp($this->acf_facebook_url);
    }

    public function getLinkedinUrl()
    {
        return $this->getProp($this->acf_linkedin_url);
    }

    public function getInstagramUrl()
    {
        return $this->getProp($this->acf_instagram_url);
    }

    public function getTwitterUrl()
    {
        return $this->getProp($this->acf_twitter_url);
    }

    public function getGallery($properties = ["id", "title", "url"])
    {
        $galleries = $this->getProp($this->acf_gallery);
        $result = [];

        if ($galleries && is_array($galleries)) {
            foreach ($galleries as $key => $gallery) {
                $single = [];
                foreach ($properties as $key => $property) {
                    $single[$property] = $gallery[$property];
                }

                array_push($result, $single);
            }
        }

        return $result;
    }

    // public function getCity() // Changed below
    public function getCity($result = 'default')
    {
        if ($result === 'object') {
            return [
                "label" => $this->getProp($this->acf_placement_city),
                "value" => $this->getProp($this->acf_placement_city),
            ];
        } else {
            return $this->getProp($this->acf_placement_city);
        }
    }

    public function getCountry($result = 'string')
    {
        // return $this->getProp($this->acf_country); // Changed below

        if (strtolower($result) == 'object') {
            $country = $this->getProp($this->acf_country);
            return [
                'label' => $country,
                'value' => $country
            ];
        } else {
            return $this->getProp($this->acf_country);
        }
    }

    public function getStatus()
    {
        $status = get_the_terms($this->vacancy_id, 'status');

        if (isset($status[0])) {

            // return ['term_id' =>  $status[0]->term_id, 'name' =>  $status[0]->name]; // Changed Below
            return ['term_id' =>  $status[0]->term_id, 'name' =>  $status[0]->name, 'slug' =>  $status[0]->slug]; // Changed Below
        }

        return ['term_id' => 0, 'name' =>  ""];
    }

    public function getReviews()
    {
        $reviews = $this->getProp($this->acf_reviews);

        if (!is_array($reviews)) return [];

        return array_map(function ($review) {
            return [
                "name"      => $review['name'],
                "role"      => $review['role'],
                "review"    => $review['text'],
            ];
        }, $reviews);
    }

    public function getSalaryStart()
    {
        return $this->getProp($this->acf_salary_start);
    }

    public function getTax()
    {
        $taxonomies = [
            [
                "name" => "sector",
            ],
            [
                "name" => "role",
            ],
            [
                "name" => "type",
            ],
            [
                "name" => "education",
            ],
            [
                "name" => "working-hours",
            ],
            [
                "name" => "status",
            ],
            [
                "name" => "location",
            ],
            [
                "name" => "experiences",
            ]
        ];

        $result = [];

        foreach ($taxonomies as $key => $taxonomy) {
            $terms = get_terms([
                "taxonomy" => $taxonomy,
                "object_ids" => $this->vacancy_id,
                "hide_empty" => false
            ]);

            if (count($terms) == 0) {
                $result[$taxonomy["name"]] = [];
                continue;
            }

            foreach ($terms as $key => $term) {
                $result[$taxonomy["name"]][$key]["label"] = $term->name;
                $result[$taxonomy["name"]][$key]["value"] = $term->term_id;
            }
        }

        return $result;
    }

    public function getSocialMedia($socialMedia)
    {
        return $this->getProp($socialMedia . "_url");
    }

    public function getSalaryEnd()
    {
        return $this->getProp($this->acf_salary_end);
    }

    public function getExternalUrl()
    {
        return $this->getProp($this->acf_external_url) ?? null;
    }

    public function getExpiredAt($format = "Y-m-d H:i:s")
    {
        // return $this->getProp($this->acf_expired_at);
        $date = $this->getProp($this->acf_expired_at);
        $date = $date ? date_create($date) : "";
        return $date != "" ? date_format($date, $format) : "";
    }

    public function setProp($acf_field, $value, $repeater = false)
    {
        if ($repeater) {
            switch ($acf_field) {
                case $this->acf_application_process_step:
                    return $this->setApplicationProcessStep($value);
                case $this->acf_reviews:
                    return $this->setReviews($value);
            }
        }

        return update_field($acf_field, $value, $this->vacancy_id);
    }

    public function getAuthor()
    {
        $vacancy = get_post($this->vacancy_id);
        return $vacancy->post_author;
    }

    public function getSlug()
    {
        $vacancy = get_post($this->vacancy_id);
        return $vacancy->post_name;
    }

    public function getPublishDate($format = "Y-m-d H:i:s")
    {
        return get_post_time($format, true, $this->vacancy_id);
    }

    public function getProp($acf_field, $single = true)
    {
        return get_field($acf_field, $this->vacancy_id, $single);
    }

    public function getThumbnail()
    {
        return wp_get_attachment_image_src(get_post_thumbnail_id($this->vacancy_id), "thumbnail")[0] ?? "";
    }

    public function getPropeties()
    {
        return get_object_vars($this);
    }

    public function getTaxonomy($formatted = false)
    {
        $taxonomies = get_post_taxonomies($this->vacancy_id);

        $taxes = wp_get_object_terms(
            $this->vacancy_id,
            $taxonomies,
            [
                "hide_empty" => false,
            ]
        );

        $groupedTax = [];

        foreach ($taxes as $tax) {
            $tempTax = $tax->taxonomy;
            $taxField = [
                "id" => $tax->term_id,
                "slug" => $tax->slug,
                "name" => html_entity_decode($tax->name)
            ];

            if ($formatted) {
                if (isset($groupedTax[$tempTax])) {
                    array_push($groupedTax[$tempTax], $taxField);
                } else {
                    $groupedTax[$tempTax] = [$taxField];
                }
            } else {
                array_push($groupedTax, $taxField);
            }
        }

        return $groupedTax;
    }

    public function storePost($payload)
    {
        $args = [
            "post_title" => $payload["title"],
            "post_author" => $payload["user_id"],
            "post_type" => $this->vacancy,
            "post_status" => "publish"
        ];

        $vacancy = wp_insert_post($args);

        $this->vacancy_id = $vacancy;

        return $vacancy;
    }


    public function getByStatus($status)
    {
        $args = [
            "post_type" => $this->vacancy,
            "post_status" => "publish",
            "tax_query" => [
                [
                    'taxonomy' => $status,
                    'field' => 'slug',
                    'terms' => array($status),
                    'operator' => 'IN'
                ]
            ],
        ];

        $vacancies = get_posts($args);

        return $vacancies;
    }

    public function allSlug()
    {
        $args = array(
            'post_type' => $this->vacancy,
            'posts_per_page' => -1, // Retrieve all posts of the type
            'fields' => 'post_name', // Retrieve only post IDs to improve performance
            'post_status' => "publish"
        );

        $vacancySitemap = get_posts($args);

        return $vacancySitemap;
    }

    public function trash(): int|WP_Error
    {
        $trashed = wp_update_post([
            "ID" => $this->vacancy_id,
            "post_status" => "trash"
        ]);

        return $trashed;
    }

    public function getBySlug(String $slug)
    {
        return get_page_by_path($slug, OBJECT, 'vacancy');
    }

    // public function setCityLongLat(string $city) // Changed Below
    public function setCityLongLat(string $city, Bool $withCompanyAsWell = false)
    {
        $coordinat = Maphelper::generateLongLat($city);
        $lat  = $this->setProp($this->acf_city_latitude, $coordinat["lat"]);
        $long = $this->setProp($this->acf_city_longitude, $coordinat["long"]);

        if ($withCompanyAsWell) {
            $this->setProp($this->_acf_imported_company_city_latitude, $coordinat["lat"]);
            $this->setProp($this->_acf_imported_company_city_longitude, $coordinat["long"]);
        }

        if ($lat && $long) {
            return true;
        } else {
            return false;
        }
    }

    public function getCityLongLat(String $result = 'all')
    {
        if ($this->vacancy_id) {
            if ($result == 'latitude') {
                return $this->getProp($this->acf_city_latitude);
            } else if ($result == 'longitude') {
                return $this->getProp($this->acf_city_longitude);
            } else {
                return [
                    'latitude'  => $this->getProp($this->acf_city_latitude),
                    'longitude' => $this->getProp($this->acf_city_longitude)
                ];
            }
        } else {
            throw new Exception('Please specify vacancy');
        }
    }

    public function setAddressLongLat(string $address)
    {
        $coordinat = Maphelper::generateLongLat($address);
        $this->setProp($this->acf_placement_address_latitude, $coordinat["lat"]);
        $this->setProp($this->acf_placement_address_longitude, $coordinat["long"]);
    }

    public function setPlacementAddressLatitude($latitude)
    {
        $this->setProp($this->acf_placement_address_latitude, $latitude);
    }

    public function setPlacementAddressLongitude($longitude)
    {
        $this->setProp($this->acf_placement_address_longitude, $longitude);
    }

    public function getPlacementAddressLongitude()
    {
        return $this->getProp($this->acf_placement_address_longitude);
    }

    public function getPlacementAddressLatitude()
    {
        return $this->getProp($this->acf_placement_address_latitude);
    }

    public function setDistance($city, $placementAddress)
    {
        $cityCoordinat = Maphelper::generateLongLat($city);
        $placementAddressCoordinat = Maphelper::generateLongLat($placementAddress);

        $distance = Maphelper::calculateDistance($cityCoordinat, $placementAddressCoordinat);

        return $this->setProp($this->acf_distance_from_city, $distance);
    }

    public function getDistance()
    {
        if ($this->vacancy_id) {
            return $this->getProp($this->acf_distance_from_city);
        } else {
            throw new Exception("Please specify vacancy!");
        }
    }

    /**
     * Get All Vacancies function
     *
     * @param array $filters
     * @param array $taxonomyFilters
     * @param array $args -> wp_query arguments
     * @return void
     */
    public function getAllVacancies($filters = [], $taxonomyFilters = [], $args = [])
    {
        $args = $this->_setArguments($args, $filters, $taxonomyFilters = []);
        $vacancies = new WP_Query($args);

        return $vacancies;
    }

    private function _setArguments($args, $filters, $taxonomyFilters)
    {
        if (empty($args)) {
            $args = [
                "post_type" => $this->vacancy,
                "posts_per_page" => $filters['postPerPage'] ?? -1,
                "offset" => $filters['offset'] ?? 0,
                "orderby" => $filters['orderBy'] ?? "date",
                "order" => $filters['sort'] ?? 'ASC',
                "post_status" => "publish",
                "meta_query" => [
                    "relation" => "AND",
                    [
                        'key' => 'expired_at',
                        'value' => date("Y-m-d H:i:s"),
                        'compare' => '>',
                        'type' => "DATE"
                    ],
                ],
                "tax_query" => [
                    "relation" => 'AND',
                    [
                        'taxonomy' => 'status',
                        'field'     => 'slug',
                        'terms'     => 'open',
                        'compare'  => 'IN'
                    ]
                ]
            ];
        }

        /** Set tax_query */
        if (!empty($taxonomyFilters)) {
            foreach ($taxonomyFilters as $key => $value) {
                if ($value && $value !== null && !empty($value)) {
                    $args['tax_query'][1]['relation'] = 'OR';

                    array_push($args['tax_query'][1], [
                        'taxonomy' => $key,
                        'field'    => 'term_id',
                        'terms'    => $value,
                        'compare'  => 'IN'
                    ]);
                }
            }
        }

        /** Set meta query */
        // If salaryStart and salaryEnd exist + more than 0
        if (array_key_exists('salaryStart', $filters) || array_key_exists('salaryEnd', $filters)) {
            if ($filters['salaryStart'] >= 0 && $filters['salaryStart'] >= 0 && $filters['salaryEnd'] !== null && $filters['salaryEnd'] > 0) {
                if (!array_key_exists('meta_query', $args)) {
                    $args['meta_query'] = [
                        "relation" => 'OR'
                    ];
                }

                array_push($args['meta_query'], [
                    'relation' => 'AND',
                    [
                        'key' => 'salary_start',
                        'value' => $filters['salaryStart'],
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    ],
                    [
                        'key' => 'salary_end',
                        'value' => $filters['salaryEnd'],
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ],
                ]);
                array_push($args['meta_query'], [
                    'relation' => 'AND',
                    [
                        'key' => 'salary_start',
                        'value' => [$filters['salaryStart'], $filters['salaryEnd']],
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN',
                    ],
                    [
                        'key' => 'salary_end',
                        'value' => $filters['salaryEnd'],
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ],
                ]);
            } else if ($filters['salaryStart'] >= 0 || $filters['salaryEnd'] >= 0) { // if only one of them is filled
                if (!array_key_exists('meta_query', $args)) {
                    $args['meta_query'] = [
                        "relation" => 'AND'
                    ];
                }

                if ($filters['salaryStart'] >= 0 && $filters['salaryEnd'] === null) { // if start is filled but other is empty
                    array_push($args['meta_query'], [
                        'key' => 'salary_start',
                        'value' => $filters['salaryStart'],
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    ]);
                    array_push($args['meta_query'], [
                        'key' => 'salary_end',
                        'value' => $filters['salaryStart'],
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    ]);
                } else if ($filters['salaryEnd'] !== null) { // vice versa
                    array_push($args['meta_query'], [
                        'key' => 'salary_start',
                        'value' => $filters['salaryEnd'],
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ]);
                    array_push($args['meta_query'], [
                        'key' => 'salary_end',
                        'value' => $filters['salaryEnd'],
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ]);
                }
            }
        }

        // filter by city
        if (array_key_exists('city', $filters) && $filters["city"]) {
            array_push($args['meta_query'], [
                'key' => 'placement_city',
                'value' => $filters['city'],
                'compare' => '=',
            ]);
        }

        if (array_key_exists('radius', $filters) && $filters["radius"]) {
            array_push($args['meta_query'], [
                'key' => 'distance_from_city',
                'value' => $filters['radius'],
                'compare' => '<',
                "type" => "numeric",
            ]);
        }

        return $args;
    }

    /** set distance acf Function */
    public function setCoordinateDistance($cityCoordinate, $placementAddressCoordinate)
    {
        $distance = Maphelper::calculateDistance($cityCoordinate, $placementAddressCoordinate);
        error_log(json_encode($distance));

        return $this->setProp($this->acf_distance_from_city, $distance);
    }


    public function getCountryCode()
    {
        return $this->getProp($this->acf_country_code, true);
    }

    /** Method for related to imported vacancy start here */
    public function setImportedCompanyCityLongLat(string $city)
    {
        $coordinat = Maphelper::generateLongLat($city);
        $this->setProp($this->_acf_imported_company_city_longitude, $coordinat["long"]);
        $this->setProp($this->_acf_imported_company_city_latitude, $coordinat["lat"]);
    }

    public function checkImported()
    {
        return $this->getProp($this->_acf_is_imported) == 1 ? true : false;
    }

    public function getImportedCompanyName()
    {
        return $this->getProp($this->_acf_imported_company_name);
    }

    public function getImportedCompanyCity()
    {
        return $this->getProp($this->_acf_imported_company_city);
    }

    public function getImportedCompanyCountry()
    {
        return $this->getProp($this->_acf_imported_company_country);
    }

    public function getImportedCompanyEmail()
    {
        return $this->getProp($this->_acf_imported_company_email);
    }

    public function getImportedCompanyLongitude()
    {
        return $this->getProp($this->_acf_imported_company_city_longitude);
    }

    public function getImportedCompanyLatitude()
    {
        return $this->getProp($this->_acf_imported_company_city_latitude);
    }

    public function getImportedSource()
    {
        return $this->getterMeta($this->meta_rv_vacancy_source);
    }

    public function getImportedSourceID()
    {
        return $this->getProp($this->_acf_imported_vacancy_source_id);
    }

    public function getImportedCompanySector()
    {
        $sectors = $this->getProp($this->_acf_imported_company_sector);
        if ($sectors && is_array($sectors)) {
            $terms = get_terms([
                'taxonomy'   => 'sector',
                'hide_empty' => false,
                'term_taxonomy_id' => $sectors
            ]);

            if ($terms instanceof \WP_Error) {
                throw $terms;
            } else if ($terms && is_array($terms)) {
                $result = [];
                foreach ($terms as $term) {
                    $result[] = [
                        'value' => $term->term_id,
                        'slug'  => $term->slug,
                        'label' => $term->name,
                    ];
                }
                return $result;
            }
        } else {
            return $sectors;
        }
    }

    public function setImportedCompanySector($value)
    {
        return $this->setProp($this->_acf_imported_company_sector, $value);
    }

    public function getImportedCompanyTotalEmployees()
    {
        return $this->getProp($this->_acf_imported_company_total_employees, true);
    }

    // public function getImportedUnusedData()
    // {
    //     return $this->getterMeta($this->_meta_rv_vacancy_unused_data);
    // }

    public function checkIfJobfeedExpired()
    {
        $isExpired = $this->getterMeta($this->meta_rv_vacancy_jobfeed_is_expired, true);
        return $isExpired;
    }

    /**
     * Get imported vacancy function
     *
     * @param array $filters
     * @return void
     */
    public function getImportedVacancy($filters = [], $taxonomyFilters = [], $args = [])
    {
        $args = $this->_setImportedArguments($args, $filters, $taxonomyFilters = []);
        $vacancies = new WP_Query($args);

        return $vacancies;
    }

    private function _setImportedArguments($args = [], $filters = [], $taxonomyFilters = [])
    {
        if (empty($args)) {
            $args = [
                "post_type" => $this->vacancy,
                "posts_per_page" => $filters['postPerPage'] ?? -1,
                "offset" => $filters['offset'] ?? 0,
                "orderby" => $filters['orderBy'] ?? "date",
                "order" => $filters['sort'] ?? 'ASC',
                "post_status" => "publish",
                "meta_query" => [
                    "relation" => "AND",
                    [
                        'key'       => $this->_acf_is_imported,
                        'value'     => 1,
                        'compare'   => '=',
                    ],
                ],
                "tax_query" => [
                    "relation" => 'AND',
                ]
            ];
        } else {
            $arguments = [
                "post_type" => $this->vacancy,
                "posts_per_page" => $filters['postPerPage'] ?? -1,
                "offset" => $filters['offset'] ?? 0,
                "orderby" => $filters['orderBy'] ?? "date",
                "order" => $filters['sort'] ?? 'ASC',
                "post_status" => "publish",
                [
                    'key'       => $this->_acf_is_imported,
                    'value'     => 1,
                    'compare'   => '=',
                ],
                "tax_query" => [
                    "relation" => 'AND',
                ]
            ];
            foreach ($args as $key => $value) {
                $arguments[$key] = $value;
            }
            $args = $arguments;
        }

        /** Set tax_query */
        if (!empty($taxonomyFilters)) {
            foreach ($taxonomyFilters as $key => $value) {
                if ($value && $value !== null && !empty($value)) {
                    $args['tax_query'][1]['relation'] = 'OR';

                    array_push($args['tax_query'][1], [
                        'taxonomy' => $key,
                        'field'    => 'term_id',
                        'terms'    => $value,
                        'compare'  => 'IN'
                    ]);
                }
            }
        }

        /** Set search query */
        if (!empty($filters['search'])) {
            $args['s'] = $filters['search'];
        }

        return $args;
    }

    public function getVacancies($filters = [], $args = [])
    {
        $args = $this->_setVacancyArguments($args, $filters);
        $vacancies = new WP_Query($args);

        return $vacancies;
    }

    private function _setVacancyArguments($args = [], $filters = [])
    {
        if (empty($args)) {
            $args = [
                "post_type"         => $this->vacancy,
                "posts_per_page"    => $filters['postPerPage'] ?? -1,
                "offset"            => $filters['offset'] ?? 0,
                "orderby"           => $filters['orderBy'] ?? "post_date",
                "order"             => $filters['sort'] ?? 'ASC',
                "post_status"       => "publish",
            ];

            if (isset($filters['orderBy'])) {
                $args['orderby'] = $filters['orderBy'];
            }

            if (isset($filters['author'])) {
                $args['author__in'] = [$filters['author']];
            }
        }

        if (!empty($filters)) {
            if (array_key_exists('meta', $filters)) {
                $args['meta_query'] = $filters['meta'];
            }

            if (array_key_exists('taxonomy', $filters)) {
                $args['tax_query'] = $filters['taxonomy'];
            }

            if (array_key_exists('postPerPage', $filters)) {
                $args['postPerPage'] = $filters['postPerPage'];
            }

            if (array_key_exists('offset', $filters)) {
                $args['offset'] = $filters['offset'];
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

            if (array_key_exists('post_status', $filters)) {
                $args['post_status'] = $filters['post_status'];
            }

            if (array_key_exists('author', $filters)) {
                // $args['author'] = $filters['author'];
                $args['author__in'] = $filters['author'];
            }

            if (array_key_exists('in', $filters)) {
                $args['post__in'] = $filters['in'];
            }

            if (array_key_exists('search', $filters)) {
                $args['s'] = $filters['search'];
            } else if (array_key_exists('s', $filters)) {
                $args['s'] = $filters['s'];
            }
        }

        return $args;
    }

    public function setApprovedStatus($value)
    {
        return $this->setProp($this->_acf_imported_approved_status, $value);
    }

    public function getApprovedStatus($result = 'object')
    {
        if ($result == 'label') {
            $value = $this->getProp($this->_acf_imported_approved_status, true);
            if ($value && !empty($value)) {
                if (is_array($value)) {
                    return $value['label'];
                } else {
                    return $value;
                }
            } else {
                return '';
            }
        } else {
            return $this->getProp($this->_acf_imported_approved_status, true);
        }
    }

    public function setApprovedBy($value)
    {
        return $this->setProp($this->_acf_imported_approved_by, $value);
    }

    public function getApprovedBy()
    {
        return $this->getProp($this->_acf_imported_approved_by, true);
    }

    public function getApprovedAt($format = 'Y-m-d H:i:s')
    {
        $approvedAt = $this->getterMeta($this->meta_rv_vacancy_approved_at);
        if ($approvedAt) {
            return date($format, strtotime($approvedAt));
        } else {
            return false;
        }
    }

    public function setApprovedAt($value)
    {
        if ($value == 'now') {
            $value = new DateTime("now");
            $value = $value->format('Y-m-d H:i:s');
        }

        return $this->setterMeta($this->meta_rv_vacancy_approved_at, $value);
    }

    public function getterMeta($key, $single = true)
    {
        if ($this->vacancy_id) {
            return get_post_meta($this->vacancy_id, $key, $single);
        } else {
            throw new Exception('Please specify vacancy!');
        }
    }

    public function setterMeta($key, $value)
    {
        return update_post_meta($this->vacancy_id, $key, $value);
    }

    public function getImportedAt($format = 'Y-m-d H:i:s')
    {
        if ($this->vacancy_id) {
            $importedAt = $this->getterMeta($this->_meta_rv_vacancy_imported_at, true);
            if ($importedAt) {
                return date($format, strtotime($importedAt));
            }
            return $importedAt;
        } else {
            throw new Exception('Please specify vacancy!');
        }
    }

    public function getImportedUnusedData()
    {
        return $this->getterMeta($this->_meta_rv_vacancy_unused_data, true);
    }
    /** Method for related to imported vacancy end here */

    public function getApplicants($filters = [])
    {
        if (isset($this->vacancy_id) && !empty($this->vacancy_id)) {
            $applicants = get_posts([
                "post_type"     => "applicants",
                "post_status"   => "publish",
                "posts_per_page" => $filters["postPerPage"],
                "offset"        => $filters['offset'],
                "orderby"       => $filters["orderby"],
                "order"         => $filters["order"],
                "meta_query"    => [
                    "relation"  => "AND",
                    [
                        "key"   => "applicant_vacancy",
                        "value" => $this->vacancy_id,
                        "compare" => "="
                    ]
                ]
            ]);

            return $applicants;
        } else {
            throw new Exception('Specify the vacancy');
        }
    }

    /**
     * Get selected term in spesific taxonomy function
     *
     * @param String $taxonomy
     * @param String $formatted
     * @return Array if $formatted true will return array associative if false return array of object.
     */
    public function getSelectedTerm($taxonomy, $formatted = null)
    {
        if (!isset($this->vacancy_id) || empty($this->vacancy_id)) {
            throw new Exception('Please specify vacancy id!');
        }

        if (!in_array($taxonomy, $this->_taxonomies)) {
            throw new Exception('Taxonomy didn\'t exists in this post type!');
        }

        if (!empty($formatted)) {
            $terms = get_the_terms($this->vacancy_id, $taxonomy);
            if ($terms) {
                if ($terms instanceof WP_Error) {
                    throw new Exception($terms->get_error_message());
                } else {
                    $result = [];
                    foreach ($terms as $term) {
                        if ($formatted === 'id') {
                            $result[] = $term->term_id;
                        } else if ($formatted === 'slug') {
                            $result[] = $term->slug;
                        } else {
                            $result[] = [
                                'term_id'   => $term->term_id,
                                'name'      => $term->name,
                                'slug'      => $term->slug
                            ];
                        }
                    }
                    return $result;
                }
            }
        } else {
            return get_the_terms($this->vacancy_id, $taxonomy);
        }
    }

    public function setVacancyTerms($taxonomy, $value)
    {
        if (taxonomy_exists($taxonomy)) {
            if (is_array($value)) {
                return wp_set_post_terms($this->vacancy_id, $value, $taxonomy);
            } else if (!is_object($value)) {
                $termExist = term_exists($value, $taxonomy);
                if ($termExist) {
                    return wp_set_post_terms($this->vacancy_id, $termExist["term_id"], $taxonomy);
                } else {
                    return false;
                }
            }
        } else {
            throw new Exception('Taxonomy not found!');
        }
    }

    public function setEmptyVacancyTerms($taxonomy, $value)
    {
        if (taxonomy_exists($taxonomy)) {
            return wp_set_post_terms($this->vacancy_id, '', $taxonomy);
        } else {
            throw new Exception('Taxonomy not found!');
        }
    }

    public function getPostStatus()
    {
        if ($this->vacancy_id) {
            $vacancy = get_post($this->vacancy_id);
            if ($vacancy) {
                return $vacancy->post_status;
            }
        } else {
            throw new Exception('Please specify vacancy!');
        }
    }

    /** 02 11 2023 : Added Function */
    public function getLanguage()
    {
        if ($this->vacancy_id) {
            return $this->getProp($this->acf_rv_vacancy_language, true);
        } else {
            throw new Exception('Please specify vacancy!');
        }
    }

    public function setLanguage($value)
    {
        if ($this->vacancy_id) {
            return $this->setProp($this->acf_rv_vacancy_language, $value);
        } else {
            throw new Exception('Please specify vacancy!');
        }
    }

    /** Vacancy for another company related */
    public function checkIsForAnotherCompany()
    {
        return $this->getProp($this->_acf_rv_vacancy_is_for_another_company) == 1 ? true : false;
    }

    /**
     * Get selected company function
     *
     * This is for case where vacancy is inputed for another company.
     * ACF only has value if the acf : rv_vacancy_is_for_another_company is true.
     *
     * example case: Vonq marketing company
     *
     * @return void
     */
    public function getSelectedCompany()
    {
        if ($this->vacancy_id) {
            return $this->getProp($this->_acf_rv_vacancy_selected_company, true);
        } else {
            throw new Exception('Please specify the vacancy!');
        }
    }

    public function checkUseExistingCompany()
    {
        if ($this->vacancy_id) {
            return $this->getProp($this->_acf_rv_vacancy_use_existing_company, true) == 1 ? true : false;
            // return $this->getProp($this->_acf_rv_vacancy_use_existing_company, true);
        } else {
            throw new Exception('Please specify the vacancy!');
        }
    }

    public function getCustomCompanyName()
    {
        if ($this->vacancy_id) {
            return $this->getProp($this->_acf_rv_vacancy_custom_company_name, true);
        } else {
            throw new Exception('Please specify the vacancy!');
        }
    }

    public function getCustomCompanyDescription()
    {
        if ($this->vacancy_id) {
            return $this->getProp($this->_acf_rv_vacancy_custom_company_description, true);
        } else {
            throw new Exception('Please specify the vacancy!');
        }
    }

    public function getCustomCompanyLogo($result = 'object')
    {
        if ($this->vacancy_id) {
            if ($result == 'url') {
                $logo = $this->getProp($this->_acf_rv_vacancy_custom_company_logo, true);
                if ($logo) {
                    return $logo['url'];
                } else {
                    return false;
                }
            } else {
                $logo = $this->getProp($this->_acf_rv_vacancy_custom_company_logo, true);
                if ($logo) {
                    return [
                        'id' => $logo['id'],
                        'title' => $logo['title'],
                        'url' => $logo['url'],
                    ];
                }
                return $logo;
            }
        } else {
            throw new Exception('Please specify the vacancy!');
        }
    }

    public function getCustomCompanySector()
    {
        if ($this->vacancy_id) {
            $sectors = $this->getProp($this->_acf_rv_vacancy_custom_company_sector);
            if ($sectors && is_array($sectors)) {
                $terms = get_terms([
                    'taxonomy'   => 'sector',
                    'hide_empty' => false,
                    'term_taxonomy_id' => $sectors
                ]);

                if ($terms instanceof \WP_Error) {
                    throw $terms;
                } else if ($terms && is_array($terms)) {
                    $result = [];
                    foreach ($terms as $term) {
                        $result[] = [
                            'value'  => $term->term_id,
                            'label'  => $term->name,
                        ];
                    }
                    return $result;
                }
            } else {
                return $sectors;
            }
        } else {
            throw new Exception('Please specify the vacancy!');
        }
    }

    public function getCustomCompanyEmail()
    {
        if ($this->vacancy_id) {
            return $this->getProp($this->_acf_rv_vacancy_custom_company_email, true);
        } else {
            throw new Exception('Please specify the vacancy!');
        }
    }

    public function getCustomCompanyPhoneCode($result = 'array')
    {
        if ($this->vacancy_id) {
            $phoneCode = $this->getProp($this->_acf_rv_vacancy_custom_company_phone_code, true);
            if ($phoneCode) {
                if ($result == 'label') {
                    if (is_array($phoneCode)) {
                        return $phoneCode['label'];
                    } else {
                        return $phoneCode;
                    }
                } else if ($result == 'value') {
                    if (is_array($phoneCode)) {
                        return $phoneCode['value'];
                    } else {
                        return $phoneCode;
                    }
                } else {
                    return $phoneCode;
                }
            }
        } else {
            throw new Exception('Please specify the vacancy!');
        }
    }

    public function getCustomCompanyPhoneNumber()
    {
        if ($this->vacancy_id) {
            return $this->getProp($this->_acf_rv_vacancy_custom_company_phone_number, true);
        } else {
            throw new Exception('Please specify the vacancy!');
        }
    }

    public function getCustomCompanyTotalEmployees($result = 'array')
    {
        if ($this->vacancy_id) {
            $totalEmplooyees = $this->getProp($this->_acf_rv_vacancy_custom_company_total_employees, true);
            if ($totalEmplooyees) {
                if ($result == 'label') {
                    if (is_array($totalEmplooyees)) {
                        return $totalEmplooyees['label'];
                    } else {
                        return $totalEmplooyees;
                    }
                } else if ($result == 'value') {
                    if (is_array($totalEmplooyees)) {
                        return $totalEmplooyees['value'];
                    } else {
                        return $totalEmplooyees;
                    }
                } else {
                    return $totalEmplooyees;
                }
            }
        } else {
            throw new Exception('Please specify the vacancy!');
        }
    }

    /**
     * Get custom company country function
     * this acf only return value and
     * if you can please do not change the return value to array.
     *
     * @param string $result
     * @return mixed
     */
    public function getCustomCompanyCountry($result = 'array')
    {
        if ($this->vacancy_id) {
            $value = $this->getProp($this->_acf_rv_vacancy_custom_company_country, true);
            if ($value) {
                if ($result == 'label') {
                    return $value;
                } else if ($result == 'value') {
                    return $value;
                } else {
                    return [
                        'value' => $value,
                        'label' => $value
                    ];
                }
            } else {
                return $value;
            }
        } else {
            throw new Exception('Please specify the vacancy!');
        }
    }

    /**
     * Get custom company country function
     * this acf only return value and
     * if you can please do not change the return value to array.
     *
     * @param string $result
     * @return mixed
     */
    public function getCustomCompanyCity($result = 'array')
    {
        if ($this->vacancy_id) {
            $value = $this->getProp($this->_acf_rv_vacancy_custom_company_city, true);
            if ($value) {
                if ($result == 'label') {
                    return $value;
                } else if ($result == 'value') {
                    return $value;
                } else {
                    return [
                        'value' => $value,
                        'label' => $value
                    ];
                }
            } else {
                return $value;
            }
        } else {
            throw new Exception('Please specify the vacancy!');
        }
    }

    public function getCustomCompanyAddress()
    {
        if ($this->vacancy_id) {
            return $this->getProp($this->_acf_rv_vacancy_custom_company_address, true);
        } else {
            throw new Exception('Please specify vacancy!');
        }
    }

    public function getCustomCompanyCoordinate($result = 'all')
    {
        if ($this->vacancy_id) {
            if ($result == 'latitude') {
                return $this->getProp($this->_acf_vacancy_custom_company_latitude, true);
            } else if ($result == 'longitude') {
                return $this->getProp($this->_acf_vacancy_custom_company_longitude, true);
            } else {
                return [
                    'latitude' => $this->getProp($this->_acf_vacancy_custom_company_latitude, true),
                    'longitude' => $this->getProp($this->_acf_vacancy_custom_company_longitude, true)
                ];
            }
        } else {
            throw new Exception('Please specify vacancy!');
        }
    }

    public function setCustomCompanyLatitude($value)
    {
        if ($this->vacancy_id) {
            return $this->setProp($this->_acf_vacancy_custom_company_longitude, $value);
        } else {
            throw new Exception('Please specify vacancy!');
        }
    }

    public function setCustomCompanyLongitude($value)
    {
        if ($this->vacancy_id) {
            return $this->setProp($this->_acf_vacancy_custom_company_latitude, $value);
        } else {
            throw new Exception('Please specify vacancy!');
        }
    }

    /**
     * Check if vacancy status is open function
     *
     * @return boolean
     */
    public function isOpen()
    {
        if ($this->vacancy_id) {
            $status = $this->getStatus();
            if ($status) {
                if (isset($status['slug'])) {
                    if ($status['slug'] == self::status_slug_open) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            throw new Exception('Please specify vacancy!');
        }
    }

    /**
     * Set property original api term that will assign to taxonomy / term function
     *
     * @param array $data
     * @return mixed
     */
    public function setVacancyOriginalCategory(array $data): mixed
    {
        if ($this->vacancy_id) {
            return $this->setterMeta($this->meta_rv_vacancy_original_api_term, $data);
        } else {
            throw new Exception('Please specify vacancy!');
        }
    }

    /**
     * Get property original api term that will assign to taxonomy / term function
     *
     * @param array $data
     * @return mixed
     */
    public function getVacancyOriginalCategory($single = true): mixed
    {
        if ($this->vacancy_id) {
            return $this->getterMeta($this->meta_rv_vacancy_original_api_term, $single);
        } else {
            throw new Exception('Please specify vacancy!');
        }
    }

    /**
     * Select vacancy by status and userID function
     *
     * @param String $status
     * @param Int $userID
     * @return mixed
     */
    public function selectVacancyByStatus(String $status, Int $userID): mixed
    {
        $args = [
            "post_type"     => $this->vacancy,
            "author__in"    => [$userID],
            "posts_per_page" => -1,
            "tax_query" => [
                [
                    'taxonomy'  => 'status',
                    'field'     => 'slug',
                    'terms'     => array($status),
                    'operator'  => 'IN'
                ],
            ],
        ];

        $vacancies = new WP_Query($args);
        return $vacancies;
    }
}
