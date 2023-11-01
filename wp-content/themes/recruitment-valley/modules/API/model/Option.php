<?php

namespace Model;

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

    private const acf_rv_email_approval_main_address = 'rv_email_approval_main_address';
    private const acf_rv_email_approval_cc  = 'rv_email_approval_cc';
    private const acf_rv_email_approval_bcc = 'rv_email_approval_bcc';

    public $options;

    public function __construct($all = false)
    {
        if ($all) {
            $this->options = get_fields('option');
        }
    }

    public function getter($key, $single = true)
    {
        return get_field($key, 'option', $single);
    }

    public function getImportNumberRoleToSet()
    {
        if (isset($this->options) && is_array($this->options) && !empty($this->options)) {
            if (array_key_exists($this->_importNumberRoleToSet, $this->options)) {
                return $this->options[$this->_importNumberRoleToSet];
            } else {
                return $this->getter($this->_importNumberRoleToSet, true);
            }
        } else {
            return $this->getter($this->_importNumberRoleToSet, true);
        }
    }

    public function getEmailApprovalMainAddress()
    {
        return $this->getter(self::acf_rv_email_approval_main_address, true);
    }

    public function getEmailApprovalCC()
    {
        return $this->getter(self::acf_rv_email_approval_cc, true);
    }

    public function getEmailApprovalBCC()
    {
        return $this->getter(self::acf_rv_email_approval_bcc, true);
    }
}
