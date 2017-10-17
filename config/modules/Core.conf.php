<?php


use Kamille\Architecture\ApplicationParameters\ApplicationParameters;
use Kamille\Architecture\Environment\Web\Environment;

$appDir = ApplicationParameters::get('app_dir');


$env = Environment::getEnvironment();



$dbName = "kamille";

if ('dev' === $env) {

    $quickPdoConf = [
        "dsn" => "mysql:dbname=$dbName;host=127.0.0.1",
        "user" => "root",
        "pass" => "root",
        "options" => [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            /**
             * With this option, QuickPdo::update method could potentially return the number of
             * found rows (using $stmt->rowCount) in the where clause rather than the number of affected rows.
             *
             */
            \PDO::MYSQL_ATTR_FOUND_ROWS => true,
        ],
    ];
} else {
    $dbName = "testleaderfit";
    $quickPdoConf = [
        "dsn" => "mysql:dbname=$dbName;host=127.0.0.1",
        "user" => "testleaderfit",
        "pass" => "passleader",
        "options" => [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        ],
    ];
}


$conf = [
    /**
     * check documentation for more info
     */
    "exceptionController" => 'Controller\Core\ExceptionController:render',
    "useFileLoggerListener" => true,
    "logFile" => $appDir . "/logs/kamille.log.txt",
    "showExceptionTrace" => false,
    //--------------------------------------------
    // DATABASE
    //--------------------------------------------
    "database" => $dbName,
    "useDbLoggerListener" => true,
    "dbLogFile" => $appDir . "/logs/kamille.sql.log.txt",
    "useQuickPdo" => true,
    "quickPdoConfig" => $quickPdoConf,
    //--------------------------------------------
    // TABATHA
    //--------------------------------------------
    /**
     * Hook into QuickPdo instance of the app and clean the tabatha cache using tabathaDb strategy
     * (https://github.com/lingtalfi/TabathaCache#tabatha-db).
     *
     */
    "useTabathaDb" => true,
    "enableTabathaCache" => false,
    //--------------------------------------------
    // JS
    //--------------------------------------------
    "addJqueryEndWrapper" => true,
    //--------------------------------------------
    // ROUTSY
    //--------------------------------------------
    "useCssAutoload" => true,
    //--------------------------------------------
    // DUAL SITE
    //--------------------------------------------
    /**
     * A dual site is when you have a frontoffice AND a backoffice handled by the same application code.
     * If it's not dual, then you only have a front office, or a backoffice, but not both.
     */
    "dualSite" => true,
    "defaultProtocol" => 'https', // http|https
    "uriPrefixBackoffice" => "/admin",
    "themeBack" => "nullosAdmin",
    "themeFront" => ApplicationParameters::get("theme"),

    //--------------------------------------------
    // DEVELOPMENT
    //--------------------------------------------
    /**
     * In fast development,
     * you code your hooks directly in the Hooks class.
     *
     * - when you install a module it skips the hooks (see ApplicationModule constructor)
     * - todo: it doesn't recreate the conf, because you code the conf directly in the app, not in the module...
     */
    "useFastDevelopment" => true,
];