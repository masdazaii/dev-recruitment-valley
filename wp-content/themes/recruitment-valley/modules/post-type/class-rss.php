<?php

namespace PostType;

use Global\Rss\RssController;
use Helper\ValidationHelper;

defined("ABSPATH") or die("Direct access not allowed!");

class RssCPT extends RegisterCPT
{
    public function __construct()
    {
        add_action('init', [$this, 'RegisterRSSCPT']);
        add_action('wp_ajax_get_vacancy_option_value', [$this, 'getVacancyOptionValues']);
        add_action('wp_ajax_nopriv_get_vacancy_option_value', [$this, 'getVacancyOptionValues']);
    }

    public function RegisterRSSCPT()
    {
        $title = __('RSS', THEME_DOMAIN);
        $slug = 'rss';
        $args = [
            'menu_position' => 5,
            'supports' => array('title', 'editor', 'author', 'thumbnail')
        ];

        $this->customPostType($title, $slug, $args);
    }

    public function getVacancyOptionValues()
    {
        try {
            /** Validate and sanitize request */
            $validator  = new ValidationHelper('vacancyOptionValue', $_POST);

            if (!$validator->tempValidate()) {
                $errors     = $validator->getErrors();
                $message    = '';
                foreach ($errors as $field => $message) {
                    $message .= $field . ' : ' . $message . PHP_EOL;
                }

                wp_send_json([
                    'success'   => false,
                    'message'   => $message
                ], 400);
            }

            /** Validate nonce */
            if (!$validator->validateNonce('nonce_vacancy_option_value', $_POST['nonce'])) {
                wp_send_json([
                    'success' => false,
                    'message' => $validator->getNonceError()
                ], 400);
            }

            /** Sanitize request body */
            $validator->tempSanitize();
            $body = $validator->getData();

            $rssController  = new RssController();
            $optionValues   = $rssController->vacancyOptionValue($body['company'], -1);

            wp_send_json([
                'success'   => true,
                'message'   => 'Success get values',
                'data'      => $optionValues
            ], 200);
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }
}

// Initialize
new RssCPT();
