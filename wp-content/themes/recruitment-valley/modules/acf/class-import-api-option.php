<?php

namespace OptionPage;

use Error;
use Helper\EncryptionHelper;
use Log;
use Model\Option;

defined('ABSPATH') or die('Direct access not allowed!');

class ImportApiSetting
{
    public function __construct()
    {
        $this->importApiOptionPage();
        add_filter('pre_update_option_options_rv_byner_client_secret', [$this, 'encryptClientSecret'], 10, 3);
        add_filter('pre_update_option_options_rv_byner_password', [$this, 'encryptClientPassword'], 10, 3);
    }

    private function importApiOptionPage()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title'    => 'Import API Settings',
                'menu_title'    => 'Import API Settings',
                'menu_slug'     => 'import-api-settings',
                'capability'    => 'edit_posts',
                'redirect'      => false
            ]);
        }
    }

    /**
     * Encrypt Byner client secret before store in database function
     *
     * @param Mixed $value
     * @param Mixed $old
     * @param String $option
     * @return Mixed
     */
    public function encryptClientSecret(Mixed $value, Mixed $old, String $option): mixed
    {
        if ($option == 'options_rv_byner_client_secret') {
            /** Log Attempt */
            $logData = [
                'value'     => $value,
                'old'       => $old,
                'option'    => $option
            ];
            Log::info("Filter Client Secret.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . '_log_filter_secret');

            $optionModel    = new Option();

            /** Check if value is same as current encrypted value in database
             * This to avoid double encrypted
             */
            if ($value !== $old) {
                if (isset($value) && !empty($value) && $value !== NULL) {
                    /** Get IV is existed */
                    $iv = $optionModel->getBynerIV();
                    if (!isset($iv) || empty($iv) || $iv == false || !is_string($iv)) {
                        $iv = NULL;
                    }

                    /** Encrypt */
                    $encryption = EncryptionHelper::encrypt('openssl', $value, DEV_PASSWORD, 'AES-256-CBC', ['option' => 0, 'iv' => $iv]);

                    /** Store Encryption IV */
                    $setIV          = $optionModel->setBynerIV($encryption['iv']);
                    if ($setIV) {
                        /** Set encrypted as new value */
                        $value  = $encryption['encrypted'];
                    }
                } else {
                    $setIV          = $optionModel->setBynerIV('');
                }

                $logData['message'] = 'NEW SECRET!';
                Log::info("End Filter Client Secret.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . '_log_filter_secret');
            }
        }

        return $value;
    }

    /**
     * Encrypt Byner client password before store in database function
     *
     * @param Mixed $value
     * @param Mixed $old
     * @param String $option
     * @return Mixed
     */
    public function encryptClientPassword(Mixed $value, Mixed $old, String $option): mixed
    {
        if ($option == 'options_rv_byner_password') {
            /** Log Attempt */
            $logData = [
                'value'     => $value,
                'old'       => $old,
                'option'    => $option
            ];
            Log::info("Filter Client Password.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . '_log_filter_password');

            $optionModel    = new Option();

            /** Check if value is same as current encrypted value in database
             * This to avoid double encrypted
             */
            if ($value !== $old) {
                if (isset($value) && !empty($value) && $value !== NULL) {
                    /** Get IV : initialization vector is existed */
                    $iv = $optionModel->getBynerIV();
                    if (!isset($iv) || empty($iv) || $iv == false || !is_string($iv)) {
                        $iv = NULL;
                    }

                    /** Encrypt */
                    $encryption = EncryptionHelper::encrypt('openssl', $value, DEV_PASSWORD, 'AES-256-CBC', ['option' => 0, 'iv' => $iv]);

                    /** Store Encryption IV : initialization vector */
                    $setIV          = $optionModel->setBynerIV($encryption['iv']);
                    if ($setIV) {
                        /** Set encrypted as new value */
                        $value  = $encryption['encrypted'];
                    }
                } else {
                    $setIV          = $optionModel->setBynerIV('');
                }

                $logData['message'] = 'NEW Password!';
                Log::info("End Filter Client Password.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . '_log_filter_password');
            }
        }

        return $value;
    }
}

// Initialize
new ImportApiSetting();
