<?php
/**
 * Autoloading
 *
 * @package BornDigital
 */

namespace BD\Security;

defined( 'ABSPATH' ) || die( "Can't access directly" );

require_once __DIR__ . '/admin/class-disable-file-editing.php';

require_once __DIR__ . '/class-rest-api-auth.php';
require_once __DIR__ . '/class-general-error-messages.php';
require_once __DIR__ . '/class-content-filter.php';
require_once __DIR__ . '/class-hide-wp-version.php';
require_once __DIR__ . '/class-security-header.php';
