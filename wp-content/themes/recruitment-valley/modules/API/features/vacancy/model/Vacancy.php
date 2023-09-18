<?php

namespace Vacancy;

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

    /** ACF for imported vacancy */
    private $_acf_is_imported = "rv_vacancy_is_imported";
    private $_acf_imported_company_name = "rv_vacancy_imported_company_name";
    private $_acf_imported_company_city = "rv_vacancy_imported_company_city";
    private $_acf_imported_company_country = "rv_vacancy_imported_company_country";
    private $_acf_imported_company_email = "rv_vacancy_imported_company_email";
    private $_acf_imported_company_city_longitude = "rv_vacancy_imported_company_city_longitude";
    private $_acf_imported_company_city_latitude = "rv_vacancy_imported_company_city_latitude";

    public function __construct($vacancy_id = false)
    {
        if ($vacancy_id) {
            $this->vacancy_id = $vacancy_id;
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
        return $this->getProp($this->acf_is_paid);
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

            return ['term_id' =>  $status[0]->term_id, 'name' =>  $status[0]->name];
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
                "object_ids" => $this->vacancy_id
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
                "name" => $tax->name
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
        $this->setProp($this->acf_city_latitude, $coordinat["lat"]);
        $this->setProp($this->acf_city_longitude, $coordinat["long"]);

        if ($withCompanyAsWell) {
            $this->setProp($this->_acf_imported_company_city_latitude, $coordinat["lat"]);
            $this->setProp($this->_acf_imported_company_city_longitude, $coordinat["long"]);
        }
    }

    public function setAddressLongLat(string $address)
    {
        $coordinat = Maphelper::generateLongLat($address);
        $this->setProp($this->acf_placement_address_latitude, $coordinat["lat"]);
        $this->setProp($this->acf_placement_address_longitude, $coordinat["long"]);
    }

    public function setDistance($city, $placementAddress)
    {
        $cityCoordinat = $coordinat = Maphelper::generateLongLat($city);
        $placementAddressCoordinat = Maphelper::generateLongLat($placementAddress);

        $distance = Maphelper::calculateDistance($cityCoordinat, $placementAddressCoordinat);

        return $this->setProp($this->acf_distance_from_city, $distance);
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
                "order" => $filters['sort'] ?? ($filters['orderBy'] && $filters['orderBy'] == 'title' ? 'aaaaa' : 'dddddd'),
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

        return $this->setProp($this->acf_distance_from_city, $distance);
    }

    /** Method for related to imported vacancy start here */
    public function setImportedCompanyCityLongLat(string $city)
    {
        $coordinat = Maphelper::generateLongLat($city);
        $this->setProp($this->_acf_imported_company_city_longitude, $coordinat["long"]);
        $this->setProp($this->_acf_imported_company_city_latitude, $coordinat["lat"]);
    }

    /** Method for related to imported vacancy start here */
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
}
