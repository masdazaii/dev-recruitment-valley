<?php

namespace Company\Profile;

use Constant\Message;
use Error;
use Helper\ValidationHelper;
use Model\ModelHelper;
use ResponseHelper;
use WP_REST_Request;

class ProfileModel
{
    public static function user_data($user_id)
    {
        $user = get_user_by('id', $user_id);
        $acf = get_fields("user_" . $user_id);
        $data = (array)$user->data;
        unset($data['user_pass']);
        $data['acf'] = $acf;

        return $data;
    }
}
