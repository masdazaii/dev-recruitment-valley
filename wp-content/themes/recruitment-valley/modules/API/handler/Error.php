<?php

class MI_WP_Error extends WP_Error
{
    public function to_array()
    {
        $error_data = $this->errors;
        $status = $this->get_error_data('status');

        $error_array = array(
            'message' => $this->get_error_message(),
            'status' => $status
        );

        return $error_array;
    }
}