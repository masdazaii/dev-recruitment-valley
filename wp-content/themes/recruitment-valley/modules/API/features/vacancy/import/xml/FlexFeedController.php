<?php

namespace Vacancy\Import\Xml;

use Exception;
use Vacancy\Vacancy;
use WP_Error;

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

        /** loop through array data */
        $i = 0;

        foreach ($data as $tag => $tagValue) {
            if ($tag == 'job') {
                if (is_array($tagValue) || $tagValue instanceof \SimpleXMLElement) {
                    $tagValueArray = get_object_vars($tagValue);

                    $payload = [];

                    /** Map property that not used.
                     * this will be stored in post meta.
                     */
                    $unusedData = [];
                    foreach ($tagValueArray as $jobKey => $jobValue) {
                        /** Post Data Title */
                        if ($jobKey === 'title' && array_key_exists('title', $tagValueArray) && !empty($jobValue)) {
                            $arguments['post_title'] = preg_replace('/[\n\t]+/', '', $jobValue);
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
                        }

                        /** ACF placcement_adress */
                        if ($jobKey === 'streetAddress' && array_key_exists('streetAddress', $tagValueArray) && !empty($jobValue)) {
                            $payload['placement_address'] = preg_replace('/[\n\t]+/', '', $jobValue);
                        }

                        /** ACF external_url */
                        if ($jobKey === 'url' && array_key_exists('url', $tagValueArray) && !empty($jobValue)) {
                            $payload['external_url'] = preg_replace('/[\n\t]+/', '', $jobValue);
                        }

                        /** ACF apply_from_this_platform */
                        $payload['apply_from_this_platform'] = false;

                        /** ACF expired_at */
                        if ($jobKey === 'expirationdate' && array_key_exists('expirationdate', $tagValueArray) && !empty($jobValue) != "") {
                            // print('<pre>' . print_r($i . ' : ' . preg_replace('/[\n\t]+/', '', $jobValue) . ' - ' . \DateTime::createFromFormat('d-n-Y', preg_replace('/[\n\t]+/', '', $jobValue))->setTime(23, 59, 59)->format('Y-m-d H:i:s'), true) . '</pre>' . PHP_EOL);
                            $payload['expired_at'] = \DateTime::createFromFormat('d-n-Y', preg_replace('/[\n\t]+/', '', $jobValue))->setTime(23, 59, 59)->format('Y-m-d H:i:s');
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

                        if (!in_array($jobKey, ['title', 'datePosted', 'description', 'city', 'streetAddress', 'url', 'expirationdate', 'hoursPerWeek', 'education', 'experience', 'isActive'])) {
                            $unusedData[$i][$jobKey] = $jobValue;
                        }
                    }

                    // print('<pre>' . print_r($arguments, true) . '</pre>');
                    // print('<pre>' . print_r($payload, true) . '</pre>');
                    print('<pre>' . print_r($taxonomy, true) . '</pre>');
                    // print('<pre>'.print_r($unusedData, true).'</pre>');
                }

                $i++;
            }
        }
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

        $newTerm = wp_insert_term($xmlValue, 'working-hours', []);
        if ($newTerm instanceof WP_Error) {
            return null;
        } else {
            return $newTerm['term_id'];
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
                    // Create new term
                    $newTerm = wp_insert_term($xmlValue, $taxonomy, []);
                    if ($newTerm instanceof WP_Error) {
                        return null;
                    } else {
                        return (int)$newTerm['term_id'];
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

            // Create new term
            $newTerm = wp_insert_term($xmlValue, 'experiences', []);
            if ($newTerm instanceof WP_Error) {
                return null;
            } else {
                return (int)$newTerm['term_id'];
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

        $newTerm = wp_insert_term($xmlValue, 'role', []);
        if ($newTerm instanceof WP_Error) {
            return null;
        } else {
            return $newTerm['term_id'];
        }
    }
}
