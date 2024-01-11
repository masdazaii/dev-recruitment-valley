<?php

namespace MI\Bag;

use Exception;
use Throwable;
use WP_Error;

class ErrorBag
{
    protected $error;
    protected $type;
    protected $message;
    protected $code;

    public function __construct(Object $error, String $type, String $message, Int $code)
    {
        $this->error    = $error;
        $this->type     = $type;
        $this->message  = $message;
        $this->code     = $code;
    }

    public static function make(Object $error)
    {
        switch (true) {
            case $error instanceof WP_Error:
                return new self($error, 'WP_Error', $error->get_error_message(), $error->get_error_code());
            case $error instanceof Exception:
                return new self($error, 'Exception', $error->getMessage(), $error->getCode());
            case $error instanceof Throwable:
                return new self($error, 'Throwable', $error->getMessage(), $error->getCode());
            default:
                throw new Exception('Error type not supported!');
        }
    }

    public function code()
    {
        return $this->code;
    }

    public function message()
    {
        return $this->message;
    }

    public function type()
    {
        return $this->type;
    }
}
