<?php


/**
 * This is the init file.
 * It configures the application preferences (just before the application is launched).
 *
 */


use Bat\FileSystemTool;
use Core\Services\A;
use Kamille\Architecture\Environment\Web\Environment;


$environment = Environment::getEnvironment();


//--------------------------------------------
// PHP CONF
//--------------------------------------------
date_default_timezone_set('Europe/Paris');

ini_set("display_errors", "1");
error_reporting(E_ALL);
ini_set('error_log', __DIR__ . "/logs/php.log.txt");


if ('cli' !== PHP_SAPI) {
    /**
     * @todo-ling
     * Trying to avoid sharing sessions between websites.
     * (so that the user doesn't have weird problems when she comes from lee.us to lee.fr for instance)
     * Is this code really useful?
     */
    $sessDir = ini_get('session.save_path') . "/" . $_SERVER['HTTP_HOST'];
    if (!is_dir($sessDir)) {
        FileSystemTool::mkdir($sessDir);
    }
    ini_set('session.save_path', $sessDir);
}





