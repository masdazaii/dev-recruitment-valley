<?php

namespace OptionPage;

defined('ABSPATH') or die('Direct access not allowed!');

class ImportApiSetting
{
    public function __construct()
    {
        $this->importApiOptionPage();
        add_filter('pre_update_option_options_rv_byner_client_secret', [$this, 'encryptClientSecret'], 10, 3);
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

    public function encryptClientSecret(Mixed $value, Mixed $old, String $option)
    {
        if ($option == 'options_rv_byner_client_id') {
            // $value =

            /** Chyper Method */
            $ciphering  = "BF-CBC";

            /** Using OpenSSl encryption method */
            $iv_length      = openssl_cipher_iv_length($ciphering);
            $encryptOption  = 0;

            /** Initialization vector */
            $encryptionIV   = random_bytes($iv_length);

            /** characters or numeric for iv */
            $encryptionKey = openssl_digest(php_uname(), 'MD5', true);

            /** Encryption of string process begins */
            $encryption = openssl_encrypt($value, $ciphering, $encryptionKey, $encryptOption, $encryptionIV);
        }
        return $value;
    }
}

// Initialize
new ImportApiSetting();
