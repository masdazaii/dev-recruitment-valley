<?php

namespace Vacancy\Import;

use ResponseHelper;
use Vacancy\Import\Xml\FlexFeedController;
use Vacancy\Import\JSON\NationaleVacatureBankController as NVBController;
use Vacancy\Import\Jobfeed\JobfeedController;
use WP_REST_Request;

class ImportService
{
    private $_nvbController;
    private $_flexFeedController;
    private $_jobfeedController;
    private $_bynerController;

    public function __construct()
    {
        // $this->flexFeedController = new FlexFeedController;
        $this->_flexFeedController  = new FlexFeedController(FLEXFEED_API_URL ?? NULL);
        $this->_nvbController       = new NVBController();
        $this->_jobfeedController   = new JobfeedController();
        $this->_bynerController     = new BynerController(BYNER_AUTH_PATH ?? NULL);
    }

    // public function flexFeed()
    // {
    //     $response = $this->flexFeedController->parse();
    //     return ResponseHelper::build( $response );
    // }

    public function flexFeed()
    {
        $response = $this->_flexFeedController->import(1);
        return ResponseHelper::build([
            'status' => 200,
            'message' => 'done',
            'data' => $response
        ]);
    }

    public function nationaleVacatureBank()
    {
        $this->_nvbController->import(1, 1444);
        return ResponseHelper::build([
            'status' => 200,
            'message' => 'done'
        ]);
    }

    public function jobfeedImport(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $this->_jobfeedController->import($params, 1, 0);
        return ResponseHelper::build([
            'status' => 200,
            'message' => 'done'
        ]);
    }

    public function jobfeedExpire(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $this->_jobfeedController->expire($params, 1, 0);
        return ResponseHelper::build([
            'status' => 200,
            'message' => 'done'
        ]);
    }

    public function bynerImport(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $this->_jobfeedController->import($params, 1, 0);
        return ResponseHelper::build([
            'status' => 200,
            'message' => 'done'
        ]);
    }

    /** Start : Function only for developer */
    public function flexFeedSetTerm(WP_REST_Request $request)
    {
        $response = $this->_flexFeedController->setTerm($request->get_params());
        return ResponseHelper::build($response);
    }

    public function jobfeedUpdate(WP_REST_Request $request)
    {
        $response = $this->_jobfeedController->updateData($request->get_params());
        return ResponseHelper::build($response);
    }
    /** End : Function only for developer */
}
