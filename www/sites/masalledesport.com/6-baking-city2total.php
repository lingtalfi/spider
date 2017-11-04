<?php


use Bat\CaseTool;
use Core\Services\A;
use DirScanner\YorgDirScannerTool;
use PhpExcelTool\PhpExcelTool;


require_once __DIR__ . "/../boot.php";
require_once __DIR__ . "/../init.php";


A::testInit();


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
$total = [];
foreach ($files as $file) {
    $data = json_decode(file_get_contents($file), true);
    $results = $data['results'];
    foreach ($results as $result) {
        $city = $result['address']['city'];

        $city = CaseTool::toDog($city);

        if (false === array_key_exists($city, $total)) {
            $total[$city] = 0;
        }
        $total[$city]++;
    }
}
ksort($total);


$target = __DIR__ . "/baked/city2total.txt";
$s = "";
foreach ($total as $city => $tot) {
    $s .= $city . ":" . $tot . PHP_EOL;
}
file_put_contents($target, $s);
