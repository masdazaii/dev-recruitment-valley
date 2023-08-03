<?php


defined('ABSPATH') || exit;

/**
 * WP MailHog
 */
class WP_MailHog
{

    function __construct()
    {
        $this->init_phpmailer();
    }

    /**
     * Override the PHPMailer SMTP options
     *
     * @return void
     */
    public function init_phpmailer()
    {
        if (defined('WP_MAILHOG_HOST') && defined('WP_MAILHOG_PORT')) {
            add_action('phpmailer_init', function ($phpmailer) {
                $phpmailer->Host     = WP_MAILHOG_HOST;
                $phpmailer->Port     = WP_MAILHOG_PORT;
                $phpmailer->SMTPAuth = false;
                $phpmailer->isSMTP();
            });
        }
    }
}

new WP_MailHog();
