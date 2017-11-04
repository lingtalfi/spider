<?php


use Core\Services\A;
use DirScanner\YorgDirScannerTool;
use Symfony\Component\DomCrawler\Crawler;


require_once __DIR__ . "/../boot.php";
require_once __DIR__ . "/../init.php";


A::testInit();

//--------------------------------------------
// EXTRACT INFO FROM departements files
//--------------------------------------------
/**
 * - nom
 * - latitude
 * - longitude
 * - lien
 * - indicateur_qualite
 * - avis_patients
 * - nb_avis_patients
 * - capacite
 * - type_structure
 * - depend_de
 * - telephone
 * - site_internet
 * - adresse
 *
 */
$dstDir = "/pathto/php/projects/spider/www/sites/hopital.fr/raw-html/departements";
$files = YorgDirScannerTool::getFilesWithExtension($dstDir, "html");


function getAttribute($text)
{
    $p = explode(":", $text, 2);
    return trim(array_pop($p));
}


//--------------------------------------------
// COLLECT DATA
//--------------------------------------------
foreach ($files as $file) {
    $html = file_get_contents($file);

    $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
    $dstFile = str_replace('departements', 'departements_no_script', $file);
    file_put_contents($dstFile, $html);
    a($dstFile);
}
