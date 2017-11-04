<?php


use Core\Services\A;


require_once __DIR__ . "/../boot.php";
require_once __DIR__ . "/../init.php";


A::testInit();

//--------------------------------------------
// COLLECT
//--------------------------------------------
/**
 * Fetch all uri found in the liste-departements-uri.txt file
 * and put the raw html in raw-html.
 */
$f = "/pathto/php/projects/spider/www/sites/hopital.fr/liste-departements-uri.txt";
$dstDir = "/pathto/php/projects/spider/www/sites/hopital.fr/raw-html/departements";


$lines = file($f, \FILE_IGNORE_NEW_LINES);

foreach ($lines as $uri) {
    $uri = trim($uri);
    $p = explode('/', $uri);
    $departement = array_pop($p);


    $content = file_get_contents($uri);

    $dstFile = $dstDir . "/$departement.html";
    echo $dstFile . '<br>';
    file_put_contents($dstFile, $content);
}
