<?php

namespace Controller;

use Helper\Maphelper;
use MI\Role\RecruiterRole;
use Model\CompanyRecruiter;
use Model\ModelHelper;
use Resource\CompanyRecruiterResource;
use Log;
use Exception;
use Throwable;
use WP_Error;

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
}
