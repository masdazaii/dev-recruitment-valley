<?php

namespace Ajax\Rss;

use Global\Rss\RssController;
use Helper\ValidationHelper;
use Model\Rss;
use Vacancy\Vacancy;
use Vacancy\VacancyCrudController;

class AjaxRSS
{
    public function __construct()
    {
        add_action('wp_ajax_get_selected_data', [$this, 'getSelectedData']);
        // add_action('wp_ajax_nopriv_get_selected_company', [$this, 'getSelectedData']);
    }

    public function getSelectedData()
    {
        try {
            /** Validate and sanitize request */
            $validator  = new ValidationHelper('vacancyByRss', $_GET);

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
            if (!$validator->validateNonce('get_vacancies_by_company', $_GET['nonce'])) {
                wp_send_json([
                    'success' => false,
                    'message' => $validator->getNonceError()
                ], 400);
            }

            /** Sanitize request body */
            $validator->tempSanitize();
            $body = $validator->getData();

            /** Get rss selected vacancies */
            $rssController  = new Rss();
            $rssVacancies   = $rssController->getRssVacancies();
            $selectedVacancy = [];
            if ($rssVacancies && is_array($rssVacancies)) {
                foreach ($rssVacancies as $vacancy) {
                    $vacancyModel = new Vacancy($vacancy);
                    $selectedVacancy[] = [
                        'id'    => $vacancy,
                        'text'  => $vacancyModel->getTitle()
                    ];
                }
            }

            wp_send_json([
                'success'   => true,
                'message'   => 'Success get values',
                'data'      => [
                    'selectedCompany'   => $rssController->getRssCompany(),
                    'selectedacancies'  => $selectedVacancy
                ]
            ], 200);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            wp_send_json([
                'success'   => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }
}

// Initialize
new AjaxRSS();
