<?php

namespace Controller;

use Log;
use Exception;
use Helper\Maphelper;
use Helper\StringHelper;
use Model\ChildCompany;
use Model\ModelHelper;
use Resource\ChildCompanyResource;
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

            /** Log Data */
            $logData['childCompanyID']  = $childCompany->user_id;

            /** Set ACF / Meta */

            /** Set recruiter owner */
            $setup['recruiter']     = $childCompany->setChildCompanyOwner($request['user_id']);

            /** Set UUID */
            $setup['uuid']          = $childCompany->setUUID($uuid);

            /** Required */
            $setup['childCompanyName']  = $childCompany->setName($request['companyName']);
            $setup['childCompanyEmail'] = $childCompany->setEmail($request['companyEmail']);
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
                    "message"   => $this->message->get("system.overall_failed", [' Failed store child company data.']),
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

        try {
            $childCompanyModel = new ChildCompany();

            /** Set Filters */
            $filters = [
                "owner"     => $request['user_id'],
                'page'      => isset($request['page']) ? (int)$request['page'] : 1,
                'perPage'   => isset($request['perPage']) ? (int)$request['perPage'] : -1,
            ];

            if (isset($request['orderBy'])) {
                $filters['orderBy']   = $request['orderBy'];
            }

            if (isset($request['sort'])) {
                $filters['order']     = $request['sort'];
            }

            $filters['offset'] = $filters['page'] <= 1 ? 0 : ((intval($filters['page']) - 1) * intval($filters['perPage']));

            $childCompanies     = ChildCompany::select($filters, []);
            $childCompaniesResponse = [
                'data'  => [],
                'meta'  => [
                    "max_posts" => 0,
                    "max_pages" => 0
                ],
            ];

            if ($childCompanies) {
                $childCompaniesResponse['meta']['max_posts']    = (int)$childCompanies->found_posts;
                $childCompaniesResponse['meta']['max_pages']    = (int)$childCompanies->max_num_pages;
                $childCompaniesResponse['meta']['show_posts']   = (int)count($childCompanies->posts);

                if ($childCompanies->found_posts > 0) {
                    $childCompaniesResponse['data'] = ChildCompanyResource::format($childCompanies->posts);
                }
            }

            return [
                "status"    => 200,
                "message"   => $this->message->get("company_recruiter.child_company.list_success", ["{$childCompaniesResponse['meta']['show_posts']}"]),
                "data"      => $childCompaniesResponse['data'],
                "meta"      => [
                    "page"      => (int)$filters['page'],
                    "perPage"   => $filters['perPage'] == -1 ? 'all' : (int)$filters['perPage'],
                    "totalPage" => $childCompaniesResponse['meta']['max_pages'],
                    "totalData" => $childCompaniesResponse['meta']['max_posts']
                ]
            ];
        } catch (\WP_Error $wp_error) {
            return $this->handleError($wp_error, __CLASS__, __METHOD__, $logData, 'log_list_child_company');
        } catch (Exception $e) {
            return $this->handleError($e, __CLASS__, __METHOD__, $logData, 'log_list_child_company');
        } catch (Throwable $th) {
            return $this->handleError($th, __CLASS__, __METHOD__, $logData, 'log_list_child_company');
        }
    }

    /**
     * Show Single child company function
     *
     * @param array $request
     * @return array
     */
    public function show(array $request): array
    {
        /** Log Attempt */
        $logData = [
            'request'   => $request
        ];
        Log::info("Show Child Company attempt.", json_encode($logData, JSON_PRETTY_PRINT), date("Y_m_d") . "_log_show_child_company");

        try {
            if (is_numeric($request['childCompany'])) {
                $childCompanyModel = ChildCompany::find('id', $request['childCompany']);
            } else if (is_string($request['childCompany']) && (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $request['childCompany']) == 1)) {
                $childCompanyModel = ChildCompany::find('uuid', $request['childCompany']);
            } else if (strpos($request['childCompany'], '-') != false) {
                $childCompanyModel = ChildCompany::find('slug', $request['childCompany']);
            } else {
                $childCompanyModel = ChildCompany::find('slug', $request['childCompany']);
            }

            if ($childCompanyModel->user) {
                $owner = $childCompanyModel->getChildCompanyOwner();
                if ($owner->ID == $request['user_id']) {
                    /** Log Attempt */
                    $logData['message'] = 'SUCCESS!';
                    Log::info("END Child Company attempt.", json_encode($logData, JSON_PRETTY_PRINT), date("Y_m_d") . "_log_show_child_company");

                    return [
                        "status"    => 200,
                        "message"   => $this->message->get("company_recruiter.child_company.show_success", [""]),
                        "data"      => ChildCompanyResource::single($childCompanyModel->user)
                    ];
                } else {
                    /** Log Attempt */
                    $logData['message'] = 'UNAUTHORIZED!';
                    Log::error("Fail Child Company attempt.", json_encode($logData, JSON_PRETTY_PRINT), date("Y_m_d") . "_log_show_child_company");

                    return [
                        "status"    => 400,
                        "message"   => $this->message->get("company_recruiter.child_company.show_unauthorized", [""]),
                    ];
                }
            } else {
                /** Log Attempt */
                $logData['message'] = 'NOT FOUND!';
                Log::error("Fail Child Company attempt.", json_encode($logData, JSON_PRETTY_PRINT), date("Y_m_d") . "_log_show_child_company");

                return [
                    "status"    => 404,
                    "message"   => $this->message->get("company_recruiter.child_company.show_not_found", [""]),
                ];
            }
        } catch (\WP_Error $wp_error) {
            return $this->handleError($wp_error, __CLASS__, __METHOD__, $logData, 'log_show_child_company');
        } catch (Exception $e) {
            return $this->handleError($e, __CLASS__, __METHOD__, $logData, 'log_show_child_company');
        } catch (Throwable $th) {
            return $this->handleError($th, __CLASS__, __METHOD__, $logData, 'log_show_child_company');
        }
    }

    /**
     * Update child company function
     *
     * @param array $request
     * @return array
     */
    public function update(array $request): array
    {
        /** Log Attempt */
        $logData        = [
            "request"   => $request
        ];
        Log::info("Update Child Company attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_update_child_company");

        $this->wpdb->query('START TRANSACTION');
        try {
            if (is_numeric($request['childCompany'])) {
                $childCompany = ChildCompany::find('id', $request['childCompany']);
            } else if (is_string($request['childCompany']) && (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $request['childCompany']) == 1)) {
                $childCompany = ChildCompany::find('uuid', $request['childCompany']);
            } else if (strpos($request['childCompany'], '-') != false) {
                $childCompany = ChildCompany::find('slug', $request['childCompany']);
            } else {
                $childCompany = ChildCompany::find('slug', $request['childCompany']);
            }

            if ($childCompany->user) {
                $owner = $childCompany->getChildCompanyOwner();
                if ($owner->ID == $request['user_id']) {
                    /** Set ACF / Meta */
                    /** Rule is Required */

                    if (array_key_exists('companyName', $request)) {
                        $update['childCompanyName']  = $childCompany->setName($request['companyName']);
                    }

                    if (array_key_exists('companyEmail', $request)) {
                        $update['childCompanyEmail'] = $childCompany->setEmail($request['companyEmail']);
                    }

                    if (array_key_exists('country', $request)) {
                        $update['country']       = $childCompany->setCountry($request['country']);
                    }

                    if (array_key_exists('countryCode', $request)) {
                        $update['countryCode']   = $childCompany->setCountryCode($request['countryCode']);
                    }

                    if (array_key_exists('city', $request)) {
                        $update['city']          = $childCompany->setCity($request['city']);
                    }

                    if (array_key_exists('street', $request)) {
                        $update['street']        = $childCompany->setStreet($request['street']);
                    }

                    if (array_key_exists('postCode', $request)) {
                        $update['postCode']      = $childCompany->setPostCode($request['postCode']);
                    }

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

                    if (array_key_exists('longitude', $request)) {
                        $update['longitude']     = $childCompany->setLongitude($request['longitude']);
                    }

                    if (array_key_exists('latitude', $request)) {
                        $update['latitude']      = $childCompany->setLatitude($request['latitude']);
                    }

                    if (array_key_exists('sector', $request)) {
                        $update['sector']        = $childCompany->setSector($request['sector']);
                    }

                    if (array_key_exists('shortDescription', $request)) {
                        $update['shortDescription']  = $childCompany->setShortDescription($request['shortDescription']);
                    }
                    /** Rules is required End */

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
                            $update['gallery']  = $childCompany->setGallery($galleryIDs);
                        }
                    }

                    /** Upload Image */
                    if (isset($request['image']) || isset($_FILES['image'])) {
                        $image = ModelHelper::handle_upload('image');
                        if ($image) {
                            $imageID            = wp_insert_attachment($image['image']['attachment'], $image['image']['file']);
                            // update_field('ucma_image', $imageID, 'user_' . $request['user_id']);
                            $update['image']    = $childCompany->setImage($imageID);
                        }
                    }

                    /** Set Video File or URL */
                    if (isset($_FILES['companyVideo']['name'])) {
                        $video = ModelHelper::handle_upload('companyVideo');
                        $update['videoUrl'] = $childCompany->setVideoUrl($video);
                    } else {
                        $update['videoUrl'] = $childCompany->setVideoUrl($request['companyVideo']);
                    }

                    if (isset($request['phoneNumberCode']) && array_key_exists('phoneNumberCode', $request)) {
                        $update['phoneNumberCode']  = $childCompany->setPhoneCode($request['phoneNumberCode']);
                    }

                    if (isset($request['phoneNumber']) && array_key_exists('phoneNumber', $request)) {
                        $update['phoneNumber']  = $childCompany->setPhoneNumber($request['phoneNumber']);
                    }

                    if (isset($request['employeesTotal']) && array_key_exists('employeesTotal', $request)) {
                        $update['employeesTotal']   = $childCompany->setTotalEmployees($request['employeesTotal']);
                    }

                    if (isset($request['kvkNumber']) && array_key_exists('kvkNumber', $request)) {
                        $update['kvkNumber']        = $childCompany->setKvkNumber($request['kvkNumber']);
                    }

                    if (isset($request['btwNumber']) && array_key_exists('btwNumber', $request)) {
                        $update['btwNumber']        = $childCompany->setBtwNumber($request['btwNumber']);
                    }

                    if (isset($request['website']) && array_key_exists('website', $request)) {
                        $update['website']          = $childCompany->setWebsite($request['website']);
                    }

                    if (isset($request['linkedin']) && array_key_exists('linkedin', $request)) {
                        $update['linkedin']         = $childCompany->setLinkedin($request['linkedin']);
                    }

                    if (isset($request['facebook']) && array_key_exists('facebook', $request)) {
                        $update['facebook']         = $childCompany->setFacebook($request['facebook']);
                    }

                    if (isset($request['instagram']) && array_key_exists('instagram', $request)) {
                        $update['instagram']        = $childCompany->setInstagram($request['instagram']);
                    }

                    if (isset($request['twitter']) && array_key_exists('twitter', $request)) {
                        $update['twitter']          = $childCompany->setTwitter($request['twitter']);
                    }

                    if (isset($request['secondaryEmploymentConditions']) && array_key_exists('secondaryEmploymentConditions', $request)) {
                        /** Set Benefit or secondary employment conditions */
                        $update['benefit']          = $childCompany->setSecondaryEmploymentCondition($request['secondaryEmploymentConditions']);
                    }

                    $update['isFullRegistered'] = $childCompany->setIsFullRegistered(1);

                    /** Log Data  */
                    $logData['update']   = $update;

                    /** Log Attempt */
                    Log::info("End Update Child Company attempt.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_update_child_company");

                    $this->wpdb->query('COMMIT');

                    return [
                        'status'    => 200,
                        'message'   => $this->message->get("profile.setup.success"),
                    ];
                } else {
                    /** Log Attempt */
                    $logData['message'] = 'UNAUTHORIZED!';
                    Log::error("Fail Update Child Company attempt.", json_encode($logData, JSON_PRETTY_PRINT), date("Y_m_d") . "_log_update_child_company");

                    return [
                        "status"    => 400,
                        "message"   => $this->message->get("company_recruiter.child_company.show_unauthorized", [""]),
                    ];
                }
            } else {
                /** Log Attempt */
                $logData['message'] = 'NOT FOUND!';
                Log::error("Fail Update Child Company attempt.", json_encode($logData, JSON_PRETTY_PRINT), date("Y_m_d") . "_log_update_child_company");

                return [
                    "status"    => 404,
                    "message"   => $this->message->get("company_recruiter.child_company.show_not_found", [""]),
                ];
            }
        } catch (\WP_Error $wp_error) {
            return $this->handleError($wp_error, __CLASS__, __METHOD__, $logData, 'log_update_child_company');
        } catch (Exception $e) {
            return $this->handleError($e, __CLASS__, __METHOD__, $logData, 'log_update_child_company');
        } catch (Throwable $th) {
            return $this->handleError($th, __CLASS__, __METHOD__, $logData, 'log_update_child_company');
        }
    }

    /**
     * Get Child company data for vacacny default value function
     *
     * @param array $request
     * @return array
     */
    public function getVacancyDefaultValue(array $request): array
    {
        /** Log Attempt */
        $logData = [
            'request'   => $request
        ];
        Log::info("Show Child Company attempt.", json_encode($logData, JSON_PRETTY_PRINT), date("Y_m_d") . "_log_vacancy_default_value_child_company");

        try {
            if (is_numeric($request['childCompany'])) {
                $childCompanyModel = ChildCompany::find('id', $request['childCompany']);
            } else if (is_string($request['childCompany']) && (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $request['childCompany']) == 1)) {
                $childCompanyModel = ChildCompany::find('uuid', $request['childCompany']);
            } else if (strpos($request['childCompany'], '-') != false) {
                $childCompanyModel = ChildCompany::find('slug', $request['childCompany']);
            } else {
                $childCompanyModel = ChildCompany::find('slug', $request['childCompany']);
            }

            $videoUrl = $childCompanyModel->getVideoUrl() ?? null;
            if ($videoUrl && !empty($videoUrl)) {
                $videoUrl = strpos($childCompanyModel->getVideoUrl(), "youtu") ? ["type" => "url", "url" => $childCompanyModel->getVideoUrl()] : ["type" => "file", "url" => $childCompanyModel->getVideoUrl()];
            }

            return [
                'status'    => 200,
                'message'   => $this->message->get('company.profile.get_success'),
                'data'      => [
                    'videoUrl'      => !empty($videoUrl) ? $videoUrl : null,
                    'socialMedia'   => [
                        'facebook'  => $childCompanyModel->getFacebook(),
                        'linkedin'  => $childCompanyModel->getLinkedin(),
                        'instagram' => $childCompanyModel->getInstagram(),
                        'twitter'   => $childCompanyModel->getTwitter(),
                    ],
                    'gallery'       => array_values($childCompanyModel->getGallery(true)),
                    'secondaryEmploymentCondition' => $childCompanyModel->getSecondaryEmploymentCondition() ?? NULL,
                ]
            ];
        } catch (\WP_Error $wp_error) {
            return $this->handleError($wp_error, __CLASS__, __METHOD__, $logData, 'log_vacancy_default_value_child_company');
        } catch (Exception $e) {
            return $this->handleError($e, __CLASS__, __METHOD__, $logData, 'log_vacancy_default_value_child_company');
        } catch (Throwable $th) {
            return $this->handleError($th, __CLASS__, __METHOD__, $logData, 'log_vacancy_default_value_child_company');
        }
    }
}
