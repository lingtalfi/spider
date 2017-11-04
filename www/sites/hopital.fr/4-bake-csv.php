<?php


use Core\Services\A;
use DirScanner\YorgDirScannerTool;
use PhpExcelTool\PhpExcelTool;
use Symfony\Component\DomCrawler\Crawler;


require_once __DIR__ . "/../boot.php";
require_once __DIR__ . "/../init.php";


A::testInit();
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';


//--------------------------------------------
// JSON TO CSV
//--------------------------------------------
$dir = "/pathto/php/projects/spider/www/sites/hopital.fr/json";
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

            $row['lien'] = prefixLink($row['lien']);
            $row['depend_de'] = prefixLink($row['depend_de']);

            $row['departement'] = $dpt;
            $rows[] = $row;
        }
    }

}


//--------------------------------------------
// CONVERTING ROWS TO EXCEL
//--------------------------------------------
$target = __DIR__ . "/sites/hopital.fr/baked/liste-hopitaux.xlsx";
$ret = PhpExcelTool::createExcelFileByData($target, $rows, [
    'propertiesFn' => function (PHPExcel_DocumentProperties $props) {
        $props->setCreator("LingTalfi")
            ->setTitle("Liste des hôpitaux trouvés sur hopital.fr")
            ->setSubject("Liste des hôpitaux");
    }
]);
a($ret);

