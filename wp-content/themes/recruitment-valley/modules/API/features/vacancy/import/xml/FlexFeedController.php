<?php

namespace Vacancy\Import\Xml;

use Error;
use Exception;
use Vacancy\Vacancy;
use WP_Error;
use Helper\StringHelper;

class FlexFeedController
{

    // private $sample_dir = THEME_URL . '/assets/sample/xml/vacancies.xml';
    private $sample_dir = THEME_DIR . '/assets/sample/flexfeed.xml';
    private $_sourceUrl;
    private $_taxonomyKey;
    private $_terms;

    public function __construct()
    {
        $this->_taxonomyKey = [
            'hoursPerWeek'  => 'working-hours',
            'education'     => 'education',
            'jobtype'       => 'type',
            'experience'    => 'experiences',
            'sector'        => 'sector',
            'role'          => 'role',
            'location'      => 'location',
            'status'        => 'status'
        ];
    }

    public function parse()
    {
        $vacancies = [];

        error_log($this->sample_dir);

        try {
            if (file_exists($this->sample_dir)) {
                $vacancies = simplexml_load_file($this->sample_dir);
            }

            return [
                "status" => 200,
                "message" => "success get vacancies",
                "data" => $vacancies,
            ];
        } catch (\Throwable $th) {
            return [
                "status" => 400,
                "message" => $th->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                "status" => 400,
                "message" => $e->getMessage()
            ];
        } catch (\WP_Error $error) {
            return [
                "status" => 400,
                "message" => $error->get_error_message()
            ];
        }
    }

    public function validate()
    {
    }

    public function save()
    {
    }

    public function import($limit = 'all', $offset = 0)
    {
        /** Get All terms */
        $this->_getTerms();

        /** Parse and store data */
        $this->_parse($limit, $offset);
    }

