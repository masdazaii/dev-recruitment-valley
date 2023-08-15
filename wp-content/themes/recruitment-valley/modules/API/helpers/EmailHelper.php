<?php

namespace Helper;

class EmailHelper
{

    public $templates;

    public static function send(String $template, array $sender, String $receipient, String $subject, array $data)
    {
        ob_start();

        switch ($template) {
            case 'contact-candidate':
            case 'contact-company':
                include THEME_DIR . '/templates/email/test.php';
                $output = ob_get_clean();
                break;
        }
        include THEME_DIR . '/templates/email/test.php';
        $output = ob_get_clean();

        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . $sender['name'] . '<' . $sender['email']  . '>';

        wp_mail($receipient, $subject, $output, $headers);
    }
}
