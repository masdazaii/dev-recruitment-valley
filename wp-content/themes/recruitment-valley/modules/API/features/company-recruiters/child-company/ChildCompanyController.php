<?php

namespace Controller;

use Log;
use Exception;
use Helper\Maphelper;
use Helper\StringHelper;
use Model\ChildCompany;
use Model\ModelHelper;
use Throwable;
use WP_Error;

class ChildCompanyController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Store / Add Child Company function
     *
     * @param array $request
     * @return array
     */
    public function store(array $request): array
    {
        /** Log Attempt */
        $logData        = [
            "request"   => $request
        ];
        Log::info("Store Child Company attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_store_child_company");

        $this->wpdb->query('START TRANSACTION');
        try {
            /** Create UUID for child company */
            $uuid   = wp_generate_uuid4();

            /** Create post for child company */
            $companySlug    = StringHelper::makeSlug($request['companyName']) . '-' . $uuid;
            $data           = [
                'post_title'    => $request['companyName'],
                'post_name'     => StringHelper::makeSlug($companySlug),
                'post_type'     => 'child-company',
            ];
            $childCompany   = ChildCompany::insert($data);

            /** Set ACF / Meta */
            /** Required */
            $setup['childCompanyName'] = $childCompany->setName($request['companyName']);
            $setup['country']       = $childCompany->setCountry($request['country']);
            $setup['countryCode']   = $childCompany->setCountryCode($request['countryCode']);
            $setup['city']          = $childCompany->setCity($request['city']);
            $setup['street']        = $childCompany->setStreet($request['street']);
            $setup['postCode']      = $childCompany->setPostCode($request['postCode']);

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

            $setup['longitude']     = $childCompany->setLongitude($request['longitude']);
            $setup['latitude']      = $childCompany->setLatitude($request['latitude']);
            $setup['sector']        = $childCompany->setSector($request['sector']);
            $setup['shortDescription']  = $childCompany->setShortDescription($request['shortDescription']);
            /** Required End */

            /** Upload Gallery */
            if (isset($request['gallery']) || isset($_FILES['gallery'])) {
                $galleries = ModelHelper::handle_uploads('gallery', $request['user_id']);

                // $currentGallery = maybe_unserialize(get_user_meta($request['user_id'], $childCompany::acf_childCompany_gallery_photo));
                $currentGallery = maybe_unserialize($childCompany->getGallery('raw'));
                $currentGallery = isset($currentGallery[0]) ? $currentGallery[0] : [];
                if (isset($galleries)) {
                    $galleryIDs = $currentGallery;
                    foreach ($galleries as $key => $gallery) {
                        $galleryIDs[]   = wp_insert_attachment($gallery['attachment'], $gallery['file']);
                    }

                    // update_field($childCompany::acf_childCompany_gallery_photo, $galleryIDs, 'user_' . $request['user_id']);
                    $setup['gallery']  = $childCompany->setGallery($galleryIDs);
                }
            }

            /** Upload Image */
            if (isset($request['image']) || isset($_FILES['image'])) {
                $image = ModelHelper::handle_upload('image');
                if ($image) {
                    $imageID            = wp_insert_attachment($image['image']['attachment'], $image['image']['file']);
                    // update_field('ucma_image', $imageID, 'user_' . $request['user_id']);
                    $setup['image']    = $childCompany->setImage($imageID);
                }
            }

            /** Set Video File or URL */
            if (isset($_FILES['companyVideo']['name'])) {
                $video = ModelHelper::handle_upload('companyVideo');
                $setup['videoUrl'] = $childCompany->setVideoUrl($video);
            } else {
                $setup['videoUrl'] = $childCompany->setVideoUrl($request['companyVideo']);
            }

            if (isset($request['phoneNumberCode']) && array_key_exists('phoneNumberCode', $request)) {
                $setup['phoneNumberCode']  = $childCompany->setPhoneCode($request['phoneNumberCode']);
            }

            if (isset($request['phoneNumber']) && array_key_exists('phoneNumber', $request)) {
                $setup['phoneNumber']  = $childCompany->setPhoneNumber($request['phoneNumber']);
            }

            if (isset($request['employeesTotal']) && array_key_exists('employeesTotal', $request)) {
                $setup['employeesTotal']   = $childCompany->setTotalEmployees($request['employeesTotal']);
            }

            if (isset($request['kvkNumber']) && array_key_exists('kvkNumber', $request)) {
                $setup['kvkNumber']        = $childCompany->setKvkNumber($request['kvkNumber']);
            }

            if (isset($request['btwNumber']) && array_key_exists('btwNumber', $request)) {
                $setup['btwNumber']        = $childCompany->setBtwNumber($request['btwNumber']);
            }

            if (isset($request['website']) && array_key_exists('website', $request)) {
                $setup['website']          = $childCompany->setWebsite($request['website']);
            }

            if (isset($request['linkedin']) && array_key_exists('linkedin', $request)) {
                $setup['linkedin']         = $childCompany->setLinkedin($request['linkedin']);
            }

            if (isset($request['facebook']) && array_key_exists('facebook', $request)) {
                $setup['facebook']         = $childCompany->setFacebook($request['facebook']);
            }

            if (isset($request['instagram']) && array_key_exists('instagram', $request)) {
                $setup['instagram']        = $childCompany->setInstagram($request['instagram']);
            }

            if (isset($request['twitter']) && array_key_exists('twitter', $request)) {
                $setup['twitter']          = $childCompany->setTwitter($request['twitter']);
            }

            if (isset($request['secondaryEmploymentConditions']) && array_key_exists('secondaryEmploymentConditions', $request)) {
                /** Set Benefit or secondary employment conditions */
                $setup['benefit']          = $childCompany->setSecondaryEmploymentCondition($request['secondaryEmploymentConditions']);
            }

            $setup['isFullRegistered'] = $childCompany->setIsFullRegistered(1);

            /** Log Data  */
            $logData['setup']   = $setup;

            /** Check if setup is all success */
            if (in_array(false, $setup)) {
                $failsetup = array_search(false, $setup);

                /** Log fail attempt */
                $logData['message']         = 'Failed store data : ' . $failsetup;
                $logData['fail_to_store']   = $failsetup;
                Log::error("Fail Store Child Company attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_store_child_company");

                $this->wpdb->query('ROLLBACK');

                return [
                    "status"    => 500,
                    "message"   => $this->message->get("registration.overall_failed", ['Failed store user data.']),
                ];
            }

            /** Log Attempt */
            Log::info("End Store Child Company attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_store_child_company");

            $this->wpdb->query('COMMIT');

            return [
                'status'    => 200,
                'message'   => $this->message->get("profile.setup.success"),
            ];
        } catch (\WP_Error $wp_error) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($wp_error, __CLASS__, __METHOD__, $logData, 'log_store_child_company');
        } catch (Exception $e) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($e, __CLASS__, __METHOD__, $logData, 'log_store_child_company');
        } catch (Throwable $th) {
            $this->wpdb->query('ROLLBACK');

            return $this->handleError($th, __CLASS__, __METHOD__, $logData, 'log_store_child_company');
        }
    }

    /**
     * List Child company function
     *
     * @param array $request
     * @return array
     */
    public function list(array $request): array
    {
        /** Log Attempt */
        $logData = [
            'request'   => $request
        ];
        Log::info("List Child Company attempt.", json_encode($logData, JSON_PRETTY_PRINT), date("Y_m_d") . "_log_list_child_company");

        // try {
        //     /** Set Filters */
        //     $filters = [
        //         "meta"  => []
        //     ];

        //     $childCompanies = ChildCompany::select();
        // }

        return [
            "status"    => 200,
            "message"   => "Dummy Response",
            "data"      => [
                [
                    "ID"            => "123123-123213-123123",
                    "recruiterID"   => 1,
                    "companyName"   => "Task Force 141",
                ],
                [
                    "ID"            => "234234-234234-234234",
                    "recruiterID"   => 1,
                    "companyName"   => "Shadow Corp.",
                ],
                [
                    "ID"            => "345345-345345-345345",
                    "recruiterID"   => 1,
                    "companyName"   => "El Sin Nombre",
                ]
            ],
            "meta"      => [
                "totalResults"  => 3,
                "totalFiltered" => 3
            ]
        ];
    }
}
