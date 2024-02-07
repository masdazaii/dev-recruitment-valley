<?php

namespace Model;

use Exception;

defined('ABSPATH') || die("Can't access directly");

abstract class BaseModel
{
    protected $model;
    protected $prefix;

    public $id;
    protected $selector; // this is for acf selector : post id / user id / category id / etc

    protected $message;
    protected $wpdb;
    protected $query;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb     = $wpdb;
        // $this->query    = new QueryBuilder();
    }

    /**
     * Meta / ACF / Option Setter function
     *
     * @param String $key   : Meta / ACF Key without the prefix.
     * @param mixed $value  : Value to set.
     * @param string $type  : Type of meta. Value must be : acf | meta | option
     * @param boolean $autoload  : Is option autoload
     * @return Mixed
     */
    public function set(String $key, mixed $value, String $type = 'acf', Bool $autoload = false)
    {
        /** Get current initialized object. */
        $this->model = get_class($this);

        if ($this->selector !== 'option') {
            $this->checkID();
        }

        if ($type == 'meta') {
            if ($this->selector == 'user_' || $this->selector == 'user') {
                return update_user_meta($this->id, $this->prefix . $key, $value);
            } else if ($this->selector == 'option') {
                return update_option($this->prefix . $key, $value, $autoload);
            } else {
                return update_post_meta($this->id, $this->prefix . $key, $value);
            }
        } else {
            if (!$this->selector || empty($this->selector)) {
                throw new Exception($this->model . " model not valid", 400);
            }

            return update_field($this->prefix . $key, $value, $this->selector);
        }
    }

    /**
     * Meta / ACF / Option Getter function
     *
     * @param String $key       : Meta / ACF Key without the prefix.
     * @param boolean $result   : Indicator for the result. Single result for meta or formatted result for acf.
     * @param string $type      : Type of meta. Value must be : acf | meta | option
     * @return void
     */
    public function get(String $key, bool $result = true, String $type = 'meta')
    {
        if ($this->selector !== 'option') {
            $this->checkID();
        }

        if ($type == 'meta') {
            if (strpos($this->selector, 'user') !== false) {
                return get_user_meta($this->id, $this->prefix . $key, $result);
            } else {
                return get_post_meta($this->selector, $key, $result);
            }
        } else {
            return get_field($this->prefix . $key, $this->selector, $result);
        }
    }

    /**
     * Check ID function
     *
     * Check if current initialization is already specify ID or not.
     * This method should call for model that connected or belong to another stronger entity.
     * e.g : user meta, post meta, term meta, etc.
     *
     * @return void
     */
    public function checkID()
    {
        if (!isset($this->id) || empty($this->id)) {
            throw new Exception('Please specify the ID of post or user!');
        }
    }
}
