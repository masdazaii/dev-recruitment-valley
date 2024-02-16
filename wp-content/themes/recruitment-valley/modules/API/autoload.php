<?php

/**
 * Autoloading
 *
 * @package Madeindonesia
 */

defined('ABSPATH') || die("Can't access directly");

$API = [
    __DIR__ . '/constant/*.php',
    __DIR__ . '/base/*Bag.php',
    __DIR__ . '/base/Base*.php',
    __DIR__ . '/validator/*.php',
    __DIR__ . '/validator/*/*.php',
    __DIR__ . '/helpers/*.php',
    __DIR__ . '/request/RequestInterface.php',
    __DIR__ . '/request/*.php',
    __DIR__ . '/middleware/*.php',
    __DIR__ . '/features/global/notification/NotificationService.php',
    __DIR__ . '/features/global/notification/NotificationController.php',
    __DIR__ . '/features/*/*/*.php',
    __DIR__ . '/features/*/*/*/*.php',
    __DIR__ . '/model/*.php',
    __DIR__ . '/route/global.php',
    __DIR__ . '/route/sitemap.php',
    __DIR__ . '/route/webhook.php',
    __DIR__ . '/route/*.php',
];

foreach ($API as $path) {
    foreach (glob($path) as $file) {
        require_once $file;
    }
}
