<?php

namespace Vacancy\Import\Xml;

use DateTime;
use DateTimeImmutable;
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

    public function __construct($source)
    {
        if ($source) {
            $this->_sourceUrl = $source;

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
        } else {
            throw new Exception("Please specify the source!");
        }
    }

    public function import($limit = 'all', $offset = 0)
    {
        error_log('Import Flexfeed Called.');
        /** Get All terms */
        $this->_getTerms();

        /** Parse and store data */
        $this->_parse($limit, $offset);
    }

    private function _parse($limit, $start)
    {
        error_log('Parse Flexfeed Called.');
        if (isset($this->_sourceUrl) && !empty($this->_sourceUrl)) {
            try {
                /** Init the curl */
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL             => $this->_sourceUrl,   // Source url
                    CURLOPT_CUSTOMREQUEST   => 'GET',   // Used HTTP Method
                    CURLOPT_HTTPHEADER      => ["Authorization: " . FLEXFEED_API_KEY],  // Add header to request
                    CURLOPT_HEADER          => false,   // true to include the header in the output.
                    CURLOPT_RETURNTRANSFER  => true,    // true to return the transfer as a string of the return value of curl_exec() instead of outputting it directly.
                    CURLOPT_CONNECTTIMEOUT  => 120,  // time-out on connect
                    CURLOPT_TIMEOUT         => 120,  // time-out on response
                ]);

                // curl_setopt($curl, CURLOPT_URL, $this->_sourceUrl);
                // curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                // curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: " . FLEXFEED_API_KEY]);
                // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $response       = curl_exec($curl);
                $responseCode   = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                /** If Curl error */
                if (curl_errno($curl)) {
                    error_log('Curl Error in fetch flexfeed API - ' . curl_error($curl));
                }

                curl_close($curl);

                /** Map the data only if response is 200 */
                if ($responseCode != 200) {
                    error_log('Failed fetch flexfeed API - response code : ' . $responseCode . ' - response trace : ' . $response);
                } else {
                    /** Decode the json */
                    $response = json_decode($response);

                    /** Get RV Administrator User data */
                    $rvAdmin = get_user_by('email', 'adminjob@recruitmentvalley.com');
                    $importUser = get_field('import_api_user_to_import', 'option') ?? $rvAdmin->ID;

                    /** wp_insert_post arguments */
                    $arguments = [
                        'post_type' => 'vacancy',
                        'post_status' => 'publish',
                        'post_author' => $importUser
                    ];

                    $payload    = [];
                    $taxonomy   = [];

                    /** Map property that not used.
                     * this will be stored in post meta.
                     */
                    $unusedData = [];

                    /** Loop through array data */
                    // $i = 0;
                    $jobs = $response->source->job;
                    for ($i = 0; $i < count($jobs); $i++) {
                        /** Validate - check if data is exists or not
                         * check by requisitionId (previously validate by url)
                         */
                        if (!property_exists($jobs[$i], 'requisitionId') || empty($jobs[$i]->requisitionId)) {
                            error_log('[Flexfeed] - RequsistionId is empty, failed to store vacancy. Title : ' . property_exists($jobs[$i], 'title') ? $jobs[$i]->title : '"NoTitleFound" - index : ' . $i);
                            trigger_error('[Flexfeed] - RequsistionId is empty, failed to store vacancy.', E_USER_WARNING);
                            $i++;
                            continue;
                        }

                        if (!$this->_validate($jobs[$i]->requisitionId)) {
                            error_log('[Flexfeed] - Vacancy already exists. RequsitionId : ' . (property_exists($jobs[$i], 'requisitionId') ? $jobs[$i]->requisitionId : '"NoRequisitionIdFound"') . ' & Title : ' . (property_exists($jobs[$i], 'title') ? $jobs[$i]->title : '"NoTitleFound"') . ' - index : ' . $i);
                            trigger_error('[Flexfeed] - Vacancy already exists.', E_USER_WARNING);
                            $i++;
                            continue;
                        }

                        /** ACF requisitionId */
                        $payload['rv_vacancy_imported_source_id'] = $jobs[$i]->requisitionId;
                        /** Unset used key : requisitionId */
                        unset($jobs[$i]->requisitionId);

                        /** Post Data Title */
                        if (property_exists($jobs[$i], 'title') && !empty($jobs[$i]->title)) {
                            $arguments['post_title'] = preg_replace('/[\n\t]+/', '', $jobs[$i]->title);

                            /** Post Data post_name */
                            $slug = StringHelper::makeSlug(preg_replace('/[\n\t]+/', '', $jobs[$i]->title), '-', 'lower');
                            $arguments['post_name'] = 'flexfeed-' . $slug . '-' . $i;

                            /** Unset used key */
                            unset($jobs[$i]->title);
                        }

                        /** Post Data Date */
                        if (property_exists($jobs[$i], 'datePosted') && !empty($jobs[$i]->datePosted)) {
                            $arguments['post_date'] = \DateTime::createFromFormat('d-n-Y H:i:s', preg_replace('/[\n\t]+/', '', $jobs[$i]->datePosted))->format('Y-m-d H:i:s');

                            /** Unset used key */
                            // unset($jobs[$i]->datePosted);
                        }

                        /** ACF Description */
                        if (property_exists($jobs[$i], 'description') && !empty($jobs[$i]->description)) {
                            $payload['description'] = preg_replace('/[\n\t]+/', '', $jobs[$i]->description);

                            /** Unset used key */
                            unset($jobs[$i]->description);
                        }

                        /** ACF placement_city */
                        if (property_exists($jobs[$i], 'city') && !empty($jobs[$i]->city)) {
                            $payload['placement_city'] = preg_replace('/[\n\t]+/', '', $jobs[$i]->city);

                            /** ACF Imported company_city */
                            $payload["rv_vacancy_imported_company_city"] = preg_replace('/[\n\t]+/', '', $jobs[$i]->city);

                            /** Unset used key */
                            unset($jobs[$i]->city);
                        }

                        /** ACF placement_address */
                        if (property_exists($jobs[$i], 'streetAddress') && !empty($jobs[$i]->streetAddress)) {
                            $payload['placement_address'] = preg_replace('/[\n\t]+/', '', $jobs[$i]->streetAddress);

                            /** ACF Imported company_country
                             * FOR NOW, all streetAddress value always have country name as last word
                             * so i only get last part of value
                             */
                            $companyCountry = explode(',', preg_replace('/[\n\t]+/', '', $jobs[$i]->streetAddress));
                            $payload["rv_vacancy_imported_company_country"] = preg_replace('/[\n\t\s+]+/', '', end($companyCountry));

                            /** Unset used key */
                            unset($jobs[$i]->streetAddress);
                        }

                        /** ACF external_url */
                        if (property_exists($jobs[$i], 'url') && !empty($jobs[$i]->url)) {
                            $payload['external_url'] = preg_replace('/[\n\t]+/', '', $jobs[$i]->url);

                            /** Unset used key */
                            unset($jobs[$i]->url);
                        }

                        /** ACF apply_from_this_platform */
                        $payload['apply_from_this_platform'] = false;

                        /** ACF expired_at */
                        if (property_exists($jobs[$i], 'expirationdate') && !empty($jobs[$i]->expirationdate)) {
                            $payload['expired_at'] = \DateTime::createFromFormat('d-n-Y', preg_replace('/[\n\t]+/', '', $jobs[$i]->expirationdate))->setTime(23, 59, 59)->format('Y-m-d H:i:s');

                            /** Unset used key */
                            unset($jobs[$i]->expirationdate);
                        } else {
                            /** Set expired date 30 days from this data inputed */
                            $expiredAt = new \DateTimeImmutable();
                            $payload['expired_at'] = $expiredAt->modify("+30 days")->format("Y-m-d H:i:s");
                        }

                        $payload['salary_start'] = 0;
                        $payload['salary_end'] = 0;

                        /** ACF Salary */
                        if (property_exists($jobs[$i], 'salary') && !empty($jobs[$i]->salary)) {
                            $salary = explode(" ", $jobs[$i]->salary);
                            $salary = $salary[0];

                            if ($salary && is_numeric($salary)) {
                                $payload['salary_start'] = $salary;
                                $payload['salary_end'] = $salary;
                            }

                            /** Unset used key */
                            unset($jobs[$i]->salary);
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
                        if (property_exists($jobs[$i], 'company') && !empty($jobs[$i]->company) && strtolower($jobs[$i]->company) !== 'undisclosed') {
                            $payload["rv_vacancy_imported_company_name"] = preg_replace('/[\n\t]+/', '', $jobs[$i]->company);

                            /** Unset data key */
                            unset($jobs[$i]->company);
                        }

                        /** ACF Imported company_email */
                        if (property_exists($jobs[$i], 'email') && !empty($jobs[$i]->email)) {
                            $payload["rv_vacancy_imported_company_email"] = preg_replace('/[\n\t]+/', '', $jobs[$i]->email);

                            /** Unset used key */
                            unset($jobs[$i]->email);
                        }

                        /** Taxonomy working-hours
                         * i'm not unset this data since this tax is to fluids.
                         * so, this will still stored in unuset data if the response exists.
                         */
                        if (property_exists($jobs[$i], 'hoursPerWeek') && !empty($jobs[$i]->hoursPerWeek)) {
                            $taxonomy['working-hours'] = $this->_findWorkingHours(preg_replace('/[\n\t]+/', '', $jobs[$i]->hoursPerWeek));

                            /** Unset used key */
                            // unset($jobs[$i]->hoursPerWeek);
                        }

                        /** Taxonomy education */
                        if (property_exists($jobs[$i], 'education') && !empty($jobs[$i]->education)) {
                            $taxonomy['education'] = $this->_findEducation($this->_taxonomyKey['education'], preg_replace('/[\n\t]+/', '', $jobs[$i]->education));

                            /** Unset used key */
                            unset($jobs[$i]->education);
                        }

                        /** Taxonomy experience */
                        if (property_exists($jobs[$i], 'experience') && !empty($jobs[$i]->experience) && $jobs[$i]->experience[0] !== 'NotSpecified') {
                            $taxonomy['experiences'] = $this->_findExperiences(preg_replace('/[\n\t]+/', '', $jobs[$i]->experience[0]));

                            /** Unset used key */
                            unset($jobs[$i]->experience);
                        }

                        /** Taxonomy Status */
                        if (property_exists($jobs[$i], 'isActive') && !empty($jobs[$i]->isActive)) {
                            /** Check if expired */
                            if ($payload['expired_at']) {
                                $today = new \DateTime("now");
                                $endOfTheDay = $today->setTime(23, 59, 59);

                                if (strtotime($payload['expired_at']) > $today->setTime(23, 59, 59)->getTimestamp()) {
                                    $taxonomy['status'] = $this->_findStatus(true);
                                    error_log($arguments['post_name'] . ' not expired - ' . $payload['expired_at'] . ' > ' . $endOfTheDay->format('Y-m-d H:i:s') . ' - ' . $taxonomy['status']);
                                } else {
                                    $taxonomy['status'] = $this->_findStatus(false);
                                    error_log($arguments['post_name'] . ' expired - ' . $payload['expired_at'] . ' < ' . $endOfTheDay->format('Y-m-d H:i:s') . ' - ' . $taxonomy['status']);
                                }
                            } else {
                                $taxonomy['status'] = $this->_findStatus($jobs[$i]->isActive);
                                error_log($arguments['post_name'] . ' is active - ' . $taxonomy['status']);
                            }

                            /** Unset used key */
                            // unset($jobs[$i]->isActive);
                        } else {
                            $taxonomy['status'] = $this->_findStatus(false);
                        }

                        /** Taxonomy Role */
                        if (property_exists($jobs[$i], 'category') && !empty($jobs[$i]->category)) {
                            $taxonomy['role'] = $this->_findRole(preg_replace('/[\n\t]+/', '', $jobs[$i]->category));

                            /** Unset used key */
                            unset($jobs[$i]->category);
                        }

                        foreach ($jobs[$i] as $key => $value) {
                            if (!in_array($key, ['title', 'datePosted', 'description', 'city', 'streetAddress', 'url', 'expirationdate', 'hoursPerWeek', 'education', 'experience', 'isActive', 'category', 'company', 'email', 'salary'])) {
                                $unusedData[$key] = $value;
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
                            // $vacancy->setStatus('open');

                            /** Set expired date 30 days from this data inputed */
                            // $expiredAt = new \DateTimeImmutable();
                            // $expiredAt = $expiredAt->modify("+30 days")->format("Y-m-d H:i:s");
                            // $vacancy->setProp("expired_at", $expiredAt);

                            foreach ($payload as $acf_field => $acfValue) {
                                $vacancy->setProp($acf_field, $acfValue, is_array($acfValue));
                            }

                            /** IF MAP API IS ENABLED */
                            if (defined('ENABLE_MAP_API') && ENABLE_MAP_API == true) {
                                /** Calc coordinate */
                                if (isset($payload["placement_city"]) && isset($payload["placement_address"])) {
                                    $vacancy->setCityLongLat($payload["placement_city"], true);
                                    $vacancy->setAddressLongLat($payload["placement_address"]);
                                    $vacancy->setDistance($payload["placement_city"], $payload["placement_city"] . " " . $payload["placement_address"]);
                                }
                            }

                            /** store unused to post meta */
                            update_post_meta($post, 'rv_vacancy_unused_data', $unusedData);
                            update_post_meta($post, 'rv_vacancy_source', 'flexfeed');
                        } catch (\Exception $error) {
                            error_log($error);
                        }
                    }
                }
            } catch (\Exception $e) {
                error_log('Exception in fetch flexfeed API - ' . $e->getMessage());
            } catch (\Throwable $throw) {
                error_log('All thrown exception in fetch flexfeed API - ' . $throw->getMessage());
            }
        } else {
            throw new Exception("Please specify the source!");
        }
    }

    private function _validate($fetchValue)
    {
        $args = [
            'post_type' => 'vacancy',
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => 'rv_vacancy_imported_source_id',
                    'value' => $fetchValue,
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

    private function _findWorkingHours($fetchValue)
    {
        $terms = $this->_terms['working-hours'];
        $alternative = strtolower(preg_replace('/\s+/', '', $fetchValue));
        $alternative = strtolower(preg_replace('/(-+)/', '', $alternative));

        foreach ($terms as $key => $value) {
            /** Check if exactly same */
            if ($value['name'] == strtolower($fetchValue) || $value['slug'] == strtolower($fetchValue) || $value['name'] == strtolower($alternative) || $value['slug'] == strtolower($alternative)) {
                return $value['term_id'];
            }

            /** Check if containt */
            if (strpos($value['name'], strtolower($fetchValue)) !== false || strpos($value['slug'], strtolower($fetchValue)) !== false || strpos($value['name'], strtolower($alternative)) !== false || strpos($value['name'], $alternative) !== false) {
                return $value['term_id'];
            }

            /** Check if between */
            $toCompare = explode('-', preg_replace('/\s+/', '', $value['name']));

            if (is_numeric($toCompare[0])) {
                if (isset($toCompare[1]) && is_numeric($toCompare[1])) {
                    if (is_numeric($fetchValue)) {
                        if ((int)$toCompare[0] <= (int)$fetchValue && (int)$fetchValue <= (int)$toCompare[1]) {
                            return (int)$value['term_id'];
                        }
                    }
                } else {
                    if (is_numeric($fetchValue)) {
                        if ((int)$toCompare[0] <= (int)$fetchValue) {
                            return (int)$value['term_id'];
                        }
                    }
                }
            }
        }

        /** Check using term_exists query */
        $termExists = term_exists(strtolower(preg_replace('/\s+/', '-', $fetchValue)), 'working-hours');

        if ($termExists) {
            if (is_array($termExists)) {
                return $termExists['term_id'];
            }
            return $termExists;
        } else {
            /** Check the alternative */
            $termExists = term_exists(strtolower($alternative), 'working-hours');
            if ($termExists) {
                return $termExists;
            }

            $newTerm = wp_insert_term($fetchValue, 'working-hours', []);

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

    private function _findEducation($taxonomy, $fetchValue)
    {
        $terms = $this->_terms['education'];
        $alternative = strtolower(preg_replace('/\s+/', '-', $fetchValue));
        $alternative = strtolower(preg_replace('/(-+)/', '-', $alternative));

        foreach ($terms as $key => $value) {
            switch ($value) {
                case $value['name'] == strtolower($fetchValue):
                case $value['slug'] == strtolower($fetchValue):
                case $value['name'] == strtolower($alternative):
                case $value['slug'] == strtolower($alternative):
                    return $value['term_id'];
            }
        }

        /** Check using term_exists query */
        $termExists = term_exists(strtolower(preg_replace('/\s+/', '-', $fetchValue)), 'education');
        if ($termExists) {
            if (is_array($termExists)) {
                return $termExists['term_id'];
            }
            return $termExists;
        } else {
            /** Check the alternative */
            $termExists = term_exists(strtolower($alternative), 'education');
            if ($termExists) {
                return $termExists;
            }

            $newTerm = wp_insert_term($fetchValue, 'education', []);

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

    private function _findExperiences($fetchValue)
    {
        $terms = $this->_terms['experiences'];
        $alternative = strtolower(preg_replace('/\s+/', '-', $fetchValue));
        $alternative = strtolower(preg_replace('/(-+)/', '-', $alternative));

        foreach ($terms as $key => $value) {
            switch ($value) {
                case $value['name'] == strtolower($fetchValue):
                case $value['slug'] == strtolower($fetchValue):
                case $value['name'] == strtolower($alternative):
                case $value['slug'] == strtolower($alternative):
                    return $value['term_id'];
            }
        }

        /** Check using term_exists query */
        $termExists = term_exists(strtolower(preg_replace('/\s+/', '-', $fetchValue)), 'experiences');
        if ($termExists) {
            if (is_array($termExists)) {
                return $termExists['term_id'];
            }
            return $termExists;
        } else {
            /** Check the alternative */
            $termExists = term_exists(strtolower($alternative), 'experiences');
            if ($termExists) {
                return $termExists;
            }

            $newTerm = wp_insert_term($fetchValue, 'experiences', []);

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

    private function _findStatus($fetchValue)
    {
        $termOpen = get_term_by('slug', 'open', 'status', 'OBJECT');
        $termClose = get_term_by('slug', 'close', 'status', 'OBJECT');

        if ($fetchValue || $fetchValue == "true") {
            return $termOpen->term_id;
        } else {
            return $termClose->term_id;
        }
    }

    private function _findRole($fetchValue)
    {
        $terms = $this->_terms['role'];
        $alternative = strtolower(preg_replace('/\s+/', '-', $fetchValue));
        $alternative = strtolower(preg_replace('/(-+)/', '-', $alternative));

        foreach ($terms as $key => $value) {
            switch ($value) {
                case $value['name'] == strtolower($fetchValue):
                case $value['slug'] == strtolower($fetchValue):
                case $value['name'] == strtolower($alternative):
                case $value['slug'] == strtolower($alternative):
                    return $value['term_id'];
            }
        }

        /** Check using term_exists query */
        $termExists = term_exists(strtolower(preg_replace('/\s+/', '-', $fetchValue)), 'role');
        if ($termExists) {
            if (is_array($termExists)) {
                return $termExists['term_id'];
            }
            return $termExists;
        } else {
            /** Check the alternative */
            $termExists = term_exists(strtolower($alternative), 'role');
            if ($termExists) {
                return $termExists;
            }

            $newTerm = wp_insert_term($fetchValue, 'role', []);

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
