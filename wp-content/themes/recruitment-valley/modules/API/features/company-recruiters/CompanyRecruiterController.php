<?php

namespace Controller;

use Exception;
use Helper\Maphelper;
use Log;
use Model\CompanyRecruiter;
use Model\ModelHelper;
use Throwable;

class CompanyRecruiterController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function setup(array $request)
    {
        /** Log Attempt */
        $logData    = [
            'request'   => $request
        ];
        Log::info("Setup Company Recruiter.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_setup_company_recruiter", false);

        $this->wpdb->query('START TRANSACTION');
        try {
            $recruiter = CompanyRecruiter::find("id", $request['user_id']);

            /** Upload Gallery */
            $galleries = ModelHelper::handle_uploads('gallery', $request['user_id']);

            // $currentGallery = maybe_unserialize(get_user_meta($request['user_id'], $recruiter::acf_recruiter_gallery_photo));
            $currentGallery = maybe_unserialize($recruiter->getGallery('raw'));
            $currentGallery = isset($currentGallery[0]) ? $currentGallery[0] : [];
            if (isset($galleries)) {
                $galleryIDs = $currentGallery;
                foreach ($galleries as $key => $gallery) {
                    $galleryIDs[] = wp_insert_attachment($gallery['attachment'], $gallery['file']);
                }
                // update_field($recruiter::acf_recruiter_gallery_photo, $galleryIDs, 'user_' . $request['user_id']);
                $recruiter->setGallery($galleryIDs);
            }

            /** Upload Image */
            $image = ModelHelper::handle_upload('image');
            if ($image) {
                $imageID = wp_insert_attachment($image['image']['attachment'], $image['image']['file']);
                // update_field('ucma_image', $imageID, 'user_' . $request['user_id']);
                $recruiter->setImage($imageID);
            }

            /** Set Video File or URL */
            if (isset($_FILES['recruiterVideo']['name'])) {
                $video = ModelHelper::handle_upload('recruiterVideo');
                $recruiter->setVideoUrl($video);
            } else {
                $recruiter->setVideoUrl($request['recruiterVideo']);
            }

            $recruiter->setName($request['recruiterName']);
            $recruiter->setPhoneCode($request['phoneNumberCode']);
            $recruiter->setPhoneNumber($request['phoneNumber']);
            $recruiter->setCountry($request['country']);
            $recruiter->setCountryCode($request['countryCode']);
            $recruiter->setCity($request['city']);
            $recruiter->setStreet($request['street']);
            $recruiter->setPostCode($request['postCode']);

            if (!isset($request['longitude']) || !isset($request['latitude'])) {
                if (isset($request['street'])) {
                    /** IF MAP API IS ENABLED */
                    if (defined('ENABLE_MAP_API') && ENABLE_MAP_API == true) {
                        $coordinate = Maphelper::generateLongLat($request['street']);
                        $request['latitude']    = $coordinate["lat"];
                        $request['longitude']   = $coordinate["long"];
                    }
                } else if (isset($request['city'])) {
                    /** IF MAP API IS ENABLED */
                    if (defined('ENABLE_MAP_API') && ENABLE_MAP_API == true) {
                        $coordinate = Maphelper::generateLongLat($request['city']);
                        $request['latitude']    = $coordinate["lat"];
                        $request['longitude']   = $coordinate["long"];
                    }
                }
            }
            $recruiter->setLongitude($request['longitude']);
            $recruiter->setLatitude($request['latitude']);

            $recruiter->setTotalEmployees($request['employeesTotal']);
            $recruiter->setSector($request['sector']);
            $recruiter->setKvkNumber($request['kvkNumber']);
            $recruiter->setBtwNumber($request['btwNumber']);
            $recruiter->setWebsiteUrl($request['website']);
            $recruiter->setLinkedinUrl($request['linkedin']);
            $recruiter->setFacebookUrl($request['facebook']);
            $recruiter->setInstagramUrl($request['instagram']);
            $recruiter->setTwitterUrl($request['twitter']);
            $recruiter->setShortDescription($request['shortDescription']);
            $recruiter->setBenefit($request['benefit']);
            $recruiter->setIsFullRegistered(1);

            return [
                'status'    => 200,
                'message'   => "Dummy message"
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
}
