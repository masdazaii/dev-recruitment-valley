<?php

namespace Model;

use Helper\EncryptionHelper;

defined('ABSPATH') or die('Direct access not allowed!');

class Option
{
    private $_importRefreshPerDay   = 'import_api_refresh_per_day';
    private $_importUser            = 'import_api_user_to_import';
    private $_importNumberRoleToSet = 'import_api_role_to_set';
    private $_importMappingRole     = 'import_api_mapping_role';
    private $_importMappingRoleTerm = 'import_api_mapping_role_term';
    private $_importRoleKeywords    = 'import_api_mapping_role_keywords';
    private $_importEachKeyword     = 'import_api_mapping_role_eachword';
    private $_importMappingSector       = 'import_api_mapping_sector';
    private $_importMappingSectorTerm   = 'import_api_mapping_sector_term';
    private $_importSectorKeywords      = 'import_api_mapping_sector_keywords';
    private $_importSectorEachKeyword   = 'import_api_mapping_sector_eachword';
    private $_importDefaultImage    = 'import_api_default_image';

    private const acf_rv_email_approval_main_address = 'rv_email_approval_main_address';
    private const acf_rv_email_approval_cc  = 'rv_email_approval_cc';
    private const acf_rv_email_approval_bcc = 'rv_email_approval_bcc';

    private const acf_rv_byner_grant_type       = 'rv_byner_grant_type';
    private const acf_rv_byner_client_id        = 'rv_byner_client_id';
    private const acf_rv_byner_client_secret    = 'rv_byner_client_secret';
    private const acf_rv_byner_username         = 'rv_byner_username';
    private const acf_rv_byner_password         = 'rv_byner_password';
    private const meta_rv_byner_iv              = 'rv_byner_iv';

    public $options;

    public function __construct($all = false)
    {
        if ($all) {
            $this->options = get_fields('option');
        }
    }

    /**
     * Meta / ACF setter function
     *
     * @param String $key
     * @param Mixed $value
     * @param string $type
     * @return mixed
     */
    public function set(String $key, Mixed $value, String $type = 'acf', Bool $autoload = false)
    {
        if ($type == 'meta') {
            return update_option($key, $value, $autoload);
        } else {
            return update_field($key, $value, 'option');
        }
    }

    /**
     * Meta / ACF getter function
     *
     * @param String $key
     * @param boolean $single
     * @param string $type
     * @return mixed
     */
    public function get(String $key, $single = true, String $type = 'acf'): mixed
    {
        if ($type == 'acf') {
            return get_field($key, 'option', $single);
        } else {
            return get_option('option_' . $key, $single);
        }
    }

    public function getImportNumberRoleToSet()
    {
        if (isset($this->options) && is_array($this->options) && !empty($this->options)) {
            if (array_key_exists($this->_importNumberRoleToSet, $this->options)) {
                return $this->options[$this->_importNumberRoleToSet];
            } else {
                return $this->get($this->_importNumberRoleToSet, true);
            }
        } else {
            return $this->get($this->_importNumberRoleToSet, true);
        }
    }

    public function getDefaultImage($result = 'url')
    {
        if ($result === 'object') {
            $attachment = $this->get($this->_importDefaultImage, true);
            if (!empty($attachment)) {
                return [
                    'id' => $attachment['ID'],
                    'title' => $attachment['title'],
                    'url' => $attachment['url']
                ];
            } else {
                return null;
            }
        } else {
            $attachment = $this->get($this->_importDefaultImage);
            return $attachment['url'] ?? null;
        }
    }

    public function getEmailApprovalMainAddress()
    {
        return $this->get(self::acf_rv_email_approval_main_address, true);
    }

    public function getEmailApprovalCC()
    {
        return $this->get(self::acf_rv_email_approval_cc, true);
    }

    public function getEmailApprovalBCC()
    {
        return $this->get(self::acf_rv_email_approval_bcc, true);
    }

    /**
     * Set Byner IV : initialization vector function
     *
     * @return mixed    : String on success or Bool false if meta not found
     */
    public function setBynerIV($value): mixed
    {
        return $this->set(self::meta_rv_byner_iv, $value, 'meta', false);
    }

    /**
     * Get Byner IV : initialization vector function
     *
     * @return mixed    : String on success or Bool false if meta not found
     */
    public function getBynerIV()
    {
        return $this->get(self::meta_rv_byner_iv, true, 'meta');
    }

    public function getGrantType()
    {
        return $this->get(self::acf_rv_byner_grant_type, true, 'acf');
    }

    /**
     * Get Byner Client ID function
     *
     * @return mixed    : String on success or Bool false if meta not found
     */
    public function getBynerClientID(): mixed
    {
        return $this->get(self::acf_rv_byner_client_id, true, 'acf');
    }

    /**
     * Get Byner Client Key function
     *
     * @param boolean $clean    : true to get real client key, false for encrypted client key.
     * @return mixed
     */
    public function getBynerClientKey(Bool $clean = false): mixed
    {
        $value = $this->get(self::acf_rv_byner_client_secret, true, 'acf');
        if ($value) {
            if ($clean) {
                return EncryptionHelper::decrypt('openssl', $value, DEV_PASSWORD, 'AES-256-CBC', ['option' => 0, 'iv' => $this->getBynerIV()]);
            } else {
                return $value;
            }
        } else {
            return $value;
        }
    }

    public function getBynerUsername(): mixed
    {
        return $this->get(self::acf_rv_byner_username, true, 'acf');
    }

    public function getBynerPassword(Bool $clean = false): mixed
    {
        $value = $this->get(self::acf_rv_byner_password, true, 'acf');
        if ($value) {
            if ($clean) {
                return EncryptionHelper::decrypt('openssl', $value, DEV_PASSWORD, 'AES-256-CBC', ['option' => 0, 'iv' => $this->getBynerIV()]);
            } else {
                return $value;
            }
        } else {
            return $value;
        }
    }

    /**
     * Get Import Mapping for Role taxonomy function
     *
     * @return void
     */
    public function getImportMappingRole()
    {
        return $this->get($this->_importMappingRole, true, 'acf');
    }

    /**
     * Get Import Mapping for Sector taxonomy function
     *
     * @return void
     */
    public function getImportMappingSector()
    {
        return $this->get($this->_importMappingSector, true, 'acf');
    }
}
