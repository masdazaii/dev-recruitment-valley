<?php

namespace Controller;

use DateTime;
use DateTimeImmutable;
use Helper\Maphelper;
use MI\Role\RecruiterRole;
use Model\CompanyRecruiter;
use Model\ModelHelper;
use Resource\CompanyRecruiterResource;
use Log;
use Exception;
use Helper\ExcelHelper;
use Helper\StringHelper;
use Model\ChildCompany;
use Model\PostModel;
use Vacancy\Vacancy;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Throwable;
use WP_Error;
use WP_Post;

class CompanyRecruiterController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Setup Account function
     *
     * @param Array $request
     * @return Array
     */
    public function setup(array $request): array
    {
        /** Log Attempt */
        $logData    = [
            'request'   => $request
        ];
        Log::info("Setup Company Recruiter Attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_setup_company_recruiter", false);

        $this->wpdb->query('START TRANSACTION');
        try {
            $recruiter = CompanyRecruiter::find("id", $request['user_id']);

            /** Required */
            $setup['recruiterName'] = $recruiter->setName($request['companyName']);
            $setup['country']       = $recruiter->setCountry($request['country']);
            $setup['countryCode']   = $recruiter->setCountryCode($request['countryCode']);
            $setup['city']          = $recruiter->setCity($request['city']);
            $setup['street']        = $recruiter->setStreet($request['street']);
            $setup['postCode']      = $recruiter->setPostCode($request['postCode']);

            if (!isset($request['longitude']) || !isset($request['latitude'])) {
                if (isset($request['street'])) {
                    /** IF MAP API IS ENABLED */
                    if (defined('ENABLE_MAP_API') && ENABLE_MAP_API == true) {
                        $coordinate = Maphelper::generateLongLat($request['street']);
                        $request['latitude']    = $coordinate["lat"];
                        $request['longitude']   = $coordinate["long"];
                    } else {
                        $request['latitude']    = '';
                        $request['longitude']   = '';
                    }
                } else if (isset($request['city'])) {
                    /** IF MAP API IS ENABLED */
                    if (defined('ENABLE_MAP_API') && ENABLE_MAP_API == true) {
                        $coordinate = Maphelper::generateLongLat($request['city']);
                        $request['latitude']    = $coordinate["lat"];
                        $request['longitude']   = $coordinate["long"];
                    } else {
                        $request['latitude']    = '';
                        $request['longitude']   = '';
                    }
                }
            }

            $setup['longitude']     = $recruiter->setLongitude($request['longitude']);
            $setup['latitude']      = $recruiter->setLatitude($request['latitude']);
            $setup['sector']        = $recruiter->setSector($request['sector']);
            $setup['shortDescription']  = $recruiter->setShortDescription($request['shortDescription']);
            /** Required End */

            /** Upload Gallery */
            if (isset($request['gallery']) || isset($_FILES['gallery'])) {
                $galleries = ModelHelper::handle_uploads('gallery', $request['user_id']);

                // $currentGallery = maybe_unserialize(get_user_meta($request['user_id'], $recruiter::acf_recruiter_gallery_photo));
                $currentGallery = maybe_unserialize($recruiter->getGallery('raw'));
                $currentGallery = isset($currentGallery[0]) ? $currentGallery[0] : [];
                if (isset($galleries)) {
                    $galleryIDs = $currentGallery;
                    foreach ($galleries as $key => $gallery) {
                        $galleryIDs[]   = wp_insert_attachment($gallery['attachment'], $gallery['file']);
                    }

                    // update_field($recruiter::acf_recruiter_gallery_photo, $galleryIDs, 'user_' . $request['user_id']);
                    $setup['gallery']  = $recruiter->setGallery($galleryIDs);
                }
            }

            /** Upload Image */
            if (isset($request['image']) || isset($_FILES['image'])) {
                $image = ModelHelper::handle_upload('image');
                if ($image) {
                    $imageID            = wp_insert_attachment($image['image']['attachment'], $image['image']['file']);
                    // update_field('ucma_image', $imageID, 'user_' . $request['user_id']);
                    $setup['image']    = $recruiter->setImage($imageID);
                }
            }

            /** Set Video File or URL */
            if (isset($_FILES['companyVideo']['name'])) {
                $video = ModelHelper::handle_upload('companyVideo');
                $setup['videoUrl'] = $recruiter->setVideoUrl($video);
            } else {
                $setup['videoUrl'] = $recruiter->setVideoUrl($request['companyVideo']);
            }

            if (isset($request['phoneNumberCode']) && array_key_exists('phoneNumberCode', $request)) {
                $setup['phoneNumberCode']  = $recruiter->setPhoneCode($request['phoneNumberCode']);
            }

            if (isset($request['phoneNumber']) && array_key_exists('phoneNumber', $request)) {
                $setup['phoneNumber']  = $recruiter->setPhoneNumber($request['phoneNumber']);
            }

            if (isset($request['employeesTotal']) && array_key_exists('employeesTotal', $request)) {
                $setup['employeesTotal']   = $recruiter->setTotalEmployees($request['employeesTotal']);
            }

            if (isset($request['kvkNumber']) && array_key_exists('kvkNumber', $request)) {
                $setup['kvkNumber']        = $recruiter->setKvkNumber($request['kvkNumber']);
            }

            if (isset($request['btwNumber']) && array_key_exists('btwNumber', $request)) {
                $setup['btwNumber']        = $recruiter->setBtwNumber($request['btwNumber']);
            }

            if (isset($request['website']) && array_key_exists('website', $request)) {
                $setup['website']          = $recruiter->setWebsiteUrl($request['website']);
            }

            if (isset($request['linkedin']) && array_key_exists('linkedin', $request)) {
                $setup['linkedin']         = $recruiter->setLinkedinUrl($request['linkedin']);
            }

            if (isset($request['facebook']) && array_key_exists('facebook', $request)) {
                $setup['facebook']         = $recruiter->setFacebookUrl($request['facebook']);
            }

            if (isset($request['instagram']) && array_key_exists('instagram', $request)) {
                $setup['instagram']        = $recruiter->setInstagramUrl($request['instagram']);
            }

            if (isset($request['twitter']) && array_key_exists('twitter', $request)) {
                $setup['twitter']          = $recruiter->setTwitterUrl($request['twitter']);
            }

            if (isset($request['secondaryEmploymentConditions']) && array_key_exists('secondaryEmploymentConditions', $request)) {
                $setup['benefit']          = $recruiter->setBenefit($request['secondaryEmploymentConditions']);
            }

            $setup['isFullRegistered'] = $recruiter->setIsFullRegistered(1);

            /** Log Data  */
            $logData['setup']   = $setup;

            /** Check if setup is all success */
            if (in_array(false, $setup)) {
                $failsetup = array_search(false, $setup);

                /** Log fail attempt */
                $logData['message']         = 'Failed store data : ' . $failsetup;
                $logData['fail_to_store']   = $failsetup;
                Log::error('Fail Setup Company Recruiter Attempt.', json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . '_log_setup_company_recruiter');

                $this->wpdb->query('ROLLBACK');

                return [
                    "status"    => 500,
                    "message"   => $this->message->get("registration.overall_failed", ['Failed store user data.']),
                ];
            }

            /** Log Attempt */
            Log::info("End Setup Company Recruiter Attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_setup_company_recruiter", false);

            $this->wpdb->query('COMMIT');

            return [
                'status'    => 200,
                'message'   => $this->message->get("profile.setup.success"),
            ];
        } catch (\WP_Error $wp_error) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($wp_error, __CLASS__, __METHOD__, $logData, 'log_setup_company_recruiter');
        } catch (Exception $e) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($e, __CLASS__, __METHOD__, $logData, 'log_setup_company_recruiter');
        } catch (Throwable $th) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($th, __CLASS__, __METHOD__, $logData, 'log_setup_company_recruiter');
        }
    }

    /**
     * GET user profile by JWT function
     *
     * @param array $request
     * @return array
     */
    public function myProfile(array $request): array
    {
        /** Log Attempt */
        $logData = [
            'request'   => $request
        ];
        Log::info("My profile attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . '_log_get_my_profile', false);

        try {
            $recruiter = CompanyRecruiter::find('id', $request['user_id']);

            if (!$recruiter->user || $recruiter->user instanceof WP_Error) {
                if ($recruiter->user instanceof WP_Error) {
                    throw $recruiter->user;
                }

                /** Log attempt */
                $logData['message'] = "User not found! POSSIBLE SYSTEM ERROR!";
                $logData['user_id'] = $request['user_id'];
                Log::error("Fail My profile attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . '_log_get_my_profile', false);

                return [
                    "status"    => 500,
                    "message"   => $this->message->get("system.overall_failed", [" {$this->message->get("auth.not_found_user")}"])
                ];
            }

            return [
                "status"    => 200,
                "message"   => $this->message->get("profile.get.success"),
                "data"      => CompanyRecruiterResource::single($recruiter->user)
            ];
        } catch (\WP_Error $wp_error) {
            return $this->handleError($wp_error, __CLASS__, __METHOD__, $logData, 'log_get_my_profile');
        } catch (Exception $e) {
            return $this->handleError($e, __CLASS__, __METHOD__, $logData, 'log_get_my_profile');
        } catch (Throwable $th) {
            return $this->handleError($th, __CLASS__, __METHOD__, $logData, 'log_get_my_profile');
        }
    }

    /**
     * Store (insert or update) detail part acf function
     *
     * @param array $request
     * @return array
     */
    public function storeDetail(array $request): array
    {
        /** Log Attempt */
        $logData    = [
            'request'   => $request
        ];
        Log::info("Store Detail Company Recruiter Attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_store_detail_company_recruiter", false);

        $this->wpdb->query('START TRANSACTION');
        try {
            $recruiter          = CompanyRecruiter::find("id", $request['user_id']);

            $store['sector']    = $recruiter->setSector($request['sector']);

            if (isset($request['phoneNumberCode']) && array_key_exists('phoneNumberCode', $request)) {
                $store['phoneNumberCode']   = $recruiter->setPhoneCode($request['phoneNumberCode']);
            }

            if (isset($request['phoneNumber']) && array_key_exists('phoneNumber', $request)) {
                $store['phoneNumber']       = $recruiter->setPhoneNumber($request['phoneNumber']);
            }

            if (isset($request['employeesTotal']) && array_key_exists('employeesTotal', $request)) {
                $store['employeesTotal']    = $recruiter->setTotalEmployees($request['employeesTotal']);
            }

            if (isset($request['kvkNumber']) && array_key_exists('kvkNumber', $request)) {
                $store['kvkNumber']         = $recruiter->setKvkNumber($request['kvkNumber']);
            }

            if (isset($request['btwNumber']) && array_key_exists('btwNumber', $request)) {
                $store['btwNumber']         = $recruiter->setBtwNumber($request['btwNumber']);
            }

            if (isset($request['website']) && array_key_exists('website', $request)) {
                $store['website']           = $recruiter->setWebsiteUrl($request['website']);
            }

            /** Log Attempt */
            $logData['store'] = $store;
            Log::info("Store Detail Company Recruiter Attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_store_detail_company_recruiter", false);

            $this->wpdb->query("COMMIT");

            return [
                "status"    => 200,
                "message"   => $this->message->get("profile.update.success")
            ];
        } catch (\WP_Error $wp_error) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($wp_error, __CLASS__, __METHOD__, $logData, 'log_store_detail_company_recruiter');
        } catch (Exception $e) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($e, __CLASS__, __METHOD__, $logData, 'log_store_detail_company_recruiter');
        } catch (Throwable $th) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($th, __CLASS__, __METHOD__, $logData, 'log_store_detail_company_recruiter');
        }
    }

    /**
     * Store (insert or update) address part acf function
     *
     * @param array $request
     * @return array
     */
    public function storeAddress(array $request): array
    {
        /** Log Attempt */
        $logData    = [
            'request'   => $request
        ];
        Log::info("Store Address Company Recruiter Attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_store_address_company_recruiter", false);

        $this->wpdb->query('START TRANSACTION');
        try {
            $recruiter = CompanyRecruiter::find("id", $request['user_id']);

            $store['country']       = $recruiter->setCountry($request['country']);
            $store['countryCode']   = $recruiter->setCountryCode($request['countryCode']);
            $store['city']          = $recruiter->setCity($request['city']);
            $store['street']        = $recruiter->setStreet($request['street']);
            $store['postCode']      = $recruiter->setPostCode($request['postCode']);

            if (!isset($request['longitude']) || !isset($request['latitude'])) {
                if (isset($request['street'])) {
                    /** IF MAP API IS ENABLED */
                    if (defined('ENABLE_MAP_API') && ENABLE_MAP_API == true) {
                        $coordinate = Maphelper::generateLongLat($request['street']);
                        $request['latitude']    = $coordinate["lat"];
                        $request['longitude']   = $coordinate["long"];
                    } else {
                        $request['latitude']    = '';
                        $request['longitude']   = '';
                    }
                } else if (isset($request['city'])) {
                    /** IF MAP API IS ENABLED */
                    if (defined('ENABLE_MAP_API') && ENABLE_MAP_API == true) {
                        $coordinate = Maphelper::generateLongLat($request['city']);
                        $request['latitude']    = $coordinate["lat"];
                        $request['longitude']   = $coordinate["long"];
                    } else {
                        $request['latitude']    = '';
                        $request['longitude']   = '';
                    }
                }
            }

            $store['longitude']     = $recruiter->setLongitude($request['longitude']);
            $store['latitude']      = $recruiter->setLatitude($request['latitude']);

            /** Log Attempt */
            $logData['store']       = $store;
            Log::info("End Store Address Company Recruiter Attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_store_address_company_recruiter", false);

            $this->wpdb->query("COMMIT");

            return [
                "status"    => 200,
                "message"   => $this->message->get("profile.update.success")
            ];
        } catch (\WP_Error $wp_error) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($wp_error, __CLASS__, __METHOD__, $logData, 'log_store_address_company_recruiter');
        } catch (Exception $e) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($e, __CLASS__, __METHOD__, $logData, 'log_store_address_company_recruiter');
        } catch (Throwable $th) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($th, __CLASS__, __METHOD__, $logData, 'log_store_address_company_recruiter');
        }
    }

    /**
     * Store (insert or update) social media part acf function.
     *
     * @param array $request
     * @return array
     */
    public function storeSocials(array $request): array
    {
        /** Log Attempt */
        $logData    = [
            'request'   => $request
        ];
        Log::info("Store Socials Company Recruiter Attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_store_social_company_recruiter", false);

        $this->wpdb->query('START TRANSACTION');
        try {
            $recruiter = CompanyRecruiter::find("id", $request['user_id']);

            if (isset($request['linkedin']) && array_key_exists('linkedin', $request)) {
                $store['linkedin']         = $recruiter->setLinkedinUrl($request['linkedin']);
            }

            if (isset($request['facebook']) && array_key_exists('facebook', $request)) {
                $store['facebook']         = $recruiter->setFacebookUrl($request['facebook']);
            }

            if (isset($request['instagram']) && array_key_exists('instagram', $request)) {
                $store['instagram']        = $recruiter->setInstagramUrl($request['instagram']);
            }

            if (isset($request['twitter']) && array_key_exists('twitter', $request)) {
                $store['twitter']          = $recruiter->setTwitterUrl($request['twitter']);
            }

            /** Log Attempt */
            $logData['store']       = $store;
            Log::info("End Store Socials Company Recruiter Attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_store_social_company_recruiter", false);

            $this->wpdb->query("COMMIT");

            return [
                "status"    => 200,
                "message"   => $this->message->get("profile.update.success")
            ];
        } catch (\WP_Error $wp_error) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($wp_error, __CLASS__, __METHOD__, $logData, 'log_store_social_company_recruiter');
        } catch (Exception $e) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($e, __CLASS__, __METHOD__, $logData, 'log_store_social_company_recruiter');
        } catch (Throwable $th) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($th, __CLASS__, __METHOD__, $logData, 'log_store_social_company_recruiter');
        }
    }

    /**
     * Store (insert or update) information part acf function
     *
     * @param array $request
     * @return array
     */
    public function storeInformation(array $request): array
    {
        /** Log Attempt */
        $logData    = [
            'request'   => $request
        ];
        Log::info("Store Information Company Recruiter Attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_store_information_company_recruiter", false);

        $this->wpdb->query('START TRANSACTION');
        try {
            $recruiter = CompanyRecruiter::find("id", $request['user_id']);

            $store['shortDescription']  = $recruiter->setShortDescription($request['shortDescription']);

            /** Upload Gallery */
            if (isset($request['gallery']) || isset($_FILES['gallery'])) {
                $galleries = ModelHelper::handle_uploads('gallery', $request['user_id']);

                // $currentGallery = maybe_unserialize(get_user_meta($request['user_id'], $recruiter::acf_recruiter_gallery_photo));
                $currentGallery = maybe_unserialize($recruiter->getGallery('raw'));
                $currentGallery = isset($currentGallery[0]) ? $currentGallery[0] : [];
                if (isset($galleries)) {
                    $galleryIDs = $currentGallery;
                    foreach ($galleries as $key => $gallery) {
                        $galleryIDs[]   = wp_insert_attachment($gallery['attachment'], $gallery['file']);
                    }

                    // update_field($recruiter::acf_recruiter_gallery_photo, $galleryIDs, 'user_' . $request['user_id']);
                    $store['gallery']  = $recruiter->setGallery($galleryIDs);
                }
            }

            /** Set Video File or URL */
            if (isset($_FILES['companyVideo']['name'])) {
                $video = ModelHelper::handle_upload('companyVideo');
                $store['videoUrl']  = $recruiter->setVideoUrl($video);
            } else {
                $store['videoUrl']  = $recruiter->setVideoUrl($request['companyVideo']);
            }

            if (isset($request['secondaryEmploymentConditions']) && array_key_exists('secondaryEmploymentConditions', $request)) {
                $store['benefit']   = $recruiter->setBenefit($request['secondaryEmploymentConditions']);
            }

            /** Log Attempt */
            $logData['store']       = $store;
            Log::info("End Store Information Company Recruiter Attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_store_information_company_recruiter", false);

            $this->wpdb->query("COMMIT");

            return [
                "status"    => 200,
                "message"   => $this->message->get("profile.update.success")
            ];
        } catch (\WP_Error $wp_error) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($wp_error, __CLASS__, __METHOD__, $logData, 'log_store_information_company_recruiter');
        } catch (Exception $e) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($e, __CLASS__, __METHOD__, $logData, 'log_store_information_company_recruiter');
        } catch (Throwable $th) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($th, __CLASS__, __METHOD__, $logData, 'log_store_information_company_recruiter');
        }
    }

    /**
     * Get image function
     *
     * @param array $request
     * @return array
     */
    public function getPhoto(array $request): array
    {
        /** Log Attempt */
        $logData = [
            'request'   => $request
        ];
        Log::info("My profile attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . '_log_get_profile_image', false);

        try {
            $recruiter = CompanyRecruiter::find('id', $request['user_id']);

            if (!$recruiter->user || $recruiter->user instanceof WP_Error) {
                if ($recruiter->user instanceof WP_Error) {
                    throw $recruiter->user;
                }

                /** Log attempt */
                $logData['message'] = "User not found! POSSIBLE SYSTEM ERROR!";
                $logData['user_id'] = $request['user_id'];
                Log::error("Fail My profile attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . '_log_get_my_profile', false);

                return [
                    "status"    => 500,
                    "message"   => $this->message->get("system.overall_failed", [" {$this->message->get("auth.not_found_user")}"])
                ];
            }

            return [
                'status'    => 200,
                'data'      => $recruiter->getThumbnail('object'),
                'message'   => $this->message->get('company.profile.get_image_success'),
            ];
        } catch (\WP_Error $wp_error) {
            return $this->handleError($wp_error, __CLASS__, __METHOD__, $logData, 'log_get_profile_image');
        } catch (Exception $e) {
            return $this->handleError($e, __CLASS__, __METHOD__, $logData, 'log_get_profile_image');
        } catch (Throwable $th) {
            return $this->handleError($th, __CLASS__, __METHOD__, $logData, 'log_get_profile_image');
        }
    }

    /**
     * Create Report to superadmin function
     *
     * @param array $request
     * @return void
     */
    public function report(array $request)
    {
        /** Log Attempt */
        $logData    = [
            'request'   => $request
        ];
        Log::info("Report Company Recruiter's vacancies Attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_report_company_recruiter_vacancy", false);

        try {
            /** Set filter */
            if (array_key_exists('filter', $request)) {
                if (array_key_exists('companyRecruiter', $request['filter'])) {
                    if ($request['filter']['companyRecruiter'] == 'all') {
                        /** Get all company recruiter user ID's */
                        $companyRecruiterModel = CompanyRecruiter::select([
                            'perPage'   => -1,
                            'role'      => 'company-recruiter',
                            'fields'    => 'ID'
                        ]);

                        $companyRecruiters = $companyRecruiterModel->get_results();
                    } else if (is_array($request['filter']['companyRecruiter'])) {
                        /** Filter value is company recruiter user id and is numeric */
                        $companyRecruiters = array_filter($request['filter']['companyRecruiter'], function ($companyRecruiter) {
                            return is_numeric($companyRecruiter);
                        }, 0);
                    } else if (is_numeric($request['filter']['companyRecruiter'])) {
                        $companyRecruiters = [$request['filter']['companyRecruiter']];
                    }
                }
            } else {
                /** Get all company recruiter user ID's */
                $companyRecruiterModel = CompanyRecruiter::select([
                    'perPage'   => -1,
                    'role'      => 'company-recruiter',
                    'fields'    => 'ID'
                ]);

                $companyRecruiters = $companyRecruiterModel->get_results();
            }

            /** Log Data */
            $logData['selectedCompanyRecruiters'] = $companyRecruiters;

            $vacancyData    = [];
            foreach ($companyRecruiters as $companyRecruiterID) {
                $companyRecruiterModel  = CompanyRecruiter::find('ID', $companyRecruiterID);

                /** Prepare filter */
                $filters        = [
                    'perPage'   => -1,
                    'author'    => $companyRecruiterID,
                    'date'      => [
                        'after'     => $request['filter']['submittedAfter'],
                        'before'    => $request['filter']['submittedBefore'],
                        'inclusive' => true,
                    ]
                ];

                /** Get vacancy each child company */
                $vacancyModel   = new Vacancy();
                $vacancies      = $vacancyModel->select($filters);

                /** Log Data */
                $logData['foundVacancies'][$companyRecruiterID]['total'] = $vacancies->found_posts;

                /** Map vacancy to each child company */
                $vacancyData[$companyRecruiterID] = [
                    'companyRecruiterName'  => $companyRecruiterModel->getName(),
                    'data' => []
                ];

                if ($vacancies->found_posts > 0) {
                    foreach ($vacancies->posts as $vacancy) {
                        /** Log Data */
                        $logData['foundVacancies'][$companyRecruiterID]['vacancyIDs'] = $vacancy->ID;

                        $eachVacancyModel       = new Vacancy($vacancy->ID);

                        $assignedChildCompany   = $eachVacancyModel->getAssignedChildCompany();
                        if ($assignedChildCompany && $assignedChildCompany instanceof WP_Post) {
                            $childCompanyModel      = new ChildCompany($assignedChildCompany);
                            $assignedChildCompanyID = $assignedChildCompany->ID;

                            $vacancyStatus          = $eachVacancyModel->getStatus();

                            $vacancyCreatedDate     = $eachVacancyModel->vacancy->post_date ?? NULL;
                            if (isset($vacancyCreatedDate)) {
                                $vacancyCreatedDate = new DateTime($vacancyCreatedDate);
                                $vacancyCreatedDate = $vacancyCreatedDate->format('Y-m-d H:i:s');
                            }

                            $vacancyTaxonomy        = $eachVacancyModel->getTax(true);
                            $vacancyTerms           = [];
                            foreach ($vacancyTaxonomy as $taxonomy => $terms) {
                                if (is_array($terms)) {
                                    if (array_is_list($terms)) {
                                        foreach ($terms as $term) {
                                            $vacancyTerms[$taxonomy][] = $term['label'];
                                        }
                                    } else {
                                        $vacancyTerms[$taxonomy][] = $terms['label'];
                                    }
                                } else {
                                    $vacancyTerms[$taxonomy][] = $terms;
                                }
                            }

                            /** Get edit URL */
                            $editUrl = get_edit_post_link($eachVacancyModel->vacancy->ID); // This will return null if user doesn't have capability.
                            if (!isset($editUrl) || empty($editUrl)) {
                                $editUrl = admin_url('post.php?post=' . $eachVacancyModel->vacancy->ID . '&action=edit');
                            }

                            $vacancyData[$companyRecruiterID]['data'][$assignedChildCompanyID][$eachVacancyModel->vacancy->ID] = [
                                'id'            => $eachVacancyModel->vacancy->ID,
                                'title'         => $eachVacancyModel->vacancy->post_title,
                                'status'        => $vacancyStatus && isset($vacancyStatus['name']) ? $vacancyStatus['name'] : '-',
                                // 'taxonomy'      => $eachVacancyModel->getTaxonomy(true),

                                'workingHours'  => $vacancyTerms['working-hours'] ?? null,
                                'education'     => $vacancyTerms['education']  ?? null,
                                'employmentType'    => $vacancyTerms['type'] ?? null,
                                'experince'         => $vacancyTerms['experiences'] ?? null,
                                'sector'            => $vacancyTerms['sector'] ?? null,
                                'role'              => $vacancyTerms['role'] ?? null,
                                'location'          => $vacancyTerms['location'] ?? null,

                                'createDate'        => $vacancyCreatedDate,
                                'expiredDate'       => $eachVacancyModel->getExpiredAt(),
                                'editUrl'           => $editUrl,
                                'childCompanyName'  => $childCompanyModel->getName(),
                            ];
                        }
                    }
                }

                /** Log Data */
                $logData['excelData'] = $vacancyData;
            }

            /** Set excel report */
            $reportData = $this->createExcelReport($vacancyData);

            /** Store to database */
            // $reportFile = fopen($reportData['path'], 'r');
            // $reportAttachment   = [
            //     'url'           => $reportData['url'],
            //     'attachment'    => [
            //         'guid'           => $reportData['url'],
            //         'post_mime_type' => $reportData['type'],
            //         'post_title'     => preg_replace('/\.[^.]+$/', '', basename($reportData['name'])),
            //         'post_content'   => '',
            //         'post_status'    => 'inherit'
            //     ],
            //     'name'       => $reportData['name'],
            //     'file'       => $reportFile
            // ];
            // $reportID = wp_insert_attachment($$reportAttachment['attachment'], $$reportAttachment['file']);

            /** Create Post for report */
            $cptSlug    = 'recruiter-report';

            $data       = [
                'post_title'    => preg_replace('/\.[^.]+$/', '', $reportData['name']),
                'post_name'     => StringHelper::makeSlug(preg_replace('/\.[^.]+$/', '', $reportData['name'])),
                'post_type'     => $cptSlug,
            ];

            $postModel  = new PostModel();
            $reportPost = $postModel->create($data);

            /** Log data */
            $logData['reportData']['postCreate']    = $reportPost;
            $logData['reportData']['postData']      = $data;

            if (!$reportPost || $reportPost instanceof WP_Error) {
                if (!$reportPost) {
                    /** Log Data */
                    $logData['reportPost']  = $reportPost;
                    $logData['message']     = 'Failed to create report post!';

                    throw new Exception('Failed to create report post!');
                } else if ($reportPost instanceof WP_Error) {
                    /** Log Data */
                    $logData['reportPost']  = $reportPost->get_error_message();
                    $logData['message']     = 'Failed to create report post!';

                    throw $reportPost;
                }
            }

            /** Set periode */
            $periodeStart   = new DateTimeImmutable($request['filter']['submittedAfter']);
            $periodeEnd     = new DateTimeImmutable($request['filter']['submittedBefore']);

            /** Set download url as meta */
            $downloadURL    = update_post_meta($reportPost, 'rv_company_recruiter_report_url', $reportData['url']);
            $periodeStart   = update_post_meta($reportPost, 'rv_company_recruiter_report_periode_start', "{$periodeStart->format('Y-m-d')}");
            $periodeEnd     = update_post_meta($reportPost, 'rv_company_recruiter_report_periode_end', "{$periodeEnd->format('Y-m-d')}");

            // $reportData = 'aa';
            $logData['report'] = [
                'data'  => $reportData,
                'post'  => is_numeric($reportPost) ? $reportPost : $reportPost->ID,
                'meta'  => [
                    'periodeStart'  => $periodeStart,
                    'periodeEnd'    => $periodeEnd,
                    'download'      => $downloadURL
                ]
            ];
            Log::info("Report Company Recruiter's vacancies Attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_report_company_recruiter_vacancy", false);
        } catch (\WP_Error $wp_error) {
            return $this->handleError($wp_error, __CLASS__, __METHOD__, $logData, 'log_report_company_recruiter_vacancy');
        } catch (Exception $e) {
            return $this->handleError($e, __CLASS__, __METHOD__, $logData, 'log_report_company_recruiter_vacancy');
        } catch (Throwable $th) {
            return $this->handleError($th, __CLASS__, __METHOD__, $logData, 'log_report_company_recruiter_vacancy');
        }

        return [
            "status"    => 200,
            "message"   => "success create report"
        ];
    }

    /**
     * Create Excel File function
     *
     * @param array $datas
     * @return void
     */
    protected function createExcelReport(array $datas)
    {
        $spreadsheet = new Spreadsheet();

        /** Create Sheet for each companyRecruiter */
        $companyRecruiterIndex = 0;
        foreach ($datas as $companyRecruiterID => $data) {
            if ($companyRecruiterIndex <= 0) {
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle($data['companyRecruiterName']);
            } else {
                $sheet = $spreadsheet->createSheet($companyRecruiterIndex);
                $sheet->setTitle($data['companyRecruiterName']);

                // $spreadsheet->setActiveSheetIndexByName($data['companyRecruiterName']);

                // $sheet = $spreadsheet->getActiveSheet();
            }

            // $companyRecruiterModel  = CompanyRecruiter::find('ID', $companyRecruiterID);

            /** Cell start */
            $coloumn        = 'A';
            $headerRow      = 1;
            $subHeaderRow   = 2;
            $dataRow        = 3;
            $tableHeaders   = [
                "no"            => "No",
                "childCompany"  => "Child Company Name",
                "vacancy"       => [
                    "name"      => 'Vacancy',
                    "subs"      => [
                        "Vacancy Title",
                        "Status",
                        "Created Date",
                        "Expired Date",
                        "Working Hours",
                        "Education",
                        "Employment Type",
                        "Working Experience",
                        "Sector",
                        "Role",
                        "Location",
                        "Edit Url"
                    ]
                ]
            ];
            $tableData      = ["no", "childCompanyName", "title", "status", "createDate", "expiredDate", "workingHours", "education", "employmentType", "experince", "sector", "role", "location", "editUrl"];

            $hasSubHeader   = array_walk_recursive($tableHeaders, function ($value, $key) {
                return is_array($value);
            });

            /** Set Table Header */
            $headerCol = $coloumn;
            foreach ($tableHeaders as $key => $header) {
                $sheet->getColumnDimension($headerCol)->setAutoSize(true);

                if (is_array($header)) {
                    $sheet->getColumnDimension($headerCol)->setAutoSize(true);

                    $totalSub           = count($header['subs']) - 1;
                    $endColoumnMerge    = ExcelHelper::getNextColumnLetter($headerCol, $totalSub);

                    $sheet->mergeCells("{$headerCol}{$headerRow}:{$endColoumnMerge}{$headerRow}");
                    $sheet->getStyle("{$headerCol}{$headerRow}:{$endColoumnMerge}{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER_CONTINUOUS);
                    $sheet->getStyle("{$headerCol}{$headerRow}:{$endColoumnMerge}{$headerRow}")->getFont()->setBold(true);
                    $sheet->getStyle("{$headerCol}{$headerRow}:{$endColoumnMerge}{$headerRow}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ddcf00');
                    $sheet->getStyle("{$headerCol}{$headerRow}:{$endColoumnMerge}{$headerRow}")->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $sheet->getStyle("{$headerCol}{$headerRow}:{$endColoumnMerge}{$headerRow}")->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $sheet->getStyle("{$headerCol}{$headerRow}:{$endColoumnMerge}{$headerRow}")->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $sheet->getStyle("{$headerCol}{$headerRow}:{$endColoumnMerge}{$headerRow}")->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $sheet->setCellValue("{$headerCol}{$headerRow}", $header['name']);

                    /** Set sub header */
                    $subHeaderCol = $headerCol;
                    foreach ($header['subs'] as $subHeader) {
                        $sheet->getStyle("{$subHeaderCol}{$subHeaderRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER_CONTINUOUS)->setVertical(Alignment::VERTICAL_CENTER);
                        $sheet->getStyle("{$subHeaderCol}{$subHeaderRow}")->getFont()->setBold(true);
                        $sheet->getStyle("{$subHeaderCol}{$subHeaderRow}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ddcf00');
                        $sheet->getStyle("{$subHeaderCol}{$subHeaderRow}")->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $sheet->getStyle("{$subHeaderCol}{$subHeaderRow}")->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $sheet->getStyle("{$subHeaderCol}{$subHeaderRow}")->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $sheet->getStyle("{$subHeaderCol}{$subHeaderRow}")->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $sheet->setCellValue("{$subHeaderCol}{$subHeaderRow}", $subHeader);
                        $subHeaderCol = ExcelHelper::getNextColumnLetter($subHeaderCol, 1);
                    }
                } else {
                    if ($hasSubHeader) {
                        $sheet->getStyle("{$headerCol}{$headerRow}:{$headerCol}{$subHeaderRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER_CONTINUOUS)->setVertical(Alignment::VERTICAL_CENTER);
                        $sheet->getStyle("{$headerCol}{$headerRow}:{$headerCol}{$subHeaderRow}")->getFont()->setBold(true);
                        $sheet->getStyle("{$headerCol}{$headerRow}:{$headerCol}{$subHeaderRow}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ddcf00');
                        $sheet->getStyle("{$headerCol}{$headerRow}:{$headerCol}{$subHeaderRow}")->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $sheet->getStyle("{$headerCol}{$headerRow}:{$headerCol}{$subHeaderRow}")->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $sheet->getStyle("{$headerCol}{$headerRow}:{$headerCol}{$subHeaderRow}")->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $sheet->getStyle("{$headerCol}{$headerRow}:{$headerCol}{$subHeaderRow}")->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $sheet->mergeCells("{$headerCol}{$headerRow}:{$headerCol}{$subHeaderRow}");
                        $sheet->setCellValue("{$headerCol}{$headerRow}", $header);
                    } else {
                        $sheet->getStyle("{$headerCol}{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER_CONTINUOUS)->setVertical(Alignment::VERTICAL_CENTER);
                        $sheet->getStyle("{$headerCol}{$headerRow}")->getFont()->setBold(true);
                        $sheet->getStyle("{$headerCol}{$headerRow}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ddcf00');
                        $sheet->getStyle("{$headerCol}{$headerRow}")->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $sheet->getStyle("{$headerCol}{$headerRow}")->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $sheet->getStyle("{$headerCol}{$headerRow}")->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $sheet->getStyle("{$headerCol}{$headerRow}")->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $sheet->setCellValue("{$headerCol}{$headerRow}", $header);
                    }
                }

                // $headerCol = ++$headerCol;
                $headerCol = ExcelHelper::getNextColumnLetter($headerCol, 1);
            }

            /** Loop each child company */
            $index = 0;
            $no = 1;
            foreach ($data['data'] as $childCompanyID => $vacancies) {
                // Log::info("data-data {$childCompanyID}.", $vacancies, date('Y_m_d') . "_log_excel_create", false);

                /** Set Table Header */
                // $headerCol = $coloumn;

                // foreach ($tableHeaders as $key => $header) {
                //     $sheet->getColumnDimension($headerCol)->setAutoSize(true);

                //     if (is_array($header)) {
                //         $sheet->getColumnDimension($headerCol)->setAutoSize(true);

                //         $totalSub           = count($header['subs']);
                //         $endColoumnMerge    = ExcelHelper::getNextColumnLetter($headerCol, $totalSub);

                //         $sheet->mergeCells("{$headerCol}{$headerRow}:{$endColoumnMerge}{$headerRow}");
                //         $sheet->getStyle("{$headerCol}{$headerRow}:{$endColoumnMerge}{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER_CONTINUOUS);
                //         $sheet->getStyle("{$headerCol}{$headerRow}:{$endColoumnMerge}{$headerRow}")->getFont()->setBold(true);
                //         $sheet->setCellValue("{$headerCol}{$headerRow}", $header['name']);

                //         /** Set sub header */
                //         $subHeaderCol = $headerCol;
                //         foreach ($header['subs'] as $subHeader) {
                //             $sheet->getStyle("{$subHeaderCol}{$subHeaderRow}")->getFont()->setBold(true);
                //             $sheet->setCellValue("{$subHeaderCol}{$subHeaderRow}", $subHeader);
                //             $subHeaderCol = ExcelHelper::getNextColumnLetter($subHeaderCol, 1);
                //         }
                //     } else {
                //         if ($hasSubHeader) {
                //             $sheet->getStyle("{$headerCol}{$headerRow}:{$headerCol}{$subHeaderRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER_CONTINUOUS)->setVertical(Alignment::VERTICAL_CENTER);
                //             $sheet->getStyle("{$headerCol}{$headerRow}:{$headerCol}{$subHeaderRow}")->getFont()->setBold(true);
                //             $sheet->mergeCells("{$headerCol}{$headerRow}:{$headerCol}{$subHeaderRow}");
                //             $sheet->setCellValue("{$headerCol}{$headerRow}", $header);
                //         } else {
                //             $sheet->getStyle("{$headerCol}{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER_CONTINUOUS)->setVertical(Alignment::VERTICAL_CENTER);
                //             $sheet->getStyle("{$headerCol}{$headerRow}")->getFont()->setBold(true);
                //             $sheet->setCellValue("{$headerCol}{$headerRow}", $header);
                //         }
                //     }

                //     // $headerCol = ++$headerCol;
                //     $headerCol = ExcelHelper::getNextColumnLetter($headerCol, 1);
                // }

                /** Set the data */
                $dataCol   = $coloumn;

                foreach ($vacancies as $vacancyID => $vacancy) {
                    $dataCol   = $coloumn;

                    foreach ($tableData as $key) {
                        $sheet->getColumnDimension($dataCol)->setAutoSize(true);

                        if ($key == "no") {
                            // $urut = $no;
                            $sheet->setCellValue("{$dataCol}{$dataRow}", "$no");
                        } else {
                            if (is_array($vacancy)) {
                                if (isset($vacancy[$key])) {
                                    if (is_array($vacancy[$key])) {
                                        $sheet->setCellValue("{$dataCol}{$dataRow}", implode(', ', $vacancy[$key]));
                                    } else {
                                        $sheet->setCellValue("{$dataCol}{$dataRow}", $vacancy[$key]);
                                    }
                                }
                            }
                        }

                        $dataCol = ExcelHelper::getNextColumnLetter($dataCol, 1);
                    }

                    // $sheet[$companyRecruiterID]->setCellValue("A{$dataRow}", $no);
                    // $sheet[$companyRecruiterID]->setCellValue("B{$dataRow}", $vacancy['childCompanyName']);
                    // $sheet[$companyRecruiterID]->setCellValue("C{$dataRow}", $vacancy['title']);
                    // $sheet[$companyRecruiterID]->setCellValue("D{$dataRow}", $vacancy['status']);
                    // $sheet[$companyRecruiterID]->setCellValue("E{$dataRow}", $vacancy['status']);

                    // $no = (int)$no + 1;
                    $no++;
                    $dataRow++;
                }

                /** Set next start row */
                $totalRow       = count($vacancies);
                // $headerRow      = (int)$headerRow + (int)$totalRow + 4;
                // $subHeaderRow   = (int)$subHeaderRow + (int)$totalRow + 4;
                // $dataRow        = (int)$dataRow + (int)$totalRow;

                // Log::info("coloumnIndex.", json_encode($columnIndex, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_excel_create", false);
            }

            $companyRecruiterIndex++;
        }

        $writer = new WriterXlsx($spreadsheet);

        $now = new DateTimeImmutable();
        // $filepath = THEME_DIR . '/export/serial-request/' . date('Y-m-d');
        $filepath = wp_upload_dir()["basedir"] . '/report/company_recruiter/' . $now->format('Y_m_d');
        $fileuri  = wp_upload_dir()["baseurl"] . '/report/company_recruiter/' . $now->format('Y_m_d');

        if (!file_exists($filepath)) {
            mkdir($filepath, 0777, true);
        }

        $filename = 'Report-Company-Recruiter-' . date('Y-m-d-H-i-s') . ".xlsx";
        $writer->save("{$filepath}/{$filename}");

        // return "{$fileuri}/{$filename}";
        $filemime = mime_content_type("{$filepath}/{$filename}");
        return [
            'name'  => $filename,
            'url'   => "{$fileuri}/{$filename}",
            'path'  => "{$filepath}/{$filename}",
            'type'  => $filemime
        ];
    }
}