    private function _parse($limit, $start)
    {
        /** Get Data */
        if ($this->_sourceUrl && !empty($this->_sourceUrl)) {
            /** Get form API */
            $data = [];
        } else {
            if ($this->sample_dir && !empty($this->sample_dir)) {
                if (file_exists($this->sample_dir)) {
                    $data = simplexml_load_file($this->sample_dir);
                } else {
                    error_log($this->sample_dir);
                    throw new Exception("Sample file not found!");
                }
            } else {
                throw new Exception("No sample file provided!");
            }
        }

        /** Set limit and offset */
        if ($limit !== 'all') {
            if (!is_numeric($limit)) {
                $limit = count($data);
            }
        } else {
            $limit = count($data);
        }

        $limit = $start + $limit;

        /** Get RV Administrator User data */
        $rvAdmin = get_user_by('email', 'adminjob@recruitmentvalley.com');

        /** wp_insert_post arguments */
        $arguments = [
            'post_type' => 'vacancy',
            'post_status' => 'publish',
            'post_author' => $rvAdmin->ID
        ];

        $payload = [];
        $taxonomy = [];

        /** Map property that not used.
         * this will be stored in post meta.
         */
        $unusedData = [];

        /** loop through array data */
        $i = 0;

        foreach ($data as $tag => $tagValue) {
            if ($tag == 'job') {
                /** Stop the loop if index more than limit */
                if ($i >= $limit) {
                    break;
                }

                if (is_array($tagValue) || $tagValue instanceof \SimpleXMLElement) {
                    $tagValueArray = get_object_vars($tagValue);

                    /** Validate - check if data is exists or not */
                    // if (!array_key_exists('url', $tagValueArray) || empty($tagValueArray['url'])) {
                    //     // throw new Error('URL is empty, failed to store vacancy.');
                    //     error_log('[Flexfeed] - URL is empty, failed to store vacancy. Title : ' . array_key_exists('title', $tagValueArray) ? $tagValueArray['title'] : '"NoTitleFound" - index : ' . $i);
                    //     trigger_error('[Flexfeed] - URL is empty, failed to store vacancy.', E_USER_WARNING);
                    //     $i++;
                    //     continue;
                    // }

                    // if (!$this->_validate($tagValueArray['url'])) {
                    //     // throw new Error('Vacancy already exists.');
                    //     error_log('[Flexfeed] - Vacancy already exists. Title : ' . (array_key_exists('title', $tagValueArray) ? $tagValueArray['title'] : '"NoTitleFound"') . ' - index : ' . $i);
                    //     trigger_error('[Flexfeed] - Vacancy already exists.', E_USER_WARNING);
                    //     $i++;
                    //     continue;
                    // }

                    $payload = [];
                    /** ACF Imported company_name */
                    if (array_key_exists('company', $tagValueArray) && !empty($tagValueArray['company']) !== "") {
                        $payload["rv_vacancy_imported_company_name"] = preg_replace('/[\n\t]+/', '', $tagValueArray['company']);
                    } else {
                        $payload["rv_vacancy_imported_company_name"] = $rvAdmin->first_name . ' ' . $rvAdmin->last_name;
                    }

                    /** Map property that not used.
                     * this will be stored in post meta.
                     */
                    $unusedData = [];

                    foreach ($tagValueArray as $jobKey => $jobValue) {
                        /** Post Data Title */
                        if ($jobKey === 'title' && array_key_exists('title', $tagValueArray) && !empty($jobValue)) {
                            $arguments['post_title'] = preg_replace('/[\n\t]+/', '', $jobValue);

                            /** Post Data post_name */
                            $slug = StringHelper::makeSlug(preg_replace('/[\n\t]+/', '', $jobValue), '-', 'lower');
                            $arguments['post_name'] = 'flexfeed-' . $slug;
                        }

                        /** Post Data Date */
                        if ($jobKey === 'datePosted' && array_key_exists('datePosted', $tagValueArray) && !empty($jobValue)) {
                            $arguments['post_date'] = \DateTime::createFromFormat('d-n-Y H:i:s', preg_replace('/[\n\t]+/', '', $jobValue))->format('Y-m-d H:i:s');
                        }

                        /** ACF Description */
                        if ($jobKey === 'description' && array_key_exists('description', $tagValueArray) && !empty($jobValue)) {
                            $payload['description'] = preg_replace('/[\n\t]+/', '', $jobValue);
                        }

                        /** ACF placement_city */
                        if ($jobKey === 'city' && array_key_exists('city', $tagValueArray) && !empty($jobValue)) {
                            $payload['placement_city'] = preg_replace('/[\n\t]+/', '', $jobValue);

                            /** ACF Imported company_city */
                            $payload["rv_vacancy_imported_company_city"] = preg_replace('/[\n\t]+/', '', $jobValue);
                        }

                        /** ACF placcement_adress */
                        if ($jobKey === 'streetAddress' && array_key_exists('streetAddress', $tagValueArray) && !empty($jobValue)) {
                            $payload['placement_address'] = preg_replace('/[\n\t]+/', '', $jobValue);

                            /** ACF Imported company_country
                             * FOR NOW, all streetAddress value always have country name as last word
                             * so i only get last part of value
                             */
                            $companyCountry = explode(',', preg_replace('/[\n\t]+/', '', $jobValue));
                            $payload["rv_vacancy_imported_company_country"] = preg_replace('/[\n\t\s+]+/', '', end($companyCountry));
                        }

                        /** ACF external_url */
                        if ($jobKey === 'url' && array_key_exists('url', $tagValueArray) && !empty($jobValue)) {
                            $payload['external_url'] = preg_replace('/[\n\t]+/', '', $jobValue);
                        }

                        /** ACF apply_from_this_platform */
                        $payload['apply_from_this_platform'] = false;

                        /** ACF expired_at */
                        if ($jobKey === 'expirationdate' && array_key_exists('expirationdate', $tagValueArray) && !empty($jobValue) != "") {
                            $payload['expired_at'] = \DateTime::createFromFormat('d-n-Y', preg_replace('/[\n\t]+/', '', $jobValue))->setTime(23, 59, 59)->format('Y-m-d H:i:s');
                        }

                        /** ACF Paid
                         * set all import to paid
                         */
                        $payload["is_paid"] = true;

                        /** ACF Imported
                         * set all is_imported acf data to "true"
                         */
                        $payload["rv_vacancy_is_imported"] = "1";

                        /** ACF Imported company_email */
                        if ($jobKey === 'email' && array_key_exists('email', $tagValueArray) && !empty($jobValue)) {
                            $payload["rv_vacancy_imported_company_email"] = preg_replace('/[\n\t]+/', '', $jobValue);
                        }

                        /** Taxonomy working-hours */
                        if ($jobKey === 'hoursPerWeek' && array_key_exists('hoursPerWeek', $tagValueArray) && !empty($jobValue)) {
                            $taxonomy['working-hours'] = $this->_findWorkingHours(preg_replace('/[\n\t]+/', '', $jobValue));
                        }

                        /** Taxonomy education */
                        if ($jobKey === 'education' && array_key_exists('education', $tagValueArray) && !empty($jobValue)) {
                            $taxonomy['education'] = $this->_findEducation($this->_taxonomyKey['education'], preg_replace('/[\n\t]+/', '', $jobValue));
                        }

                        /** Taxonomy experience */
                        if ($jobKey === 'experience' && array_key_exists('experience', $tagValueArray) && !empty($jobValue)) {
                            $taxonomy['experiences'] = $this->_findExperiences(preg_replace('/[\n\t]+/', '', $jobValue));
                        }

                        /** Taxonomy Status */
                        if ($jobKey === 'isActive' && array_key_exists('isActive', $tagValueArray) && !empty($jobValue)) {
                            $taxonomy['status'] = $this->_findStatus($jobValue);
                        }

                        /** Taxonomy Role */
                        if ($jobKey === 'category' && array_key_exists('category', $tagValueArray) && !empty($jobValue)) {
                            $taxonomy['role'] = $this->_findRole(preg_replace('/[\n\t]+/', '', $jobValue));
                        }

                        if (!in_array($jobKey, ['title', 'datePosted', 'description', 'city', 'streetAddress', 'url', 'expirationdate', 'hoursPerWeek', 'education', 'experience', 'isActive', 'category', 'company', 'email'])) {
                            $unusedData[$i][$jobKey] = $jobValue;
                        }
                    }

                    /** Insert data */
                    try {
                        $post = wp_insert_post($arguments, true);

                        if (is_wp_error($post)) {
                            error_log(json_encode($post->get_error_messages()));
                        }

                        $vacancy = new Vacancy($post);

                        $vacancy->setTaxonomy($taxonomy);

                        /** Taxonomy Status */
                        $vacancy->setStatus('open');

                        /** Set expired date 30 days from this data inputed */
                        $expiredAt = new \DateTimeImmutable();
                        $expiredAt = $expiredAt->modify("+30 days")->format("Y-m-d H:i:s");
                        $vacancy->setProp("expired_at", $expiredAt);

                        foreach ($payload as $acf_field => $acfValue) {
                            $vacancy->setProp($acf_field, $acfValue, is_array($acfValue));
                        }

                        /** Calc coordinate */
                        if (isset($payload["placement_city"]) && isset($payload["placement_address"])) {
                            $vacancy->setCityLongLat($payload["placement_city"], true);
                            $vacancy->setAddressLongLat($payload["placement_address"]);
                            $vacancy->setDistance($payload["placement_city"], $payload["placement_city"] . " " . $payload["placement_address"]);
                        }

                        /** store unused to post meta */
                        update_post_meta($post, 'rv_vacancy_unused_data', $unusedData);
                        update_post_meta($post, 'rv_vacancy_source', 'flexfeed');
                    } catch (\Exception $error) {
                        error_log($error);
                    }
                }

                $i++;
            }
        }
    }

