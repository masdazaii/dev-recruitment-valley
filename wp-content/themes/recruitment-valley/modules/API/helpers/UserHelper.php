<?php

namespace Helper;

class UserHelper
{
    public static function is_deleted( $userId )
    {
        return get_user_meta($userId, 'is_deleted', true);
    } 
}