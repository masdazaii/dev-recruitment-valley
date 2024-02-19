<?php

/**
 * Autoloading
 *
 * @package MadeIndonesia
 */

namespace BD\Dashboard\Restrictions;

defined('ABSPATH') || die("Can't access directly");

// for quick testing.
$is_enabled = true;

if (!$is_enabled) {
	return;
}

// require classes.
require_once __DIR__ . '/admin/class-restrict.php';
require_once __DIR__ . '/admin/class-setup.php';

// require custom fields.
require_once __DIR__ . '/acf/dashboard-restrictions.php';
