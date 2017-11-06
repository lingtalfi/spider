<?php


use Bat\CaseTool;
use Core\Services\A;
use DirScanner\YorgDirScannerTool;
use PhpExcelTool\PhpExcelTool;
use Symfony\Component\DomCrawler\Crawler;


require_once __DIR__ . "/../boot.php";
require_once __DIR__ . "/../init.php";


A::testInit();
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';


//--------------------------------------------
// COLLECT SECOND LEVEL INFORMATION
//--------------------------------------------
$dir = "/pathto/php/projects/spider/www/sites/hopital.fr/json";
$dstDir = "/pathto/php/projects/spider/www/sites/hopital.fr/raw-html/hopital";
$files = YorgDirScannerTool::getFilesWithExtension($dir, "json");

function prefixLink($string)
{
    if (strlen($string) > 0) {
        $string = "https://hopital.fr" . $string;
    }
    return $string;
}



$rows = [];
foreach ($files as $file) {
    $dpt = substr(basename($file), 0, -5);
    $data = json_decode(file_get_contents($file), true);
    foreach ($data as $row) {
        if ($row) { // Corse du sud: no result
            $uri = prefixLink($row['lien']);
            $content  = file_get_contents($uri);
            $p=explode('/', $row['lien']);
            $component = array_pop($p);
            $file = $dstDir . "/" . CaseTool::toDog($component) . '.html';
            file_put_contents($file, $content);

            a($file);
        }
    }

}


