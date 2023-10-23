<?php

namespace Ajax\Rss;

use Global\Rss\RssController;
use Helper\ValidationHelper;
use Vacancy\VacancyCrudController;

class AjaxVacancy
{
    public function __construct()
    {
        add_action('wp_ajax_get_vacancies_by_company', [$this, 'getVacancyByCompany']);
        add_action('wp_ajax_nopriv_get_vacancies_by_company', [$this, 'getVacancyByCompany']);

        add_filter("acf/load_field/key=rv_rss_select_vacancy", 'filter_field', 10, 1);
    }

    public function getVacancyByCompany()
    {
        try {
            /** Validate and sanitize request */
            $validator  = new ValidationHelper('vacancyByCompany', $_POST);

            if (!$validator->tempValidate()) {
                $errors     = $validator->getErrors();
                $message    = '';
                foreach ($errors as $field => $message) {
                    $message .= $field . ' : ' . $message[0] . PHP_EOL;
                }

                wp_send_json([
                    'success'   => false,
                    'message'   => $message,
                    'errors'    => $errors
                ], 400);
            }

            /** Validate nonce */
            if (!$validator->validateNonce('get_vacancies_by_company', $_POST['nonce'])) {
                wp_send_json([
                    'success' => false,
                    'message' => $validator->getNonceError()
                ], 400);
            }

            /** Sanitize request body */
            $validator->tempSanitize();
            $body = $validator->getData();

            $vacancyController  = new VacancyCrudController();
            $optionValues       = $vacancyController->getVacancyByCompany($body['company'], -1, $body['result']);

            wp_send_json([
                'success'   => true,
                'message'   => 'Success get values',
                'data'      => $optionValues
            ], 200);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            wp_send_json([
                'success'   => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }

    function filter_field(array $field): array
    {

        if (defined('DOING_AJAX') && DOING_AJAX) {

            $field['choices'] = 'a';
            $field['choices'] = 'b';
        }

        return $field;
    }
}

// Initialize
new AjaxVacancy();
