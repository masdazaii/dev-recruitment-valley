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
            'location'      => 'location'
        ];
    }

    public function testParse()
    {
        /** Get All terms */
        $this->_getTerms();

        if (file_exists(BASE_DIR . '/flexfeed.xml')) {
            $data = simplexml_load_file(BASE_DIR . '/flexfeed.xml');

            $res = [
                'job' => []
            ];
            $temp = [];

            $i = 0;
            foreach ($data as $tag => $tagValue) {
                if ($tag == 'job') {
                    // print('<pre>' . print_r('a' . PHP_EOL, true) . '</pre>');
                    if (is_array($tagValue) || $tagValue instanceof \SimpleXMLElement) {
                        $tagValueArray = get_object_vars($tagValue);

                        $payload = [];

                        /** ACF placement_city */
                        if (array_key_exists('city', $tagValueArray) && $tagValueArray['city'] !== "") {
                            $ayload['placement_city'] = preg_replace('/[\n\t]+/', '', $tagValueArray['city']);
                        }

                        /** ACF placcement_adress */
                        if (array_key_exists('streetAddress', $tagValueArray) && $tagValueArray['streetAddress'] !== "") {
                            $payload['placement_address'] = preg_replace('/[\n\t]+/', '', $tagValueArray['streetAddress']);
                        }

                        /** ACF external_url */
                        if (array_key_exists('url', $tagValueArray) && $tagValueArray['url'] !== "") {
                            $payload['external_url'] = preg_replace('/[\n\t]+/', '', $tagValueArray['url']);
                            $payload['apply_from_this_platform'] = false;
                        }

                        /** ACF expired_at */
                        if (array_key_exists('expirationDate', $tagValueArray) && $tagValueArray['expirationDate'] != "") {
                            $payload['expired_at'] = preg_replace('/[\n\t]+/', '', $tagValueArray['expirationdate']);
                        }

                        /** Taxonomy working-hours */
                        if (array_key_exists('hoursPerWeek', $tagValueArray) && $tagValueArray['hoursPerWeek'] !== "") {
                            $payload['working-hours'] = $this->_findHours(preg_replace('/[\n\t]+/', '', $tagValueArray['hoursPerWeek']));
                        }

                        /** Taxonomy education */
                        if (array_key_exists('education', $tagValueArray) && $tagValueArray['education'] !== "") {
                            $payload['education'] = $this->_findTerms($this->_taxonomyKey['education'], preg_replace('/[\n\t]+/', '', $tagValueArray['education']));
                        }

                        // $payload = [
                        //     "placement_city" => preg_replace('/[\n\t]+/', '', $tagValueArray['city']),
                        //     "placement_address" => preg_replace('/[\n\t]+/', '', $tagValueArray['streetAddress']),
                        //     // "salary_start" => $salary,
                        //     // "salary_end" => $salary,
                        //     "external_url" => preg_replace('/[\n\t]+/', '', $tagValueArray['url']),
                        //     "apply_from_this_platform" => false,
                        //     // "user_id" => ,
                        //     "taxonomy" => [
                        //         // "sector" => $request["sector"],
                        //         // "role" => $request["role"],
                        //         "working-hours" => $this->_findHours(preg_replace('/[\n\t]+/', '', $tagValueArray['hoursPerWeek'])),
                        //         // "location" => $request["location"],
                        //         // "education" => $tagValueArray['education'] && $tagValueArray['education'] !== "" ? $this->_findTerms('education', preg_replace('/[\n\t]+/', '', $tagValueArray['education'])) : ,
                        //         // "type" => get_term_by('slug', $tagValue->jobtype[0], 'type', 'OBJECT')->term_id,
                        //         // "experiences" => get_term_by('slug', $tagValue->experience[0], 'type', 'OBJECT')->term_id
                        //     ],
                        //     'expired_at' => preg_replace('/[\n\t]+/', '', $tagValueArray['expirationdate']),
                        //     // 'rv_vacancy_country' => $request['country']
                        // ];

                        // print('<pre>' . print_r($i . ' : ' . $tagValueArray['email'], true) . '</pre>');
                        print('<pre>' . print_r($payload, true) . '</pre>');
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

    private function _findHours($xmlValue)
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

    private function _findTerms($taxonomy, $xmlValue)
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
}
