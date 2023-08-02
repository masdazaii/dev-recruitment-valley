<?php

namespace Helper;

class OTPHelper
{
    public static function generate(Int $length)
    {
        $charSet = '0123456789';
        $otp = '';
        $charSetLength = strlen($charSet);

        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $charSetLength - 1);
            $otp .= $charSet[$index];
        }

        return $otp;
    }
}
