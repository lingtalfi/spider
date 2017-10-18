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
// SCRIPT
//--------------------------------------------
/**
 * Collecting rows
 */
$max = 10;
$c = 0;
$dir = __DIR__ . "/json-pages";
$files = YorgDirScannerTool::getFilesWithExtension($dir, "json");
$rows = [];
foreach ($files as $file) {
    $data = json_decode(file_get_contents($file), true);
    $results = $data['results'];
    foreach ($results as $result) {
        $name = $result['name'];
        $address = $result['address']['street'];
        $zipCode = $result['address']['zipCode'];
        $city = $result['address']['city'];
        $subscribed = (int)$result['isSubscribed'];

        $rows[] = [
            'name' => $name,
            'address' => $address,
            'zip' => $zipCode,
            'city' => $city,
            'subscribed' => $subscribed,
        ];
    }
    if ($c++ >= $max) {
        break;
    }
}


//--------------------------------------------
// CONVERTING ROWS TO EXCEL
//--------------------------------------------
$target = __DIR__ . "/baked/liste-salle-sport.xlsx";
$ret = PhpExcelTool::createExcelFileByData($target, $rows, [
    'propertiesFn' => function (PHPExcel_DocumentProperties $props) {
        $props->setCreator("LingTalfi")
            ->setTitle("Liste des salles fitness trouvés sur masalledesport.com")
            ->setSubject("Liste des salles");
    }
]);
a($ret);

