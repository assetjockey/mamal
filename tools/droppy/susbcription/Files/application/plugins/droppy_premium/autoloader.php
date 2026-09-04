<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Droppy Premium - Autoloader
 */

// Autoload config
$premiumJsonConfig = file_get_contents(dirname(__FILE__) . '/config.json');
$premium_config = json_decode($premiumJsonConfig, true)['premium'];

// Autload models
foreach (glob(dirname(__FILE__) . "/models/*.php") as $filename)
{
    require_once $filename;
}