<?php

namespace MI\Base\Controller;

defined("ABSPATH") or die("Direct access not allowed!");

use MI\Bag\ErrorBag;
use Constant\Message;
use Log;
use WP_Error;
use Exception;
use Throwable;

abstract class BaseController
{
    protected $message;
    protected $wpdb;

    public function __construct()
    {
        global $wpdb;
        // $this->message  = new Message();
        $this->wpdb     = $wpdb;
    }

    public function handleError(Object $error, String $class, String $method, array $data = [], String $logname = 'log_error')
    {
        $error = ErrorBag::make($error);

        error_log("{$error->type()} : " . $error->message() . " - logged by {$method}.");
        Log::error("{$error->type()} : " . $error->message() . " - logged by {$method}.", array_merge($data, [$error]), date('Y_m_d') . "_{$logname}");

        switch (true) {
            case 400 <= $error->code() && $error->code() <= 499:
                $errorMessage = $error->message();
                break;
            default:
                $errorMessage = "System Failure";
                break;
        }

        return [
            "status"    => $error->code() && $error->code() != 0 ? $error->code() : 500,
            "message"   => $errorMessage,
        ];
    }
}
