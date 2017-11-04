<?php


use Bat\CaseTool;
use Core\Services\A;
use PhpExcelTool\PhpExcelTool;


require_once __DIR__ . "/../boot.php";
require_once __DIR__ . "/../init.php";


A::testInit();
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';



$target = __DIR__ . "/baked/liste-ville-equipement-et-total.xlsx";



//--------------------------------------------
// SCRIPT
//--------------------------------------------
/**
 * Totals
 */
$file = __DIR__ . "/baked/city2total.txt";
$lines = file($file, \FILE_IGNORE_NEW_LINES);
$city2Total = [];
foreach ($lines as $line) {
    $p = explode(':', $line);
    $city2Total[$p[0]] = $p[1];
}


$file = '/Users/pierrelafitte/Downloads/Liste des Villes Equipements.xlsx';
$rows = PhpExcelTool::getRows($file, [
    'skipTop' => 2,
]);


$notFound = [];
$fixes = [
    'tourcoin' => 'tourcoing',
    'anthony' => 'antony',
    'chalon-sur-saonne' => 'chalon-sur-saone',
    'aubagnes' => 'aubagne',
    'garges-les-gonesse' => 'garge-les-gonesse',
    'joue-les-tous' => 'joue-les-tours',
    'montigny-les-bretonneux' => 'montigny-le-bretonneux',
    'neuily-sur-marne' => 'neuilly-sur-marne',
    'hagueneau' => 'haguenau',
    'puy-en-velay' => 'le-puy-en-velay',
];


foreach ($rows as $k => $row) {
    $nbHabitants = $row[3];
    $city = CaseTool::toDog(strtolower(trim($row[2])));

    $number = null;
    if (array_key_exists($city, $city2Total)) {
        $number = $city2Total[$city];
    }
    elseif (array_key_exists($city, $fixes)) {
        $number = $city2Total[$fixes[$city]];
    } else {
        $number = "notFound";
        $notFound[] = $city;
    }

    array_splice($row, 4, null, $number);
    $rows[$k] = $row;
}




PhpExcelTool::createExcelFileByData($target, $rows, [
    'propertiesFn' => function (\PHPExcel_DocumentProperties $props) {
        $props->setTitle("Liste des villes équipements et nombre clubs");
    },
]);



PhpExcelTool::createExcelFileByData($target, $rows, [
    'propertiesFn' => function (\PHPExcel_DocumentProperties $props) {
        $props->setTitle("Liste des villes équipements et nombre clubs");
    },
]);


