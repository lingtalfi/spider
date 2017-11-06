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

function getPersons($file)
{

    $persons = [];
    $html = file_get_contents($file);
    $crawler = new Crawler($html);
    $foundInFile = false;

    $richText = $crawler->filter("main > .block > .rich_text");
    if ($richText->count()) {
        $ul = $richText->filter("ul.list_attributes");
        if ($ul->count()) {
            $firstUl = $ul->eq(0);
            $lis = $firstUl->filter("li");
            $persons = [];
            $lis->each(function (Crawler $_crawler) use (&$persons, &$foundInFile) {
                $titre = trim(rtrim(trim($_crawler->filter("strong")->text()), ":"));
                $nom = trim($_crawler->filter("span")->text());

                $persons[] = [
                    'titre' => $titre,
                    'nom' => $nom,
                ];
                $foundInFile = true;
            });
        }
    }

    return $persons;

}


header("Content-type: text/plain");
$file2Uri = [];
$rows = [];
$c = 0;
foreach ($files as $file) {
    $dpt = substr(basename($file), 0, -5);
    $data = json_decode(file_get_contents($file), true);
    $newData = $data;
    foreach ($data as $k => $row) {
        if ($row) { // Corse du sud: no result
            $uri = prefixLink($row['lien']);
            $p = explode('/', $row['lien']);
            $component = array_pop($p);
            $component = CaseTool::toDog($component);
            $file = $dstDir . "/" . $component . '.html';
            $persons = getPersons($file);
            $sPerson = "";
            $c = 0;
            foreach ($persons as $person) {
                if (0 !== $c++) {
                    $sPerson .= ', ';
                }
                $sPerson .= $person["titre"] . ": " . $person['nom'];
            }

            $row['personnel'] = $sPerson;
            $rows[] = $row;

        }
    }
}


//--------------------------------------------
// CONVERTING ROWS TO EXCEL
//--------------------------------------------
$target = __DIR__ . "/sites/hopital.fr/baked/liste-hopitaux-et-personnel.xlsx";
$ret = PhpExcelTool::createExcelFileByData($target, $rows, [
    'propertiesFn' => function (PHPExcel_DocumentProperties $props) {
        $props->setCreator("LingTalfi")
            ->setTitle("Liste des hôpitaux trouvés sur hopital.fr avec personnel")
            ->setSubject("Liste des hôpitaux et personnel");
    }
]);
a($ret);