    private function _validate($xmlValue)
    {
        $args = [
            'post_type' => 'vacancy',
            'meta_query' => [
                [
                    'key' => 'external_url',
                    'value' => $xmlValue,
                    'compare' => '=',
                ]
            ]
        ];
        $query = new \WP_Query($args);

        return $query->post_count > 0 ? false : true;
    }

    private function _getTerms()
    {
        $terms = get_terms([
            'taxonomy' => array_values($this->_taxonomyKey),
            'hide_empty' => false
        ]);

        foreach ($terms as $key => $value) {
            $this->_terms[$value->taxonomy][] = [
                'term_id'   => $value->term_id,
                'name'      => $value->name,
                'slug'      => $value->slug
            ];
        }
    }

    private function _findWorkingHours($xmlValue)
    {
        $terms = $this->_terms['working-hours'];

        foreach ($terms as $key => $value) {
            $toCompare = explode('-', preg_replace('/\s+/', '', $value['name']));

            if (is_numeric($toCompare[0])) {
                if (is_numeric($toCompare[1])) {
                    if ((int)$toCompare[0] <= (int)$xmlValue && (int)$xmlValue <= (int)$toCompare[1]) {
                        return (int)$value['term_id'];
                    }
                } else {
                    if ((int)$toCompare[0] <= (int)$xmlValue) {
                        return (int)$value['term_id'];
                    }
                }
            }
        }

        /** Check using term_exists query */
        $termExists = term_exists(strtolower(preg_replace('/\s+/', '-', $xmlValue)), 'working-hours');
        if ($termExists) {
            if (is_array($termExists)) {
                return $termExists['term_id'];
            }
            return $termExists;
        } else {
            $newTerm = wp_insert_term($xmlValue, 'working-hours', []);

            if ($newTerm instanceof WP_Error) {
                if (array_key_exists('term_exists', $newTerm->error_data)) {
                    return $newTerm->error_data['term_exists'];
                }
                return null;
            } else {
                return $newTerm['term_id'];
            }
        }
    }

