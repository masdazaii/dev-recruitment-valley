<?php

namespace Vacancy\Import\JSON;

use Exception;
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

        /** wp_insert_post arguments */
        $arguments = [
            'post_type' => 'vacancy',
            'post_status' => 'publish',
            'post_author' => get_user_by('email', 'admin.jobRV@local.com')->ID
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

                if (!$this->_validate($data[$i]['url'])) {
                    // throw new Error('Vacancy already exists.');
                    error_log('[NationaleVacatureBank] - Vacancy already exists. Title : ' . (array_key_exists('title', $data[$i]) ? $data[$i]['job_title'] : '"NoTitleFound"') . ' - index : ' . $i);
                    trigger_error('[NationaleVacatureBank] - Vacancy already exists.', E_USER_WARNING);
                    $i++;
                    continue;
                }

                /** Post Data Title */
                if (array_key_exists('job_title', $data[$i]) && !empty($data[$i]['job_title'])) {
                    $arguments['post_title'] = preg_replace('/[\n\t]+/', '', $data[$i]['job_title']);

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
                if (array_key_exists('job_description', $data[$i]) && !empty($data[$i]['job_description'])) {
                    $payload['description'] = $data[$i]['job_description'];

                    /** Unset used key */
                    unset($data[$i]['job_description']);
                }

                $payload['salary_start'] = 0;
                $payload['salary_end'] = 0;

                /** ACF Salary */
                if (array_key_exists('salary', $data[$i]) && !empty($data[$i]['salary'])) {
                    // Salary Start
                    if (array_key_exists('salary_start', $data[$i]) && !empty($data[$i]['salary_start'])) {
                        $payload['salary_start'] = (int)$data[$i]['salary_start'];

                        /** Unset used key */
                        unset($data[$i]['salary_start']);
                    } else {
                        $payload['salary_start'] = (int)$data[$i]['salary'];
                    }

                    // Salary End
                    if (array_key_exists('salary_end', $data[$i]) && !empty($data[$i]['salary_end'])) {
                        $payload['salary_end'] = (int)$data[$i]['salary_end'];

                        /** Unset used key */
                        unset($data[$i]['salary_end']);
                    } else {
                        $payload['salary_end'] = (int)$data[$i]['salary'];
                    }

                    /** Unset used key */
                    unset($data[$i]['salary']);
                }

                if (array_key_exists('salary_start', $data[$i]) && !empty($data[$i]['salary_start'])) {
                    $payload['salaryStart'] = (int)$data[$i]['salary_start'];

                    /** Unset used key */
                    unset($data[$i]['salary_start']);
                }

                if (array_key_exists('salary_end', $data[$i]) && !empty($data[$i]['salary_end'])) {
                    $payload['salary_end'] = (int)$data[$i]['salary_end'];

                    /** Unset used key */
                    unset($data[$i]['salary_end']);
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
                if (array_key_exists('location_remote_possible', $data[$i]) && !empty($data[$i]['location_remote_possible']) && $data[$i]['location_remote_possible'] == 'false') {
                    /** Set Placement Address */
                    if (array_key_exists('organization_address', $data[$i]) && !empty($data[$i]['organization_address'])) {
                        $payload['placement_address'] = preg_replace('/[\n\t]+/', '', $data[$i]['organization_address']);

                        /** Unset used key */
                        unset($data[$i]['organization_address']);
                    }

                    /** Set Taxonomy  */
                }

                /** ACF City Coordinate */
                if (array_key_exists('location_coordinates', $data[$i]) && !empty($data[$i]['location_coordinates'])) {
                    list($latitude, $longitude) = explode(',', $data[$i]['location_coordinates'], 2);
                    $payload['city_latitude'] = preg_replace('/[\n\t]+/', '', $latitude);
                    $payload['city_longitude'] = preg_replace('/[\n\t]+/', '', $longitude);

                    /** Unset used key */
                    unset($data[$i]['location_coordinates']);
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
                // if (array_key_exists('employment_type', $data[$i]) && !empty($data[$i]['employment_type'])) {
                //     if (is_array($data[$i]['employment_type'])) {
                //         if (array_key_exists('label', $data[$i]['employment_type'])) {
                //             $taxonomy['type'] = $data[$i]['employment_type']['label'];

                //             /** Unset used key */
                //             unset($data[$i]['employment_type']);
                //         }
                //     }
                // }

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
                    update_post_meta($post, 'nvb_unused_data', $unusedData);
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

        // print('<pre>' . print_r($query, true) . '</pre>');

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

        $newTerm = wp_insert_term($jsonValue, 'working-hours', []);
        if ($newTerm instanceof WP_Error) {
            return null;
        } else {
            return $newTerm['term_id'];
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

        $newTerm = wp_insert_term($jsonValue, 'education', []);
        if ($newTerm instanceof WP_Error) {
            return null;
        } else {
            return $newTerm['term_id'];
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

        $newTerm = wp_insert_term($jsonValue, 'experiences', []);
        if ($newTerm instanceof WP_Error) {
            return null;
        } else {
            return $newTerm['term_id'];
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

        $newTerm = wp_insert_term($jsonValue, 'role', []);
        if ($newTerm instanceof WP_Error) {
            return null;
        } else {
            return $newTerm['term_id'];
        }
    }
}
