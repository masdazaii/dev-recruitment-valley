<?php

/**
 * Autoloading
 *
 * @package BornDigital
 */

defined('ABSPATH') || die("Can't access directly");

require_once __DIR__ . '/class-placeholder.php';
require_once __DIR__ . '/class-setup.php';
require_once __DIR__ . '/class-email.php';
require_once __DIR__ . '/class-email-option.php'; // This is added line.

// require classes.
require_once __DIR__ . '/class-reset-password.php';
