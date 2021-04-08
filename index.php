<?php

/**
 * Plugin Name: User Classification
 * Plugin URI: https://github.com/LSVH/wp-user-classification
 * Description: Classify an WordPress user.
 * Version: 0.1.1
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: LSVH
 * Author URI: https://lsvh.org/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: user-classification
 * Domain Path: /languages
 */

$autoloader = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
    die('Autoloader not found.');
}

require $autoloader;

use LSVH\WordPress\Plugin\UserClassification\Bootstrap;

$boot = new Bootstrap(__FILE__);
$boot->exec();