    private function _findEducation($taxonomy, $xmlValue)
    {
        if (in_array($taxonomy, array_values($this->_taxonomyKey))) {
            $terms = $this->_terms[$taxonomy];

            foreach ($terms as $key => $value) {
                if (strpos($xmlValue, $value['name'])) {
                    return (int)$value['term_id'];
                } else {
                    /** Check using term_exists query */
                    $termExists = term_exists(strtolower(preg_replace('/\s+/', '-', $xmlValue)), $taxonomy);
                    if ($termExists) {
                        if (is_array($termExists)) {
                            return $termExists['term_id'];
                        }
                        return $termExists;
                    } else {
                        $newTerm = wp_insert_term($xmlValue, $taxonomy, []);

                        if ($newTerm instanceof WP_Error) {
                            if (array_key_exists('term_exists', $newTerm->error_data)) {
                                return $newTerm->error_data['term_exists'];
                            }
                            return null;
                        } else {
                            return $newTerm['term_id'];
                        }
                    }
                }
            }
        }

        return;
    }

    private function _findExperiences($xmlValue)
    {
        if ($xmlValue !== 'NotSpecified') {
            $terms = $this->_terms['experiences'];
            $alternative = strtolower(preg_replace('/\s+/', '-', $xmlValue));

            foreach ($terms as $key => $value) {
                switch ($value) {
                    case $value['name'] == strtolower($xmlValue):
                    case $value['slug'] == strtolower($xmlValue):
                    case $value['name'] == strtolower($alternative):
                    case $value['slug'] == strtolower($alternative):
                        return $value['term_id'];
                }
            }

            /** Check using term_exists query */
            $termExists = term_exists(strtolower(preg_replace('/\s+/', '-', $xmlValue)), 'experiences');
            if ($termExists) {
                if (is_array($termExists)) {
                    return $termExists['term_id'];
                }
                return $termExists;
            } else {
                $newTerm = wp_insert_term($xmlValue, 'experiences', []);

                if ($newTerm instanceof WP_Error) {
                    if (array_key_exists('term_exists', $newTerm->error_data)) {
                        return $newTerm->error_data['term_exists'];
                    }
                    return null;
                } else {
                    return $newTerm['term_id'];
                }
            }
        } else {
            return;
        }
    }

    private function _findStatus($xmlValue)
    {
        $termOpen = get_term_by('slug', 'open', 'status', 'OBJECT');
        $termClose = get_term_by('slug', 'close', 'status', 'OBJECT');

        if ($xmlValue || $xmlValue == "true") {
            return $termOpen->term_id;
        } else {
            return $termClose->term_id;
        }
    }

    private function _findRole($xmlValue)
    {
        $terms = $this->_terms['role'];
        $alternative = strtolower(preg_replace('/\s+/', '-', $xmlValue));

        foreach ($terms as $key => $value) {
            switch ($value) {
                case $value['name'] == strtolower($xmlValue):
                case $value['slug'] == strtolower($xmlValue):
                case $value['name'] == strtolower($alternative):
                case $value['slug'] == strtolower($alternative):
                    return $value['term_id'];
            }
        }

        /** Check using term_exists query */
        $termExists = term_exists(strtolower(preg_replace('/\s+/', '-', $xmlValue)), 'role');
        if ($termExists) {
            if (is_array($termExists)) {
                return $termExists['term_id'];
            }
            return $termExists;
        } else {
            $newTerm = wp_insert_term($xmlValue, 'role', []);

            if ($newTerm instanceof WP_Error) {
                if (array_key_exists('term_exists', $newTerm->error_data)) {
                    return $newTerm->error_data['term_exists'];
                }
                return null;
            } else {
                return $newTerm['term_id'];
            }
        }
    }
}
