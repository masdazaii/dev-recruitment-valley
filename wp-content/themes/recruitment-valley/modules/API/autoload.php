<?php

/**
 * Autoloading
 *
 * @package Madeindonesia
 */

defined('ABSPATH') || die("Can't access directly");

$API = [
    __DIR__ . '/constant/*.php',
    __DIR__ . '/helpers/*.php',
    __DIR__ . '/middleware/*.php',
    __DIR__ . '/features/*/*/*.php',
    __DIR__ . '/route/*.php',
];

foreach ($API as $path) {
    foreach (glob($path) as $file) {
        require_once $file;
    }
}
