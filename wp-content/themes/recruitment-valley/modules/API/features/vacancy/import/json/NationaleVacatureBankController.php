<?php

namespace Vacancy\Import\JSON;

use Exception;
use Helper\Maphelper;
use Helper\StringHelper;
use Vacancy\Vacancy;
use WP_Error;

class NationaleVacatureBankController
{
    private $_sampleFile = THEME_DIR . '/assets/sample/nvb_job.jsonl';
    private $_sourceUrl;
    private $_taxonomy;
    private $_terms;

    public function __construct()
    {
        $this->_taxonomy = ['working-hours', 'education', 'type', 'experiences', 'sector', 'role', 'location', 'status'];
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
            $json = [];
        } else {
            if ($this->_sampleFile && !empty($this->_sampleFile)) {
                if (file_exists($this->_sampleFile)) {
                    $json = file_get_contents($this->_sampleFile);
                } else {
                    error_log($this->_sampleFile);
                    throw new Exception("Sample file not found!");
                }
            } else {
                throw new Exception("No sample file provided!");
            }
        }

        /** Decode */
        $data = json_decode($json, true);

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

        /** loop through array data
         * preferably using foreach,
         * but since i want to limit the sample that's why come the for-loop
         */
        for ($i = $start; $i < $limit; $i++) {
            if ($i <= count($data) - 1) {

                /** Validate - check if data is exists or not */
                if (!array_key_exists('apply_url', $data[$i]) || empty($data[$i]['apply_url'])) {
                    // throw new Error('URL is empty, failed to store vacancy.');
                    error_log('[NationaleVacatureBank] - URL is empty, failed to store vacancy. Title : ' . array_key_exists('title', $data[$i]) ? $data[$i]['job_title'] : '"NoTitleFound" - index : ' . $i);
                    trigger_error('[NationaleVacatureBank] - URL is empty, failed to store vacancy.', E_USER_WARNING);
                    $i++;
                    continue;
                }

                if (!$this->_validate($data[$i]['apply_url'])) {
                    // throw new Error('Vacancy already exists.');
                    error_log('[NationaleVacatureBank] - Vacancy already exists. Title : ' . (array_key_exists('title', $data[$i]) ? $data[$i]['job_title'] : '"NoTitleFound"') . ' - index : ' . $i);
                    trigger_error('[NationaleVacatureBank] - Vacancy already exists.', E_USER_WARNING);
                    $i++;
                    continue;
                }

                /** Post Data Title & name */
                if (array_key_exists('job_title', $data[$i]) && !empty($data[$i]['job_title'])) {
                    $arguments['post_title'] = preg_replace('/[\n\t]+/', '', $data[$i]['job_title']);

                    /** Post Data post_name */
                    $slug = StringHelper::makeSlug(preg_replace('/[\n\t]+/', '', $data[$i]['job_title']), '-', 'lower');
                    $arguments['post_name'] = 'nvb-' . $slug;

                    /** Unset used key */
                    unset($data[$i]['job_title']);
                }

                /** Post Data post date */
                if (array_key_exists('date', $data[$i]) && !empty($data[$i]['date'])) {
                    $arguments['post_date'] = preg_replace('/[\n\t]+/', '', $data[$i]['date']);

                    /** Unset used key */
                    unset($data[$i]['date']);
                }


                /** ACF Description */
                if (array_key_exists('full_text', $data[$i]) && !empty($data[$i]['full_text'])) {
                    $payload['description'] = $data[$i]['full_text'];

                    /** Unset used key */
                    unset($data[$i]['full_text']);
                } else {
                    $payload['description'] = '';
                    if (array_key_exists('employer_description', $data[$i]) && !empty($data[$i]['employer_description'])) {
                        $payload['description'] .= $data[$i]['employer_description'];

                        /** Unser used key */
                        unset($data[$i]['employer_description']);
                    }

                    if (array_key_exists('conditions_description', $data[$i]) && !empty($data[$i]['conditions_description'])) {
                        $payload['description'] .= $data[$i]['conditions_description'];

                        /** Unset used key */
                        unset($data[$i]['conditions_description']);
                    }

                    if (array_key_exists('candidate_description', $data[$i]) && !empty($data[$i]['candidate_description'])) {
                        $payload['description'] .= $data[$i]['candidate_description'];

                        /** Unset used key */
                        unset($data[$i]['candidate_description']);
                    }

                    if (array_key_exists('job_description', $data[$i]) && !empty($data[$i]['job_description'])) {
                        $payload['description'] .= $data[$i]['job_description'];

                        /** Unset used key */
                        unset($data[$i]['job_description']);
                    }
                }

                $payload['salary_start'] = 0;
                $payload['salary_end'] = 0;

                /** ACF Salary */
                if (array_key_exists('salary', $data[$i]) && !empty($data[$i]['salary'])) {
                    // Salary Start
                    if (array_key_exists('salary_from', $data[$i]) && !empty($data[$i]['salary_from'])) {
                        $payload['salary_start'] = (int)$data[$i]['salary_from'];

                        /** Unset used key */
                        unset($data[$i]['salary_from']);
                    } else {
                        $payload['salary_start'] = (int)$data[$i]['salary'];
                    }

                    // Salary End
                    if (array_key_exists('salary_to', $data[$i]) && !empty($data[$i]['salary_to'])) {
                        $payload['salary_end'] = (int)$data[$i]['salary_to'];

                        /** Unset used key */
                        unset($data[$i]['salary_to']);
                    } else {
                        $payload['salary_end'] = (int)$data[$i]['salary'];
                    }

                    /** Unset used key */
                    // unset($data[$i]['salary']);
                }

                if (array_key_exists('salary_from', $data[$i]) && !empty($data[$i]['salary_from'])) {
                    $payload['salaryStart'] = (int)$data[$i]['salary_from'];

                    /** Unset used key */
                    unset($data[$i]['salary_start']);
                }

                if (array_key_exists('salary_to', $data[$i]) && !empty($data[$i]['salary_to'])) {
                    $payload['salary_end'] = (int)$data[$i]['salary_to'];

                    /** Unset used key */
                    unset($data[$i]['salary_to']);
                }

                /** ACF External url */
                if (array_key_exists('apply_url', $data[$i]) && !empty($data[$i]['apply_url'])) {
                    $payload['external_url'] = preg_replace('/[\n\t]+/', '', $data[$i]['apply_url']);

                    /** Unset used key */
                    unset($data[$i]['apply_url']);
                }

                /** ACF City */
                if (array_key_exists('location_name', $data[$i]) && !empty($data[$i]['location_name'])) {
                    $payload['placement_city'] = preg_replace('/[\n\t]+/', '', $data[$i]['location_name']);

                    /** Unset used key */
                    unset($data[$i]['location_name']);
                }

                /** ACF Placement Address
                 * sometimes, there is property "location_remote_possible"
                 * if this property value is false, set placement with organization address
                 */
                if (array_key_exists('location_remote_possible', $data[$i]) && (!empty($data[$i]['location_remote_possible']) || $data[$i]['location_remote_possible'] == false)) {
                    /** Set Placement Address */
                    if (array_key_exists('organization_address', $data[$i]) && !empty($data[$i]['organization_address'])) {
                        $payload['placement_address'] = preg_replace('/[\n\t]+/', '', $data[$i]['organization_address']);

                        /** Unset used key */
                        unset($data[$i]['organization_address']);
                    }

                    /** Set Taxonomy Location to remote */
                    $taxonomy['location'] = $this->_findLocation('on-site');
                }

                /** ACF City Coordinate */
                if (array_key_exists('location_coordinates', $data[$i]) && !empty($data[$i]['location_coordinates'])) {
                    list($latitude, $longitude) = explode(',', $data[$i]['location_coordinates'], 2);
                    $payload['city_latitude'] = preg_replace('/[\n\t]+/', '', $latitude);
                    $payload['city_longitude'] = preg_replace('/[\n\t]+/', '', $longitude);

                    /** Unset used key */
                    unset($data[$i]['location_coordinates']);
                }

                /** ACF Paid
                 * set all import to paid
                 */
                $payload["is_paid"] = true;

                /** ACF Imported
                 * set all is_imported acf data to "true"
                 */
                $payload["rv_vacancy_is_imported"] = "1";

                /** ACF Imported rv_vacancy_imported_company_name */
                if (array_key_exists('organization_name', $data[$i]) && !empty($data[$i]['organization_name']) !== "") {
                    $payload["rv_vacancy_imported_company_name"] = preg_replace('/[\n\t]+/', '', $data[$i]['organization_name']);

                    /** Unset data key */
                    unset($data[$i]['organization_name']);

                    /** ACF Imported rv_vacancy_imported_company_email */
                    if (array_key_exists('organization_email', $data[$i]) && !empty($data[$i]['organization_email'])) {
                        $payload["rv_vacancy_imported_company_email"] = preg_replace('/[\n\t]+/', '', $data[$i]['organization_email']);

                        /** Unset data key */
                        unset($data[$i]['organization_email']);
                    }

                    /** ACF Imported company_city */
                    if (array_key_exists('organization_location_name', $data[$i]) && !empty($data[$i]['organization_location_name']) !== "") {
                        $payload["rv_vacancy_imported_company_city"] = preg_replace('/[\n\t]+/', '', $data[$i]['organization_location_name']);

                        /** Unset data key */
                        unset($data[$i]['organization_location_name']);
                    }
                } else if (array_key_exists('advertiser_name', $data[$i]) && !empty($data[$i]['advertiser_name']) !== "") {
                    $payload["rv_vacancy_imported_company_name"] = preg_replace('/[\n\t]+/', '', $data[$i]['advertiser_name']);

                    /** Unset data key */
                    unset($data[$i]['advertiser_name']);

                    /** ACF Imported rv_vacancy_imported_company_email */
                    if (array_key_exists('advertiser_email', $data[$i]) && !empty($data[$i]['advertiser_email'])) {
                        $payload["rv_vacancy_imported_company_email"] = preg_replace('/[\n\t]+/', '', $data[$i]['advertiser_email']);

                        /** Unset data key */
                        unset($data[$i]['advertiser_email']);
                    }

                    /** ACF Imported company_city */
                    if (array_key_exists('advertiser_location', $data[$i]) && !empty($data[$i]['advertiser_location']) !== "") {
                        $payload["rv_vacancy_imported_company_city"] = preg_replace('/[\n\t]+/', '', $data[$i]['advertiser_location']);

                        /** Unset data key */
                        unset($data[$i]['advertiser_location']);
                    }
                } else {
                    $payload["rv_vacancy_imported_company_name"] = $rvAdmin->first_name . ' ' . $rvAdmin->last_name;
                }

                /** ACF Imported Country */
                if (array_key_exists('rv_vacancy_imported_company_city', $payload) && !empty($payload['rv_vacancy_imported_company_city'])) {

                    if (array_key_exists('organization_address', $data[$i]) && !empty($data[$i]['organization_address'])) {
                        $mapAddress = preg_replace('/[\n\t]+/', '', $data[$i]['organization_address']);
                    } else {
                        $mapAddress = $payload['rv_vacancy_imported_company_city'];
                    }

                    $payload['rv_vacancy_imported_company_country'] = Maphelper::reverseGeoData('address', 'nl', 'country', [], $mapAddress)['long_name'];
                }

                /** Taxonomy Working-hours */
                if (array_key_exists('hours_per_week_from', $data[$i]) && !empty($data[$i]['hours_per_week_from'])) {
                    if (array_key_exists('hours_per_week_to', $data[$i]) && !empty($data[$i]['hours_per_week_to'])) {
                        $taxonomy['working-hours'] = $this->_findWorkingHours($data[$i]['hours_per_week_from'] . ' - ' . $data[$i]['hours_per_week_to']);

                        /** Unset used key */
                        unset($data[$i]['hours_per_week_to']);
                    } else {
                        $taxonomy['working-hours'] = $this->_findWorkingHours($data[$i]['hours_per_week_from']);
                    }

                    /** Unset used key */
                    unset($data[$i]['hours_per_week_from']);
                }

                /** Taxonomy Education */
                if (array_key_exists('education_level', $data[$i]) && !empty($data[$i]['education_level'])) {
                    if (is_array($data[$i]['education_level'])) {
                        if (array_key_exists('label', $data[$i]['education_level'])) {
                            $taxonomy['education'] = $this->_findEducation($data[$i]['education_level']['label']);

                            /** Unset used key */
                            unset($data[$i]['education_level']);
                        }
                    }
                }

                /** Taxonomy Experiences */
                if (array_key_exists('experience_level', $data[$i]) && !empty($data[$i]['experience_level'])) {
                    if (is_array($data[$i]['experience_level'])) {
                        if (array_key_exists('label', $data[$i]['experience_level'])) {
                            $taxonomy['experiences'] = $this->_findExperience($data[$i]['experience_level']['label']);

                            /** Unset used key */
                            unset($data[$i]['experience_level']);
                        }
                    }
                }

                /** Taxonomy Job Type */
                if (array_key_exists('employment_type', $data[$i]) && !empty($data[$i]['employment_type'])) {
                    if (is_array($data[$i]['employment_type'])) {
                        if (array_key_exists('label', $data[$i]['employment_type'])) {
                            $taxonomy['type'] = $this->_findEmploymentType($data[$i]['employment_type']['label']);

                            /** Unset used key */
                            unset($data[$i]['employment_type']);
                        }
                    }
                }

                /** Taxonomy Role */
                if (array_key_exists('profession', $data[$i]) && !empty($data[$i]['profession'])) {
                    if (is_array($data[$i]['profession'])) {
                        if (array_key_exists('label', $data[$i]['profession'])) {
                            $taxonomy['role'] = $this->_findRole($data[$i]['profession']['label']);

                            /** Unset used key */
                            unset($data[$i]['profession']);
                        }
                    }
                } else if (array_key_exists('profession_group', $data[$i]) && !empty($data[$i]['profession_group'])) {
                    if (is_array($data[$i]['profession_group'])) {
                        if (array_key_exists('label', $data[$i]['profession_group'])) {
                            $taxonomy['role'] = $this->_findRole($data[$i]['profession_group']['label']);

                            /** Unset used key */
                            unset($data[$i]['profession_group']);
                        }
                    }
                } else if (array_key_exists('profession_class', $data[$i]) && !empty($data[$i]['profession_class'])) {
                    if (is_array($data[$i]['profession_class'])) {
                        if (array_key_exists('label', $data[$i]['profession_class'])) {
                            $taxonomy['role'] = $this->_findRole($data[$i]['profession_class']['label']);

                            /** Unset used key */
                            unset($data[$i]['profession_class']);
                        }
                    }
                }

                foreach ($data[$i] as $propKey => $propValue) {
                    $unusedData[$propKey] = $propValue;
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

                    /** store unused to post meta */
                    update_post_meta($post, 'rv_vacancy_unused_data', $unusedData);
                    update_post_meta($post, 'rv_vacancy_source', 'nationalvacaturebank');

                    /** Calc coordinate placement City*/
                    if (isset($payload["placement_city"]) || isset($payload["placement_address"])) {
                        if ($payload['city_latitude'] == "" && $payload["city_longitude"] == "") {
                            $vacancy->setCityLongLat($payload["placement_city"]);
                        }

                        if (isset($payload["placement_address"])) {
                            $vacancy->setAddressLongLat($payload["placement_address"]);
                        }

                        if ($payload['city_latitude'] == "" && $payload["city_longitude"] == "" && isset($payload["placement_address"])) {
                            $vacancy->setDistance($payload["placement_city"], $payload["placement_city"] . " " . $payload["placement_address"]);
                        } else if ($payload['city_latitude'] !== "" && $payload["city_longitude"] !== "" && isset($payload["placement_address"])) {
                            $cityCoordinate = [
                                'lat' => $payload['city_latitude'],
                                'long' => $payload['city_longitude']
                            ];

                            $vacancy->setAddressLongLat($payload['placement_address']);
                            $placementCoordinate = [
                                'lat' => $vacancy->getProp('placement_address_latitude', true),
                                'long' => $vacancy->getProp('placement_address_longitude', true),
                            ];

                            $vacancy->setCoordinateDistance($cityCoordinate, $placementCoordinate);
                        }
                    }

                    /** Calc coordinate company city */
                    if (isset($payload["rv_vacancy_imported_company_city"]) && $payload["rv_vacancy_imported_company_city"] !== "") {
                        $vacancy->setImportedCompanyCityLongLat($payload["rv_vacancy_imported_company_city"]);
                    }
                } catch (\Exception $error) {
                    error_log($error);
                }
            }
        }
    }

    private function _validate($jsonValue)
    {
        $args = [
            'post_type' => 'vacancy',
            'meta_query' => [
                [
                    'key' => 'external_url',
                    'value' => $jsonValue,
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
            'taxonomy' => array_values($this->_taxonomy),
            'hide_empty' => false
        ]);

        foreach ($terms as $key => $value) {
            $this->_terms[$value->taxonomy][] = [
                'term_id'   => $value->term_id,
                'name'      => strtolower($value->name),
                'slug'      => strtolower($value->slug)
            ];
        }
    }

    private function _findWorkingHours($jsonValue)
    {
        $terms = $this->_terms['working-hours'];
        $alternative = strtolower(preg_replace('/\s+/', '', $jsonValue));

        foreach ($terms as $key => $value) {
            if ($value['name'] == strtolower($jsonValue) || $value['slug'] == strtolower($jsonValue) || $value['name'] == strtolower($alternative) || $value['slug'] == strtolower($alternative)) {
                return $value['term_id'];
            }
        }

        /** Check using term_exists query */
        $termExists = term_exists(strtolower(preg_replace('/\s+/', '-', $jsonValue)), 'working-hours');
        if ($termExists) {
            if (is_array($termExists)) {
                return $termExists['term_id'];
            }
            return $termExists;;
        } else {
            $newTerm = wp_insert_term($jsonValue, 'working-hours', []);

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

    private function _findEducation($jsonValue)
    {
        $terms = $this->_terms['education'];
        $alternative = strtolower(preg_replace('/\s+/', '-', $jsonValue));

        foreach ($terms as $key => $value) {
            switch ($value) {
                case $value['name'] == strtolower($jsonValue):
                case $value['slug'] == strtolower($jsonValue):
                case $value['name'] == strtolower($alternative):
                case $value['slug'] == strtolower($alternative):
                    return $value['term_id'];
            }
        }

        /** Check using term_exists query */
        $termExists = term_exists(strtolower(preg_replace('/\s+/', '-', $jsonValue)), 'education');
        if ($termExists) {
            if (is_array($termExists)) {
                return $termExists['term_id'];
            }
            return $termExists;;
        } else {
            $newTerm = wp_insert_term($jsonValue, 'education', []);

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

    private function _findExperience($jsonValue)
    {
        $terms = $this->_terms['experiences'];
        $alternative = strtolower(preg_replace('/\s+/', '-', $jsonValue));

        foreach ($terms as $key => $value) {
            switch ($value) {
                case $value['name'] == strtolower($jsonValue):
                case $value['slug'] == strtolower($jsonValue):
                case $value['name'] == strtolower($alternative):
                case $value['slug'] == strtolower($alternative):
                    return $value['term_id'];
            }
        }

        /** Check using term_exists query */
        $termExists = term_exists(strtolower(preg_replace('/\s+/', '-', $jsonValue)), 'experiences');
        if ($termExists) {
            if (is_array($termExists)) {
                return $termExists['term_id'];
            }
            return $termExists;;
        } else {
            $newTerm = wp_insert_term($jsonValue, 'experiences', []);

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

    private function _findRole($jsonValue)
    {
        $terms = $this->_terms['role'];
        $alternative = strtolower(preg_replace('/\s+/', '-', $jsonValue));

        foreach ($terms as $key => $value) {
            switch ($value) {
                case $value['name'] == strtolower($jsonValue):
                case $value['slug'] == strtolower($jsonValue):
                case $value['name'] == strtolower($alternative):
                case $value['slug'] == strtolower($alternative):
                    return $value['term_id'];
            }
        }

        /** Check using term_exists query */
        $termExists = term_exists(strtolower(preg_replace('/\s+/', '-', $jsonValue)), 'role');
        if ($termExists) {
            if (is_array($termExists)) {
                return $termExists['term_id'];
            }
            return $termExists;;
        } else {
            $newTerm = wp_insert_term($jsonValue, 'role', []);

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

    private function _findLocation($jsonValue)
    {
        $terms = $this->_terms['location'];
        $alternative = strtolower(preg_replace('/\s+/', '-', $jsonValue));

        foreach ($terms as $key => $value) {
            switch ($value) {
                case $value['name'] == strtolower($jsonValue):
                case $value['slug'] == strtolower($jsonValue):
                case $value['name'] == strtolower($alternative):
                case $value['slug'] == strtolower($alternative):
                    return $value['term_id'];
            }
        }

        /** Check using term_exists query */
        $termExists = term_exists(strtolower(preg_replace('/\s+/', '-', $jsonValue)), 'location');
        if ($termExists) {
            if (is_array($termExists)) {
                return $termExists['term_id'];
            }
            return $termExists;;
        } else {
            $newTerm = wp_insert_term($jsonValue, 'location', []);

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

    private function _findEmploymentType($jsonValue)
    {
        /** Manual */
        $terms = $this->_terms['type'];
        $alternative = strtolower(preg_replace('/\s+/', '-', $jsonValue));

        foreach ($terms as $key => $value) {
            switch ($value) {
                case $value['name'] == strtolower($jsonValue):
                case $value['slug'] == strtolower($jsonValue):
                case $value['name'] == strtolower($alternative):
                case $value['slug'] == strtolower($alternative):
                    return $value['term_id'];
            }
        }

        /** Check using term_exists query */
        $termExists = term_exists(strtolower(preg_replace('/\s+/', '-', $jsonValue)), 'type');
        if ($termExists) {
            if (is_array($termExists)) {
                return $termExists['term_id'];
            }
            return $termExists;;
        } else {
            $newTerm = wp_insert_term($jsonValue, 'type', []);

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
