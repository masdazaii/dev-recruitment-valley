<?php

namespace Service;

use Controller\ParserController;
use Exception;
use WP_Error;

class ParserService
{
    private $_parserController;
    private $_taxonomyKey;
    private $_terms;

    public function __construct()
    {
        $this->_parserController = new ParserController();
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

    public function testParse()
    {
        /** Get All terms */
        $this->_getTerms();

        if (file_exists(BASE_DIR . '/flexfeed.xml')) {
            $data = simplexml_load_file(BASE_DIR . '/flexfeed.xml');

            /** wp_insert_post arguments */
            $arguments = [
                'post_type' => 'vacancy',
                'post_status' => 'publish'
            ];

            $i = 0;

            foreach ($data as $tag => $tagValue) {
                if ($tag == 'job') {
                    // print('<pre>' . print_r('a' . PHP_EOL, true) . '</pre>');
                    if (is_array($tagValue) || $tagValue instanceof \SimpleXMLElement) {
                        $tagValueArray = get_object_vars($tagValue);

                        $payload = [];

                        /** Map property that not used.
                         * this will be stored in post meta.
                         */
                        $unusedData = [];
                        foreach ($tagValueArray as $jobKey => $jobValue) {
                            // print('<pre>' . print_r($jobKey, true) . '</pre>' . PHP_EOL);

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
                                $payload['taxonomy']['working-hours'] = $this->_findWorkingHours(preg_replace('/[\n\t]+/', '', $jobValue));
                            }

                            /** Taxonomy education */
                            if ($jobKey === 'education' && array_key_exists('education', $tagValueArray) && !empty($jobValue)) {
                                $payload['taxonomy']['education'] = $this->_findEducation($this->_taxonomyKey['education'], preg_replace('/[\n\t]+/', '', $jobValue));
                            }

                            /** Taxonomy experience */
                            if ($jobKey === 'experience' && array_key_exists('experience', $tagValueArray) && !empty($jobValue)) {
                                $payload['taxonomy']['experiences'] = $this->_findExperiences(preg_replace('/[\n\t]+/', '', $jobValue));
                            }

                            /** Taxonomy Status */
                            if ($jobKey === 'isActive' && array_key_exists('isActive', $tagValueArray) && !empty($jobValue)) {
                                $payload['taxonomy']['status'] = $this->_findStatus($jobValue);
                            }

                            if (!in_array($jobKey, ['title', 'datePosted', 'description', 'city', 'streetAddress', 'url', 'expirationdate', 'hoursPerWeek', 'education', 'experience', 'isActive']))
                                $unusedData[] = [
                                    $jobKey => $jobValue
                                ];
                        }
                    } else {
                        throw new Exception("not an array : " . json_encode($tagValue));
                    }

                    $i++;
                } else {
                    $res[$tag] = $tagValue;
                }
            }

            // print('<pre>'.print_r($res, true).'</pre>');
            return \ResponseHelper::build([
                'status' => 200,
                'data' => $res
            ]);
        } else {
            print('<pre>' . print_r('a', true) . '</pre>');
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

            foreach ($terms as $key => $value) {
                if (strpos($xmlValue, $value['name'])) {
                    return (int)$value['term_id'];
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
}
