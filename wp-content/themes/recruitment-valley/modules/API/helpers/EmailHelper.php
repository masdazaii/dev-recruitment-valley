<?php

namespace Helper;

use BD\Emails\Email;
use Exception;
use Model\Company;
use Package;
use Transaction;

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

    public static function sendPaymentConfirmation($transactionId)
    {
        try {
            $transaction = new Transaction($transactionId);

            $companyId = $transaction->getUserId();

            error_log($companyId);

            $packageId = $transaction->getPackageId();

            $company = new Company($companyId);
            $package = new Package($packageId);

            $args = [
                "company.contactPerson" => $company->getEmail(),
                "company.name" => $company->getName(),
            ];

            $content = Email::render_html_email('payment-confirmation-credits-company.php', $args);
            $site_title = get_bloginfo('name');

            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
            );

            wp_mail($company->getEmail(), "Bevestiging van aankoop credits - $site_title", $content, $headers);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    public static function sendJobAlert()
    {
        
    }
}
