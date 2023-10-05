<?php

/**
 * Autoloading
 *
 * @package BornDigital
 */

namespace BD;

defined('ABSPATH') || die("Can't access directly");

define('BD_ACF_DOMAIN', 'borndigital_acf');

require_once __DIR__ . '/ajax/class-generate-acf-file.php';
require_once __DIR__ . '/admin/class-setup.php';
require_once __DIR__ . '/class-company-option.php';
require_once __DIR__ . '/class-stripe-option.php';
require_once __DIR__ . '/class-validation.php';
require_once __DIR__ . '/class-custom-action.php';
