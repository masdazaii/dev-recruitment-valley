<?php

namespace Vacancy\Import\Jobfeed;

require_once get_stylesheet_directory() . "/vendor/autoload.php";

use Exception;
use Helper\Maphelper;
use Helper\StringHelper;
use Helper\CalculateHelper;
use Model\Option;
use Model\Term;
use BD\Emails\Email;
use Constant\Message;
use Vacancy\Vacancy;
use WP_Error;
use Aws\Exception\AwsException;

class JobfeedController
{
    private $_taxonomy;
    private $_terms;
    private $_keywords;
    private $_message;

    public function __construct()
    {
        $this->_taxonomy = ['working-hours', 'education', 'type', 'experiences', 'sector', 'role', 'location', 'status'];
    }

    public function import($parameter, $limit = 'all', $offset = 0)
    {
        /** Get Mapped Keyword from option */
        $this->_getMappedKeyword();

        $this->_getTerms();

        $date = new \DateTime($parameter['date'] ?? "now");
        $parameter['date'] = $date->format('Y-m-d');
        $this->_parse($parameter, $limit, $offset);
    }

    private function _parse($parameter, $limit, $start)
    {
        try {
            $dateNow = $parameter['date'] ?? date('Y-m-d');

            if (isset($parameter['test']) && $parameter['test'] == true) {
                error_log('Jobfeed test! ' . $parameter['date']);
                $fileName = wp_upload_dir()["basedir"] . '/aws/job/new/' . $dateNow . '.gz';
                error_log(json_encode(wp_upload_dir()));

                // Raising this value may increase performance
                $out_file_name = str_replace('.gz', '.jsonl', $fileName);
                $vacancies = file_get_contents($out_file_name);
                $vacancies = explode("\n", $vacancies);
            } else {
                error_log('Jobfeed live! ' . $parameter['date']);
                $s3 = new \Aws\S3\S3Client([
                    'region' => 'eu-central-1',
                    'version' => 'latest',
                    'credentials' => [
                        'key' => get_field('aws_key_id', 'option'),
                        'secret' => get_field('aws_secret_key', 'option'),
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
            }

            $i = 0;
            $imported = 0;

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

            foreach ($vacancies as $vacancy) {
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

                /** Decode vacancy */
                $vacancy = json_decode($vacancy);

                if (isset($vacancy)) {
                    /** Check "expired" property exists */
                    if (isset($vacancy->expired)) {
                        /** Check "expired" property value, if false then skip this one */
                        if ($vacancy->expired) {
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
                        // $expiredAt = new \DateTimeImmutable();
                        // $payload['expired_at'] = $expiredAt->modify("+30 days")->format("Y-m-d H:i:s");

                        /** Set expired date to 3 years for now.
                         * This is because currently rv is detect empty expiration date as expired vacancy.
                         * This should be temporary solution, and would recommend to change the expiration vacancy flow.
                         */
                        $today = new \DateTimeImmutable();
                        $payload['expired_at'] = $today->modify('+3 years')->format('Y-m-d H:i:s');
                    } else {
                        $payload['expired_at'] = $vacancy->expiration_date;
                        $today = new \DateTimeImmutable();

                        if (strtotime($vacancy->expiration_date) <= $today->setTime(23, 59, 59)->getTimestamp()) {
                            /** Set expired date to 3 years for now.
                             * This is because currently rv is detect empty expiration date as expired vacancy.
                             * This should be temporary solution, and would recommend to change the expiration vacancy flow.
                             */
                            $payload['expired_at'] = $today->modify('+3 years')->format('Y-m-d H:i:s');
                            // error_log('[Jobfeed] - Vacancy is expired, vacancy will not stored to recruitment valley database. RequsitionId : ' . (isset($vacancy->job_id) ? $vacancy->job_id : '"NoJobIdFound"') . ' & Title : ' . (isset($vacancy->job_title) ? $vacancy->job_title : '"NoTitleFound"') . ' - index : ' . $i);
                            // $i++;
                            // continue;
                        }
                    }

                    /** Set status to processing */
                    $taxonomy['status'] = $this->_findStatus('processing');

                    /** Validate - check if data id is exists */
                    if (!isset($vacancy->job_id)) {
                        error_log('Vacancy didn\'t has job id');
                        $i++;
                        continue;
                    }

                    if (!$this->_validate($vacancy->job_id)) {
                        error_log('[Jobfeed] - Vacancy already exists. JobID : ' . (isset($vacancy->job_id) ? $vacancy->job_id : 'No_job_id_found') . '. Title : ' . (isset($vacancy->job_title) ? $vacancy->job_title : 'no_job_title_found') . ' - index : ' . $i);
                        $i++;
                        continue;
                    }

                    /** ACF Job_id */
                    $payload['rv_vacancy_imported_source_id']   = $vacancy->job_id;
                    /** Unset used key : job_id */
                    unset($vacancy->job_id);

                    /** Post Data Title */
                    if (isset($vacancy->job_title)) {
                        // $arguments['post_title'] = preg_replace('/[\n\t]+/', '', $vacancy->job_title);
                        $arguments['post_title'] = preg_replace('/[\n]+/', '<br>', $vacancy->job_title);
                        $arguments['post_title'] = preg_replace('/[\t]+/', '&emsp;', $arguments['post_title']);

                        /** Post Data post_name */
                        $slug = StringHelper::makeSlug(preg_replace('/[\n\t]+/', '', $vacancy->job_title), '-', 'lower');
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
                        // $payload['description'] = preg_replace('/[\n\t]+/', '', $vacancy->full_text);
                        // $payload['description'] = preg_replace('/[\n]+/', '<br>', $vacancy->full_text);
                        // $payload['description'] = preg_replace('/[\t]+/', '&emsp;', $payload['description']);
                        $payload['description'] = $vacancy->full_text;

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

                    /** ACF City */
                    if (isset($vacancy->location_name)) {
                        // $payload['placement_city'] = preg_replace('/[\n\t]+/', '', $vacancy->location_name);
                        $payload['placement_city'] = $vacancy->location_name;

                        /** Unset used key */
                        unset($vacancy->location_name);
                    }

                    /** ACF Placement Address
                     * sometimes, there is property "location_remote_possible"
                     * if this property value is false, set placement with organization address
                     */
                    if (isset($vacancy->location_remote_possible) && (!empty($vacancy->location_remote_possible) || $vacancy->location_remote_possible == false)) {
                        /** Set Placement Address */
                        if (isset($vacancy->organization_address)) {
                            // $payload['placement_address'] = preg_replace('/[\n\t]+/', '', $vacancy->organization_address);
                            $payload['placement_address'] = $vacancy->organization_address;

                            /** Unset used key */
                            unset($vacancy->organization_address);
                        }

                        /** Set Taxonomy Location to remote */
                        $taxonomy['location'] = $this->_findLocation('on-site');
                    } else if (isset($vacancy->location_remote_possible) && $vacancy->location_remote_possible) {
                        $taxonomy['location'] = $this->_findLocation('hybride');
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

                    /** ACF Language */
                    if (isset($vacancy->language)) {
                        $payload['rv_vacancy_language'] = $vacancy->language;
                    }

                    /** ACF Imported rv_vacancy_imported_company_name */
                    if (isset($vacancy->organization_name)) {
                        $payload["rv_vacancy_imported_company_name"] = preg_replace('/[\n\t]+/', '', $vacancy->organization_name);

                        /** Unset data key */
                        // unset($vacancy->organization_name);

                        /** ACF Imported rv_vacancy_imported_company_email */
                        if (isset($vacancy->organization_email)) {
                            $payload["rv_vacancy_imported_company_email"] = preg_replace('/[\n\t]+/', '', $vacancy->organization_email);

                            /** Unset data key */
                            // unset($vacancy->organization_email);
                        }

                        /** ACF Imported company_city */
                        if (isset($vacancy->organization_location_name)) {
                            $payload["rv_vacancy_imported_company_city"] = preg_replace('/[\n\t]+/', '', $vacancy->organization_location_name);

                            /** Unset data key */
                            // unset($vacancy->organization_location_name);
                        }

                        /** ACF Imported company_size */
                        if (isset($vacancy->organization_size)) {
                            $payload["rv_vacancy_imported_company_total_employees"] = preg_replace('/[\n\t]+/', '', $vacancy->organization_size->label);

                            /** Unset data key */
                            // unset($vacancy->organization_size);
                        }
                    } else if (isset($vacancy->advertiser_name)) {
                        $payload["rv_vacancy_imported_company_name"] = preg_replace('/[\n\t]+/', '', $vacancy->advertiser_name);

                        /** Unset data key */
                        // unset($vacancy->advertiser_name);

                        /** ACF Imported rv_vacancy_imported_company_email */
                        if (isset($vacancy->advertiser_email)) {
                            $payload["rv_vacancy_imported_company_email"] = preg_replace('/[\n\t]+/', '', $vacancy->advertiser_email);

                            /** Unset data key */
                            // unset($vacancy->advertiser_email);
                        }

                        /** ACF Imported company_city */
                        if (isset($vacancy->advertiser_location)) {
                            $payload["rv_vacancy_imported_company_city"] = preg_replace('/[\n\t]+/', '', $vacancy->advertiser_location);

                            /** Unset data key */
                            // unset($vacancy->advertiser_location);
                        }
                    }

                    /** ACF Imported Company Country */
                    if (array_key_exists('rv_vacancy_imported_company_city', $payload) && !empty($payload['rv_vacancy_imported_company_city'])) {

                        if (isset($vacancy->organization_address)) {
                            $mapAddress = preg_replace('/[\n\t]+/', '', $vacancy->organization_address);
                        } else {
                            $mapAddress = $payload['rv_vacancy_imported_company_city'];
                        }

                        /** IF MAP API IS ENABLED */
                        if (defined('ENABLE_MAP_API') && ENABLE_MAP_API == true) {
                            try {
                                $payload['rv_vacancy_imported_company_country'] = Maphelper::reverseGeoData('address', 'nl', 'country', [], $mapAddress)['long_name'];
                            } catch (\WP_Error $wperror) {
                                error_log($wperror->get_error_message());
                            } catch (\Exception $error) {
                                error_log($error->getMessage());
                            } catch (\Throwable $th) {
                                error_log($th->getMessage());
                            }
                        }
                    }

                    /** Taxonomy Working-hours */
                    if (isset($vacancy->hours_per_week_from)) {
                        if (isset($vacancy->hours_per_week_to)) {
                            if ((int)$vacancy->hours_per_week_from < (int)$vacancy->hours_per_week_to) {
                                // $taxonomy['working-hours'] = $this->_findWorkingHours($vacancy->hours_per_week_from . '-' . $vacancy->hours_per_week_to);

                                /** Check term.
                                 * 1. Check by end of working hour.
                                 * 2. If term not exists, check by start of working hour.
                                 * 3. if both false, check / create term by both.
                                 */
                                $termByWorkingHour = $this->_findWorkingHours($vacancy->hours_per_week_to, false);

                                if ($termByWorkingHour) {
                                    $taxonomy['working-hours'] = $termByWorkingHour;
                                } else {
                                    $termByWorkingHour = $this->_findWorkingHours($vacancy->hours_per_week_from, false);

                                    if ($termByWorkingHour) {
                                        $taxonomy['working-hours'] = $termByWorkingHour;
                                    } else {
                                        $termByWorkingHour = $this->_findWorkingHours($vacancy->hours_per_week_from . '-' . $vacancy->hours_per_week_to);
                                    }
                                }
                            } else {
                                $taxonomy['working-hours'] = $this->_findWorkingHours($vacancy->hours_per_week_from);
                            }

                            /** Unset used key */
                            // unset($vacancy->hours_per_week_to);
                        } else {
                            $taxonomy['working-hours'] = $this->_findWorkingHours($vacancy->hours_per_week_from);
                        }

                        /** Unset used key */
                        // unset($vacancy->hours_per_week_from);
                    }

                    /** Taxonomy Education */
                    if (isset($vacancy->education_level)) {
                        if (is_array($vacancy->education_level)) {
                            if (array_key_exists('label', $vacancy->education_level)) {
                                $taxonomy['education'] = $this->_findEducation($vacancy->education_level['label']);

                                /** Unset used key */
                                unset($vacancy->education_level);
                            }
                        }
                    }

                    /** Taxonomy Experiences */
                    if (isset($vacancy->experience_level)) {
                        if (is_array($vacancy->experience_level)) {
                            if (array_key_exists('label', $vacancy->experience_level)) {
                                $taxonomy['experiences'] = $this->_findExperience($vacancy->experience_level['label']);

                                /** Unset used key */
                                unset($vacancy->experience_level);
                            }
                        }
                    }

                    /** Taxonomy Job Type */
                    if (isset($vacancy->employment_type)) {
                        if (is_array($vacancy->employment_type)) {
                            if (array_key_exists('label', $vacancy->employment_type)) {
                                $taxonomy['type'] = $this->_findEmploymentType($vacancy->employment_type['label']);

                                /** Unset used key */
                                unset($vacancy->employment_type);
                            }
                        }
                    }

                    /** Taxonomy Role */
                    $taxonomy['role'] = false;

                    if (isset($vacancy->profession)) {
                        if (is_array($vacancy->profession) && array_key_exists('label', $vacancy->profession)) {
                            /** Get closest role */
                            $taxonomy['role'] = CalculateHelper::calcLevenshteinCost($this->_keywords['role'], strtolower(preg_replace('/[\n\t]+/', '', $vacancy->profession['label'])), 5, 1, 1, 1, 'array');

                            /** Unset used key */
                            // unset($vacancy->profession);
                        } else if (is_object($vacancy->profession) && property_exists($vacancy->profession, 'label')) {
                            $taxonomy['role'] = CalculateHelper::calcLevenshteinCost($this->_keywords['role'], strtolower(preg_replace('/[\n\t]+/', '', $vacancy->profession->label)), 5, 1, 1, 1, 'array');
                        }
                    } else if (isset($vacancy->profession_group)) {
                        if (is_array($vacancy->profession_group) && array_key_exists('label', $vacancy->profession_group)) {
                            /** Get closest role */
                            $taxonomy['role'] = CalculateHelper::calcLevenshteinCost($this->_keywords['role'], strtolower(preg_replace('/[\n\t]+/', '', $vacancy->profession_group['label'])), 5, 1, 1, 1, 'array');

                            /** Unset used key */
                            // unset($vacancy->profession_group);
                        } else if (is_object($vacancy->profession_group) && property_exists($vacancy->profession, 'label')) {
                            $taxonomy['role'] = CalculateHelper::calcLevenshteinCost($this->_keywords['role'], strtolower(preg_replace('/[\n\t]+/', '', $vacancy->profession_group->label)), 5, 1, 1, 1, 'array');
                        }
                    } else if (isset($vacancy->profession_class)) {
                        if (is_array($vacancy->profession_class) && array_key_exists('label', $vacancy->profession_class)) {
                            /** Get closest role */
                            $taxonomy['role'] = CalculateHelper::calcLevenshteinCost($this->_keywords['role'], strtolower(preg_replace('/[\n\t]+/', '', $vacancy->profession_class['label'])), 5, 1, 1, 1, 'array');

                            /** Unset used key */
                            // unset($vacancy->profession_class);
                        } else if (is_object($vacancy->profession_class) && property_exists($vacancy->profession_class, 'label')) {
                            $taxonomy['role'] = CalculateHelper::calcLevenshteinCost($this->_keywords['role'], strtolower(preg_replace('/[\n\t]+/', '', $vacancy->profession_class->label)), 5, 1, 1, 1, 'array');
                        }
                    }

                    /** Set the role */
                    if (isset($taxonomy['role']) && $taxonomy['role'] != false) {
                        /** If calculation return empty role,
                         * if not set the role,
                         * if empty create new role */
                        if (empty($taxonomy['role'])) {
                            $termModel = new Term();
                            $taxonomy['role'] = $termModel->createTerm('role', $taxonomy['role'], []);
                        } else {
                            $option     = new Option(true);
                            $limitRole  = $option->getImportNumberRoleToSet();

                            $termCount  = 1;
                            $tempRole = [];
                            foreach ($taxonomy['role'] as $termID => $levenshteinCost) {
                                if ($termCount > $limitRole) {
                                    break;
                                }
                                $tempRole[] = $termID;

                                $termCount++;
                            }
                            $taxonomy['role'] = $tempRole;
                        }
                    } else {
                        $termModel = new Term();
                        if (isset($vacancy->profession)) {
                            if (is_array($vacancy->profession) && array_key_exists('label', $vacancy->profession)) {
                                $newRole = $vacancy->profession['label'];
                            } else if (is_object($vacancy->profession) && property_exists($vacancy->profession, 'label')) {
                                $newRole = $vacancy->profession->label;
                            }
                        } else if (isset($vacancy->profession_group)) {
                            if (is_array($vacancy->profession_group) && array_key_exists('label', $vacancy->profession_group)) {
                                $newRole = $vacancy->profession_group['label'];
                            } else if (is_object($vacancy->profession_group) && property_exists($vacancy->profession_group, 'label')) {
                                $newRole = $vacancy->profession_group->label;
                            }
                        } else if (isset($vacancy->profession_class)) {
                            if (is_array($vacancy->profession_class) && array_key_exists('label', $vacancy->profession_class)) {
                                $newRole = $vacancy->profession_class['label'];
                            } else if (is_object($vacancy->profession_class) && property_exists($vacancy->profession_class, 'label')) {
                                $newRole = $vacancy->profession_class->label;
                            }
                        }
                        $taxonomy['role'] = $termModel->createTerm('role', $newRole, []);
                    }

                    /** Taxonomy Sector */
                    $taxonomy['sector'] = false;

                    if (isset($vacancy->organization_industry)) {
                        if (is_array($vacancy->organization_industry) && array_key_exists('label', $vacancy->organization_industry)) {
                            /** Get closest role */
                            $taxonomy['sector'] = CalculateHelper::calcLevenshteinCost($this->_keywords['sector'], strtolower(preg_replace('/[\n\t]+/', '', $vacancy->organization_industry['label'])), 5, 1, 1, 1, 'array');

                            /** Unset used key */
                            // unset($vacancy->organization_industry);
                        } else if (is_object($vacancy->organization_industry) && property_exists($vacancy->organization_industry, 'label')) {
                            $taxonomy['sector'] = CalculateHelper::calcLevenshteinCost($this->_keywords['sector'], strtolower(preg_replace('/[\n\t]+/', '', $vacancy->organization_industry->label)), 5, 1, 1, 1, 'array');
                        }
                    }

                    /** Set the sector */
                    if (isset($taxonomy['sector']) && $taxonomy['sector'] != false) {
                        /** If calculation return empty sector,
                         * if not set the sector,
                         * if empty create new sector */
                        if (empty($taxonomy['sector'])) {
                            $termModel = new Term();
                            $taxonomy['sector'] = $termModel->createTerm('sector', $taxonomy['sector'], []);
                        } else {
                            $option     = new Option(true);
                            $limitSector  = $option->getImportNumberRoleToSet();

                            $termCount  = 1;
                            $tempSector = [];
                            foreach ($taxonomy['sector'] as $termID => $levenshteinCost) {
                                if ($termCount > $limitSector) {
                                    break;
                                }
                                $tempSector[] = $termID;

                                $termCount++;
                            }
                            $taxonomy['sector'] = $tempSector;
                            $payload['rv_vacancy_imported_company_sector'] = $tempSector[0];
                        }
                    } else {
                        $termModel = new Term();
                        if (isset($vacancy->organization_industry)) {
                            if (is_array($vacancy->organization_industry) && array_key_exists('label', $vacancy->organization_industry)) {
                                $newSector = $termModel->createTerm('sector', $vacancy->organization_industry['label'], []);
                            } else if (is_object($vacancy->organization_industry) && property_exists($vacancy->organization_industry, 'label')) {
                                $newSector = $termModel->createTerm('sector', $vacancy->organization_industry->label, []);
                            }
                        }
                        $taxonomy['sector'] = $newSector;
                        $payload['rv_vacancy_imported_company_sector'] = $newSector;
                    }

                    /** Mapping Unused data */
                    foreach ($vacancy as $propertyKey => $propertyValue) {
                        $unusedData[$propertyKey] = $propertyValue;
                    }

                    /** Insert data */
                    try {
                        $post = wp_insert_post($arguments, true);

                        if (is_wp_error($post)) {
                            error_log(json_encode($post->get_error_messages()));
                        }

                        $vacancy = new Vacancy($post);

                        $vacancy->setTaxonomy($taxonomy);

                        foreach ($payload as $acf_field => $acfValue) {
                            $vacancy->setProp($acf_field, $acfValue, is_array($acfValue));
                        }

                        /** store unused to post meta */
                        $now = new \DateTimeImmutable("now");
                        update_post_meta($post, 'rv_vacancy_unused_data', $unusedData);
                        update_post_meta($post, 'rv_vacancy_source', 'jobfeed');
                        update_post_meta($post, 'rv_vacancy_imported_at', $now->format('Y-m-d H:i:s'));


                        /** IF MAP API IS ENABLED */
                        if (defined('ENABLE_MAP_API') && ENABLE_MAP_API == true) {
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
                        }

                        /** Increase imported count */
                        $imported++;
                    } catch (\WP_Error $wperror) {
                        error_log($wperror->get_error_message());
                    } catch (\Exception $error) {
                        error_log($error->getMessage());
                    } catch (\Throwable $th) {
                        error_log($th->getMessage());
                    }
                    $i++;
                } else {
                    error_log('[Jobfeed] - vacancy is empty - index : ' . $i);
                }
            }

            /** Email if imported is more than 0 */
            if ($imported > 0) {
                /** Email to admin */
                $this->_message = new Message();

                /** Get recipient email */
                // $adminEmail = get_option('admin_email', false);
                $optionModel = new Option();
                $approvalMainRecipient  = $optionModel->getEmailApprovalMainAddress();
                $approvalCCRecipients = $optionModel->getEmailApprovalCC();
                $approvalBCCRecipients = $optionModel->getEmailApprovalBCC();

                /** Set headers */
                $headers[] = 'Content-Type: text/html; charset=UTF-8';

                /** Set cc / bcc */
                if (isset($approvalCCRecipients) && is_array($approvalCCRecipients)) {
                    $ccRecipient = [];
                    foreach ($approvalCCRecipients as $recipient) {
                        $ccRecipient[] = 'Cc: ' . $recipient['rv_email_approval_cc_address'];
                    }
                    array_unique($ccRecipient);
                    $headers = array_merge($headers, $ccRecipient);
                }

                if (isset($approvalBCCRecipients) && is_array($approvalBCCRecipients)) {
                    $bccRecipient = [];
                    foreach ($approvalBCCRecipients as $recipient) {
                        $bccRecipient[] = 'Bcc: ' . $recipient['rv_email_approval_bcc_address'];
                    }
                    array_unique($bccRecipient);
                    $headers = array_merge($headers, $bccRecipient);
                }

                $approvalArgs = [
                    // 'url' => menu_page_url('import-approval'),
                ];

                $content = Email::render_html_email('admin-new-vacancy-approval.php', $approvalArgs);
                wp_mail($approvalMainRecipient, ($this->_message->get('vacancy.approval_subject') ?? 'Approval requested - RecruitmentValley'), $content, $headers);
            }
        } catch (AwsException $e) {
            error_log('Exception in fetch AWS S3 Bucket - ' . $e->getMessage());
        } catch (\Exception $e) {
            error_log('Exception in fetch AWS S3 Bucket - ' . $e->getMessage());
        } catch (\Throwable $throw) {
            error_log('All thrown exception in fetch AWS S3 Bucket - ' . $throw->getMessage());
        }
    }

    public function expire($parameter, $limit = 'all', $offset = 0)
    {
        try {
            $date = new \DateTime($parameter['date'] ?? "now");
            $parameter['date'] = $date->format('Y-m-d');
            $dateNow = $parameter['date'] ?? date('Y-m-d');

            if (isset($parameter['test']) && $parameter['test'] == true) {
                error_log('Expired Jobfeed test! ' . $parameter['date']);
                $fileName = wp_upload_dir()["basedir"] . '/aws/job/expired/' . $dateNow . '.gz';
                error_log(json_encode(wp_upload_dir()));

                // Raising this value may increase performance
                $out_file_name = str_replace('.gz', '.jsonl', $fileName);
                $vacancies = file_get_contents($out_file_name);
                $vacancies = explode("\n", $vacancies);
            } else {
                error_log('Expired Jobfeed live! ' . $parameter['date']);
                $s3 = new \Aws\S3\S3Client([
                    'region' => 'eu-central-1',
                    'version' => 'latest',
                    'credentials' => [
                        'key' => get_field('aws_key_id', 'option'),
                        'secret' => get_field('aws_secret_key', 'option'),
                    ]
                ]);

                $key = "NL/daily/" . $dateNow . "/jobs_expired.0.jsonl.gz";
                $fileName = wp_upload_dir()["basedir"] . '/aws/job/expired/' . $dateNow . '.gz';
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
            }

            $i = 0;

            foreach ($vacancies as $vacancy) {
                /** Decode vacancy */
                $vacancy = json_decode($vacancy);

                if (isset($vacancy)) {
                    if (!isset($vacancy->job_id)) {
                        error_log('Vacancy didn\'t has job id');
                        $i++;
                        continue;
                    }

                    $validate = $this->_validate($vacancy->job_id, true);
                    if (!$validate) {
                        error_log('[Expired Jobfeed] - Vacancy not exists. JobID : ' . (isset($vacancy->job_id) ? $vacancy->job_id : 'No_job_id_found') . '. Expiration Date : ' . (isset($vacancy->expiration_date) ? $vacancy->expiration_date : 'no_expiration_date_found') . ' - index : ' . $i);
                        $i++;
                        continue;
                    }

                    error_log('[Expired Jobfeed] - Vacancy exists. Vacancy_id : ' . $validate . ' - JobID : ' . (isset($vacancy->job_id) ? $vacancy->job_id : 'No_job_id_found') . '. Expiration Date : ' . (isset($vacancy->expiration_date) ? $vacancy->expiration_date : 'no_expiration_date_found') . ' - index : ' . $i);
                    /** Set expired_at */
                    $payload['expired_at']  = $vacancy->expiration_date;
                    $taxonomy['status']     = $this->_findStatus('close');

                    $vacancyModel = new Vacancy($validate);
                    $vacancyModel->setTaxonomy($taxonomy);

                    foreach ($payload as $acf_field => $acfValue) {
                        $vacancyModel->setProp($acf_field, $acfValue, is_array($acfValue));
                    }
                }
            }
        } catch (AwsException $e) {
            error_log('Exception in fetch AWS S3 Bucket - ' . $e->getMessage());
        } catch (\Exception $e) {
            error_log('Exception in fetch AWS S3 Bucket - ' . $e->getMessage());
        } catch (\Throwable $throw) {
            error_log('All thrown exception in fetch AWS S3 Bucket - ' . $throw->getMessage());
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

    /**
     * Get Mapped Keyword function
     *
     * This function is to get mapped keyword from option.
     *
     * @return void
     */
    private function _getMappedKeyword()
    {
        error_log('getMappedKeyword');

        try {
            $this->_keywords = [
                'role' => [],
                'sector' => [],
            ];

            $termModel = new Term();

            /** Map Role  */
            /** Get term role */
            $terms = $termModel->selectTermByTaxonomy('role', 'array');
            if ($terms) {
                foreach ($terms as $term) {
                    $this->_keywords['role'][$term['term_id']][] = $term['name'];
                }
            }

            /** Get keywords options and set each keyword to lowercase */
            $keywordOption = get_field('import_api_mapping_role', 'option');
            foreach ($keywordOption as $keyword) {
                if (is_array($keyword)) {
                    if (array_key_exists('import_api_mapping_role_term', $keyword) && array_key_exists('import_api_mapping_role_keywords', $keyword)) {
                        foreach ($keyword['import_api_mapping_role_keywords'] as $word) {
                            if (is_array($word)) {
                                if (array_key_exists('import_api_mapping_role_eachword', $word))
                                    $this->_keywords['role'][$keyword['import_api_mapping_role_term']][] = strtolower($word['import_api_mapping_role_eachword']);
                            } else if (is_string($word)) {
                                $this->_keywords['role'][$keyword['import_api_mapping_role_term']][] = strtolower($word);
                            }
                        }
                    }
                }
            }

            /** Map Sector */
            /** Get term sector */
            $terms = $termModel->selectTermByTaxonomy('sector', 'array');
            if ($terms) {
                foreach ($terms as $term) {
                    $this->_keywords['sector'][$term['term_id']][] = $term['name'];
                }
            }

            /** Get keywords options and set each keyword to lowercase */
            $keywordOption = get_field('import_api_mapping_sector', 'option');
            foreach ($keywordOption as $keyword) {
                if (is_array($keyword)) {
                    if (array_key_exists('import_api_mapping_sector_term', $keyword) && array_key_exists('import_api_mapping_sector_keywords', $keyword)) {
                        foreach ($keyword['import_api_mapping_sector_keywords'] as $word) {
                            if (is_array($word)) {
                                if (array_key_exists('import_api_mapping_sector_eachword', $word))
                                    $this->_keywords['sector'][$keyword['import_api_mapping_sector_term']][] = strtolower($word['import_api_mapping_sector_eachword']);
                            } else if (is_string($word)) {
                                $this->_keywords['sector'][$keyword['import_api_mapping_sector_term']][] = strtolower($word);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
        }
    }

    private function _validate($fetchValue, $returnID = false)
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

        if ($returnID) {
            return $query->post_count > 0 ? $query->posts[0]->ID : false;
        } else {
            return $query->post_count > 0 ? false : true;
        }
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

    private function _findWorkingHours($fetchValue, $createNew = true)
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

        if ($createNew) {
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
        } else {
            return false;
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
}
