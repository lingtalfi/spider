<?php


use Bat\CaseTool;
use Core\Services\A;
use PhpExcelTool\PhpExcelTool;
use Symfony\Component\DomCrawler\Crawler;


require_once __DIR__ . "/../boot.php";
require_once __DIR__ . "/../init.php";
require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';





A::testInit();




//--------------------------------------------
// THIS CREATE THE city/cities.txt file
//--------------------------------------------

$file = "/Users/pierrelafitte/Downloads/Liste des Villes Equipements.xlsx";
$target = __DIR__ . "/city/cities.txt";

$cities = array_map(function($v){
    return ucfirst(trim($v));
}, array_unique(array_filter(PhpExcelTool::getColumnValues("C", $file))));
sort($cities);


$s = '';
foreach ($cities as $city) {
    $s .= $city . PHP_EOL;
}
file_put_contents($target, $s);

a($cities);




