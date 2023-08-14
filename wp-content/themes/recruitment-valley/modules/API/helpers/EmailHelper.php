<?php

class EmailHelper
{

    public $templates;

    public static function send($sender, $receipient)
    {
        $headers[] = 'From: ' . $sender['name'] . '<' . $postData['reservation-email']  . '>';
    }
}
