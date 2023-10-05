<?php

namespace Service;

use Controller\ParserController;
use Exception;
use PostType\Vacancy;
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
                'post_status' => 'publish',
                'post_author' => get_user_by('email', 'admin.jobRV@local.com')
            ];

            $i = 0;

            // print('<pre>' . print_r($data, true) . '</pre>');

            foreach ($data as $tag => $tagValue) {
                print($i . ' - ' . print_r($tag, true) . PHP_EOL);
                // if ($tag == 'job') {
                //     // print('<pre>' . print_r('a' . PHP_EOL, true) . '</pre>');
                //     if (is_array($tagValue) || $tagValue instanceof \SimpleXMLElement) {
                //         $tagValueArray = get_object_vars($tagValue);

                //         $payload = [];

                //         /** Map property that not used.
                //          * this will be stored in post meta.
                //          */
                //         $unusedData = [];
                //         foreach ($tagValueArray as $jobKey => $jobValue) {
                //             // print('<pre>' . print_r($jobKey, true) . '</pre>' . PHP_EOL);

                //             /** Post Data Title */
                //             if ($jobKey === 'title' && array_key_exists('title', $tagValueArray) && !empty($jobValue)) {
                //                 $arguments['post_title'] = preg_replace('/[\n\t]+/', '', $jobValue);
                //             }

                //             /** Post Data Date */
                //             if ($jobKey === 'datePosted' && array_key_exists('datePosted', $tagValueArray) && !empty($jobValue)) {
                //                 $arguments['post_date'] = \DateTime::createFromFormat('d-n-Y H:i:s', preg_replace('/[\n\t]+/', '', $jobValue))->format('Y-m-d H:i:s');
                //             }

                //             /** ACF Description */
                //             if ($jobKey === 'description' && array_key_exists('description', $tagValueArray) && !empty($jobValue)) {
                //                 $payload['description'] = preg_replace('/[\n\t]+/', '', $jobValue);
                //             }

                //             /** ACF placement_city */
                //             if ($jobKey === 'city' && array_key_exists('city', $tagValueArray) && !empty($jobValue)) {
                //                 $payload['placement_city'] = preg_replace('/[\n\t]+/', '', $jobValue);
                //             }

                //             /** ACF placcement_adress */
                //             if ($jobKey === 'streetAddress' && array_key_exists('streetAddress', $tagValueArray) && !empty($jobValue)) {
                //                 $payload['placement_address'] = preg_replace('/[\n\t]+/', '', $jobValue);
                //             }

                //             /** ACF external_url */
                //             if ($jobKey === 'url' && array_key_exists('url', $tagValueArray) && !empty($jobValue)) {
                //                 $payload['external_url'] = preg_replace('/[\n\t]+/', '', $jobValue);
                //             }

                //             /** ACF apply_from_this_platform */
                //             $payload['apply_from_this_platform'] = false;

                //             /** ACF expired_at */
                //             if ($jobKey === 'expirationdate' && array_key_exists('expirationdate', $tagValueArray) && !empty($jobValue) != "") {
                //                 // print('<pre>' . print_r($i . ' : ' . preg_replace('/[\n\t]+/', '', $jobValue) . ' - ' . \DateTime::createFromFormat('d-n-Y', preg_replace('/[\n\t]+/', '', $jobValue))->setTime(23, 59, 59)->format('Y-m-d H:i:s'), true) . '</pre>' . PHP_EOL);
                //                 $payload['expired_at'] = \DateTime::createFromFormat('d-n-Y', preg_replace('/[\n\t]+/', '', $jobValue))->setTime(23, 59, 59)->format('Y-m-d H:i:s');
                //             }

                //             /** Taxonomy working-hours */
                //             if ($jobKey === 'hoursPerWeek' && array_key_exists('hoursPerWeek', $tagValueArray) && !empty($jobValue)) {
                //                 $taxonomy['working-hours'] = $this->_findWorkingHours(preg_replace('/[\n\t]+/', '', $jobValue));
                //             }

                //             /** Taxonomy education */
                //             if ($jobKey === 'education' && array_key_exists('education', $tagValueArray) && !empty($jobValue)) {
                //                 $taxonomy['education'] = $this->_findEducation($this->_taxonomyKey['education'], preg_replace('/[\n\t]+/', '', $jobValue));
                //             }

                //             /** Taxonomy experience */
                //             if ($jobKey === 'experience' && array_key_exists('experience', $tagValueArray) && !empty($jobValue)) {
                //                 $taxonomy['experiences'] = $this->_findExperiences(preg_replace('/[\n\t]+/', '', $jobValue));
                //             }

                //             /** Taxonomy Status */
                //             if ($jobKey === 'isActive' && array_key_exists('isActive', $tagValueArray) && !empty($jobValue)) {
                //                 $taxonomy['status'] = $this->_findStatus($jobValue);
                //             }

                //             if (!in_array($jobKey, ['title', 'datePosted', 'description', 'city', 'streetAddress', 'url', 'expirationdate', 'hoursPerWeek', 'education', 'experience', 'isActive'])) {
                //                 $unusedData[] = [
                //                     $jobKey => $jobValue
                //                 ];
                //             }
                //         }

                //         $post = wp_insert_post($arguments);
                //         $vacancy = new Vacancy($post->ID);

                //         foreach ($payload as $key => $value) {
                //             // $vacancy->setProp
                //         }
                //     } else {
                //         throw new Exception("not an array : " . json_encode($tagValue));
                //     }

                //     $i++;
                // } else {
                //     $res[$tag] = $tagValue;
                // }
                $i++;
            }

            // print('<pre>'.print_r($res, true).'</pre>');
            return \ResponseHelper::build([
                'status' => 200,
                // 'data' => $res
            ]);
        } else {
            print('<pre>' . print_r('a', true) . '</pre>');
        }
    }

    public function parseJsonl()
    {
        if (file_exists(BASE_DIR . '/jobs.0.jsonl')) {
            $json = file_get_contents(BASE_DIR . '/jobs.0.jsonl');

            $data = json_decode($json, true);

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

            $i = 0;
            foreach ($data as $key => $value) {
                /** Post Data Title */
                if (array_key_exists('job_title', $value) && !empty($value['job_title'])) {
                    $arguments['post_title'] = preg_replace('/[\n\t]+/', '', $value['job_title']);

                    /** Unset used key */
                    unset($value['job_title']);
                }

                /** Post Data post date */
                if (array_key_exists('date', $value) && !empty($value['date'])) {
                    $arguments['post_date'] = preg_replace('/[\n\t]+/', '', $value['date']);

                    /** Unset used key */
                    unset($value['date']);
                }

                /** ACF Description */
                if (array_key_exists('job_description', $value) && !empty($value['job_description'])) {
                    $payload['description'] = $value['job_description'];

                    /** Unset used key */
                    unset($value['job_description']);
                }

                $salaryStart = 0;
                $salaryEnd = 0;

                /** ACF Salary */
                if (array_key_exists('salary', $value) && !empty($value['salary'])) {
                    // Salary Start
                    if (array_key_exists('salary_start', $value) && !empty($value['salary_start'])) {
                        $payload['salary_start'] = (int)$value['salary_start'];

                        /** Unset used key */
                        unset($value['salary_start']);
                    } else {
                        $payload['salary_start'] = (int)$value['salary'];
                    }

                    // Salary End
                    if (array_key_exists('salary_end', $value) && !empty($value['salary_end'])) {
                        $payload['salary_end'] = (int)$value['salary_end'];

                        /** Unset used key */
                        unset($value['salary_end']);
                    } else {
                        $payload['salary_end'] = (int)$value['salary'];
                    }

                    /** Unset used key */
                    unset($value['salary']);
                }

                if (array_key_exists('salary_start', $value) && !empty($value['salary_start'])) {
                    $payload['salaryStart'] = (int)$value['salary_start'];

                    /** Unset used key */
                    unset($value['salary_start']);
                }

                if (array_key_exists('salary_end', $value) && !empty($value['salary_end'])) {
                    $payload['salary_end'] = (int)$value['salary_end'];

                    /** Unset used key */
                    unset($value['salary_end']);
                }

                /** ACF External url */
                if (array_key_exists('apply_url', $value) && !empty($value['apply_url'])) {
                    $payload['external_url'] = preg_replace('/[\n\t]+/', '', $value['apply_url']);

                    /** Unset used key */
                    unset($value['apply_url']);
                }

                /** Taxonomy Working-hours */
                if (array_key_exists('hours_per_week_from', $value) && !empty($value['hours_per_week_from'])) {
                    if (array_key_exists('hours_per_week_to', $value) && !empty($value['hours_per_week_to'])) {
                        $taxonomy['working-hours'] = $value['hours_per_week_from'] . ' - ' . $value['hours_per_week_to'];

                        /** Unset used key */
                        unset($value['hours_per_week_to']);
                    } else {
                        $taxonomy['working-hours'] = $value['hours_per_week_from'];
                    }

                    /** Unset used key */
                    unset($value['hours_per_week_from']);
                }

                /** Taxonomy Education */
                if (array_key_exists('education_level', $value) && !empty($value['education_level'])) {
                    if (is_array($value['education_level'])) {
                        if (array_key_exists('label', $value['education_level'])) {
                            $taxonomy['education'] = $value['education_level']['label'];

                            /** Unset used key */
                            unset($value['education_level']);
                        }
                    }
                }

                /** Taxonomy Experiences */
                if (array_key_exists('experience_level', $value) && !empty($value['experience_level'])) {
                    if (is_array($value['experience_level'])) {
                        if (array_key_exists('label', $value['experience_level'])) {
                            $taxonomy['experiences'] = $value['experience_level']['label'];

                            /** Unset used key */
                            unset($value['experience_level']);
                        }
                    }
                }

                /** Taxonomy Job Type */
                // if (array_key_exists('employment_type', $value) && !empty($value['employment_type'])) {
                //     if (is_array($value['employment_type'])) {
                //         if (array_key_exists('label', $value['employment_type'])) {
                //             $taxonomy['type'] = $value['employment_type']['label'];

                //             /** Unset used key */
                //             unset($value['employment_type']);
                //         }
                //     }
                // }

                /** Taxonomy Role */
                if (array_key_exists('profession', $value) && !empty($value['profession'])) {
                    if (is_array($value['profession'])) {
                        if (array_key_exists('label', $value['profession'])) {
                            $taxonomy['role'] = $value['profession']['label'];

                            /** Unset used key */
                            unset($value['profession']);
                        }
                    }
                } else if (array_key_exists('profession_group', $value) && !empty($value['profession_group'])) {
                    if (is_array($value['profession_group'])) {
                        if (array_key_exists('label', $value['profession_group'])) {
                            $taxonomy['role'] = $value['profession_group']['label'];

                            /** Unset used key */
                            unset($value['profession_group']);
                        }
                    }
                } else if (array_key_exists('profession_class', $value) && !empty($value['profession_class'])) {
                    if (is_array($value['profession_class'])) {
                        if (array_key_exists('label', $value['profession_class'])) {
                            $taxonomy['role'] = $value['profession_class']['label'];

                            /** Unset used key */
                            unset($value['profession_class']);
                        }
                    }
                }

                foreach ($value as $propKey => $propValue) {
                    $unusedData[$i][$propKey] = $propValue;
                }

                $i++;
            }
        } else {
            print('<pre>' . print_r('not_found', true) . '</pre>');
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
