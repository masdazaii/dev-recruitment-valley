<?php

namespace Vacancy\Import\Jobfeed;

require_once get_stylesheet_directory() . "/vendor/autoload.php";

use Exception;
use Helper\Maphelper;
use Helper\StringHelper;
use Vacancy\Vacancy;
use WP_Error;
use Aws\Exception\AwsException;

class JobfeedController
{
    private $_taxonomy;
    private $_terms;

    public function __construct()
    {
        $this->_taxonomy = ['working-hours', 'education', 'type', 'experiences', 'sector', 'role', 'location', 'status'];
    }

    public function import($limit = 'all', $offset = 0)
    {
        $this->_getTerms();

        $this->_parse($limit, $offset);
    }

    private function _parse($limit, $start)
    {
        try {

            $dateNow = date('Y-m-d');

            $s3 = new \Aws\S3\S3Client([
                'region' => 'eu-central-1',
                'version' => 'latest',
                'credentials' => [
                    'key' => JOBFEED_KEY ?? '',
                    'secret' => JOBFEED_SECRET_KEY ?? '',
                ]
            ]);

            $key = "NL/daily/" . $dateNow . "/jobs_new.0.jsonl.gz";
            $fileName = wp_upload_dir()["basedir"] . '/aws/job/new/' . $dateNow . '.gz';
            error_log(json_encode(wp_upload_dir()));

            $result = $s3->getObject([
                'Bucket' => 'jobfeed-intelligence-group',
                'Key'    => $key,
                'SaveAs' => $fileName,
            ]);

            // Raising this value may increase performance
            $buffer_size = 4096000; // read 4kb at a time
            $out_file_name = str_replace('.gz', '.jsonl', $fileName);

            // Open our files (in binary mode)
            $file = gzopen($fileName, 'rb');
            $out_file = fopen($out_file_name, 'wb');

            // Keep repeating until the end of the input file
            while (!gzeof($file)) {
                // Read buffer-size bytes
                // Both fwrite and gzread and binary-safe
                fwrite($out_file, gzread($file, $buffer_size));
            }

            // Files are done, close files
            fclose($out_file);
            gzclose($file);


            $vacancies = file_get_contents($out_file_name);
            $vacancies = explode("\n", $vacancies);
            // print('<pre>' . print_r($vacancies, true) . '</pre>');

            $i = 0;
            foreach ($vacancies as $vacancy) {
                /** Decode vacancy */
                $vacancy = json_decode($vacancy);

                /** Check "expired" property exists */
                if (isset($vacancy->expired)) {
                    /** Check "expired" property value, if false then skip this one */
                    if (!$vacancy->expired) {
                        $i++;
                        continue;
                    }
                } else {
                    $i++;
                    continue;
                }

                /** ACF expired_at
                 * Check if vacancy "expired_date" property is expired or not
                 */
                if (!isset($vacancy->expiration_date)) {
                    /** Set expired date 30 days from this data inputed */
                    $expiredAt = new \DateTimeImmutable();
                    $payload['expired_at'] = $expiredAt->modify("+30 days")->format("Y-m-d H:i:s");
                } else {
                    $payload['expired_at'] = $vacancy->expiration_date;
                    $today = new \DateTimeImmutable();

                    if (strtotime($vacancy->expiration_date) > $today->setTime(23, 59, 59)->getTimestamp()) {
                        $taxonomy['status'] = $this->_findStatus('processing');

                        /** Unset used key */
                        // unset($vacancy->expiration_date);
                    } else {
                        error_log('[Flexfeed] - Vacancy is expired, vacancy will not stored to recruitment valley database. RequsitionId : ' . (property_exists($jobs[$i], 'requisitionId') ? $jobs[$i]->requisitionId : '"NoRequisitionIdFound"') . ' & Title : ' . (property_exists($jobs[$i], 'title') ? $jobs[$i]->title : '"NoTitleFound"') . ' - index : ' . $i);
                        $i++;
                        continue;
                    }
                }

                /** Validate - check if data id is exists */
                if (!isset($vacancy->job_id)) {
                    error_log('Vacancy didn\'t has job id');
                    $i++;
                    continue;
                }

                /** Validate - check if data is already exists */
                if (!$this->_validate($vacancy->job_id)) {
                    error_log('[Flexfeed] - Vacancy already exists. JobID : ' . (isset($vacancy->job_id) ? $vacancy->job_id : 'No_job_id_found') . '. Title : ' . (isset($vacancy->job_title) ? $vacancy->job_title : 'no_job_title_found') . ' - index : ' . $i);
                    $i++;
                    continue;
                }

                /** ACF Job_id */
                $payload['rv_vacancy_imported_source_id']   = $vacancy->job_id;
                /** Unset used key : job_id */
                unset($vacancy->job_id);

                /** Post Data Title */
                if (isset($vacancy->job_title)) {
                    $arguments['post_title'] = preg_replace('/[\n\t]+/', '', $vacancy->job_title);

                    /** Post Data post_name */
                    $slug = StringHelper::makeSlug(preg_replace('/[\n\t]+/', '', $vacancy->job_title, '-', 'lower'));
                    $arguments['post_name'] = 'jobfeed-' . $slug;

                    /** Unset used key */
                    unset($vacancy->job_title);
                }

                /** Post Data post_date */
                if (isset($vacancy->date)) {
                    $arguments['post_date'] = date('Y-m-d H:i:s', strtotime(preg_replace('/[\n\t]+/', '', $vacancy->date)));

                    /** Unset used key */
                    unset($vacancy->date);
                }

                /** ACF Description */
                if (isset($vacancy->full_text)) {
                    $payload['description'] = preg_replace('/[\n\t]+/', '', $vacancy->full_text);

                    /** Unset used key */
                    unset($vacancy->full_text);
                } else {
                    $payload['description'] = '';
                    if (isset($vacancy->employer_description)) {
                        $payload['description'] .= $vacancy->employer_description;

                        /** Unser used key */
                        unset($vacancy->employer_description);
                    }

                    if (isset($vacancy->conditions_description)) {
                        $payload['description'] .= $vacancy->conditions_description;

                        /** Unset used key */
                        unset($vacancy->conditions_description);
                    }

                    if (isset($vacancy->candidate_description)) {
                        $payload['description'] .= $vacancy->candidate_description;

                        /** Unset used key */
                        unset($vacancy->candidate_description);
                    }

                    if (isset($vacancy->job_description)) {
                        $payload['description'] .= $vacancy->job_description;

                        /** Unset used key */
                        unset($vacancy->job_description);
                    }
                }

                /** ACF Salary */
                $payload['salary_start'] = 0;
                $payload['salary_end'] = 0;

                if (isset($vacancy->salary)) {
                    // Salary Start
                    if (isset($vacancy->salary_from)) {
                        $payload['salary_start'] = (int)$vacancy->salary_from;

                        /** Unset used key */
                        unset($vacancy->salary_from);
                    } else {
                        $payload['salary_start'] = (int)$vacancy->salary_from;
                    }

                    // Salary End
                    if (isset($vacancy->salary_to)) {
                        $payload['salary_end'] = (int)$vacancy->salary_to;

                        /** Unset used key */
                        unset($vacancy->salary_to);
                    } else {
                        $payload['salary_end'] = (int)$vacancy->salary_from;
                    }

                    /** Unset used key */
                    // unset($vacancy->salary_from);
                }

                if (isset($vacancy->salary_from)) {
                    $payload['salaryStart'] = (int)$vacancy->salary_from;

                    /** Unset used key */
                    // unset($vacancy->salary_from);
                }

                if (isset($vacancy->salary_to)) {
                    $payload['salary_end'] = (int)$vacancy->salary_to;

                    /** Unset used key */
                    // unset($vacancy->salary_to);
                }

                /** ACF External Url
                 * there is 2 property for external url :
                 * 1. apply_url
                 * 2. source_url
                 */
                if (isset($vacancy->apply_url)) {
                    $payload['external_url'] = preg_replace('/[\n\t]+/', '', $vacancy->apply_url);

                    /** Unset used key */
                    unset($vacancy->apply_url);
                }

                /** ACF Placement Address
                 * sometimes, there is property "location_remote_possible"
                 * if this property value is false, set placement with organization address
                 */
                if (isset($vacancy->location_remote_possible) && (!empty($vacancy->location_remote_possible) || $vacancy->location_remote_possible == false)) {
                    /** Set Placement Address */
                    if (isset($vacancy->organization_address)) {
                        $payload['placement_address'] = preg_replace('/[\n\t]+/', '', $vacancy->organization_address);

                        /** Unset used key */
                        unset($vacancy->organization_address);
                    }

                    /** Set Taxonomy Location to remote */
                    // $taxonomy['location'] = $this->_findLocation('on-site');
                }

                /** ACF Placement Address Coordinate
                 * There's 2 property for coordinate
                 * 1. location_coordinate
                 * 2. organization_coordinate
                 */
                if (isset($vacancy->location_coordinates)) {
                    list($latitude, $longitude) = explode(',', $vacancy->location_coordinates, 2);
                    $payload['placement_address_latitude']   = preg_replace('/[\n\t]+/', '', $latitude);
                    $payload['placement_address_longitude']  = preg_replace('/[\n\t]+/', '', $longitude);
                    $payload['city_latitude']   = preg_replace('/[\n\t]+/', '', $latitude);
                    $payload['city_longitude']  = preg_replace('/[\n\t]+/', '', $longitude);

                    /** Unset used key */
                    unset($vacancy->location_coordinates);
                }

                /** ACF Paid
                 * set all import to free
                 */
                $payload["is_paid"] = false;

                /** ACF Imported
                 * set all is_imported acf data to "true"
                 */
                $payload["rv_vacancy_is_imported"] = "1";

                /** ACF Imported rv_vacancy_imported_company_name */
                if (isset($vacancy->organization_name)) {
                    $payload["rv_vacancy_imported_company_name"] = preg_replace('/[\n\t]+/', '', $vacancy->organization_name);

                    /** Unset data key */
                    unset($vacancy->organization_name);

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

                // echo '<pre>';
                // var_dump(json_decode($vacancy)->job_id);
                // echo '</pre>';
            }
        } catch (AwsException $e) {
            echo '<pre>';
            var_dump($e->getMessage());
            echo '</pre>';
        }
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

    private function _findStatus($fetchValue)
    {
        $termOpen = get_term_by('slug', 'open', 'status', 'OBJECT');
        $termClose = get_term_by('slug', 'close', 'status', 'OBJECT');

        if ($fetchValue) {
            if ($fetchValue == "true") {
                return $termOpen->term_id;
            } else {
                $term = get_term_by('slug', $fetchValue, 'status', 'OBJECT');
                if ($term) {
                    return $term->term_id;
                } else {
                    throw new Exception("Term '{$fetchValue}' didn\'t exists!");
                }
            }
        } else {
            return $termClose->term_id;
        }
    }
}
