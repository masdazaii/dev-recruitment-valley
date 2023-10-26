<?php

namespace Global\FAQ;

use WP_REST_Request;
use ResponseHelper;

defined('ABSPATH') or die('Direct access not allowed!');

class FaqService
{
    private $faqController;

    public function __construct()
    {
        $this->faqController = new FaqController();
    }

    public function list(WP_REST_Request $request)
    {
        $params = $request->get_params();

        $response = $this->faqController->list($params);
        return ResponseHelper::build($response);
    }
}
