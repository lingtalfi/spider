<?php


use Bat\CaseTool;
use Core\Services\A;
use Symfony\Component\DomCrawler\Crawler;


require_once __DIR__ . "/../boot.php";
require_once __DIR__ . "/../init.php";


A::testInit();


//--------------------------------------------
// SCRIPT
//--------------------------------------------
header('Content-type: text/plain');
$file = __DIR__ . "/city2latitudes/city2latitudes.txt";
$city2Lats = file($file, \FILE_IGNORE_NEW_LINES);


function getClubsByJsonUri($uri)
{

    $ret = [];
    $fileName = __DIR__ . "/files/" . CaseTool::toDog($uri) . ".json";
    if (false === file_exists($fileName)) {
        $json = file_get_contents($uri);
        file_put_contents($fileName, $json);
    } else {
        $json = file_get_contents($fileName);
    }


    $all = json_decode($json);
    return $all;

}


function getFileName($city, $page)
{
    $city = CaseTool::toDog($city);
    return __DIR__ . "/json-pages/$city---$page.json";
}


function getContentByUri($uri)
{
    $result = (array)getClubsByJsonUri($uri);
    $array = json_decode(json_encode($result), true);
    return $array;
}

foreach ($city2Lats as $line) {

    $page = 1;
    $p = explode(':', $line);
    list($city, $lat, $lon) = $p;
    $target = getFileName($city, $page);

    if (!file_exists($target)) {
        $uri = "https://www.masalledesport.com/rechercheApi?lat=$lat&lng=$lon&city=$city&query=$city&page=" . $page;
        $data = getContentByUri($uri);
        file_put_contents($target, json_encode($data));
    } else {
        $data = json_decode(file_get_contents($target), true);
    }

    if (array_key_exists('pagination', $data)) {
        $total = $data['pagination']['pageCount'];
        if ($total > 1) {
            for ($page = 2; $page <= $total; $page++) {

                $target = getFileName($city, $page);
                if (!file_exists($target)) {
                    $uri = "https://www.masalledesport.com/rechercheApi?lat=$lat&lng=$lon&city=$city&query=$city&page=" . $page;
                    $data = getContentByUri($uri);
                    file_put_contents($target, json_encode($data));
                }
            }
        }
    }
}

