<?php

namespace Vacancy\Import;

use ResponseHelper;
use Vacancy\Import\Xml\FlexFeedController;
use Vacancy\Import\JSON\NationaleVacatureBankController as NVBController;

class ImportService
{
    private $_nvbController;
    private $_flexFeedController;

    public function __construct()
    {
        // $this->flexFeedController = new FlexFeedController;
        $this->_flexFeedController = new FlexFeedController(FLEXFEED_API_URL ?? NULL);
        $this->_nvbController = new NVBController();
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
}
