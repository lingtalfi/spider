<?php


use Bat\CaseTool;
use Core\Services\A;
use Symfony\Component\DomCrawler\Crawler;


require_once __DIR__ . "/../boot.php";
require_once __DIR__ . "/../init.php";


A::testInit();


$file = __DIR__ . "/city/cities.txt";


//--------------------------------------------
// SCRIPT
//--------------------------------------------
header('Content-type: text/plain');
$cities = file($file, \FILE_IGNORE_NEW_LINES);
$target = __DIR__ . "/city2latitudes/city2latitudes.txt";

function getLatitudeLongitudeByUri($uri)
{
    $ret = [];
    $fileName = __DIR__ . "/files/" . CaseTool::toDog($uri) . ".html";
    if (false === file_exists($fileName)) {
        $html = file_get_contents($uri);
        file_put_contents($fileName, $html);
    } else {
        $html = file_get_contents($fileName);
    }


    $crawler = new Crawler($html);
    $done = false;
    $crawler->filter("script")->each(function (Crawler $c) use (&$done, &$ret) {
        if (false === $done) {

            if (0 === strpos($c->html(), 'window.__data__')) {
                $html = $c->html();
                if (preg_match('!"lat":.*"baseUrl"!U', $html, $match)) {
                    $p = explode(',', $match[0]);
                    if (3 === count($p)) {
                        $latP = explode(':', $p[0]);
                        $lonP = explode(':', $p[1]);
                        $lat = array_pop($latP);
                        $lon = array_pop($lonP);
                        $done = true;
                        $ret = [$lat, $lon];
                    }
                }
            }
        }
    });

    return $ret;
}


$allLatitudes = [];
foreach ($cities as $city) {
    $formattedCityName = str_replace(' ', '-', $city);
    $uri = "https://www.masalledesport.com/salle-de-sport,a," . $formattedCityName;
    $allLatitudes[$formattedCityName] = getLatitudeLongitudeByUri($uri);
    a($formattedCityName);
}

$s = '';
foreach($allLatitudes as $city => $lats){
    $s .= $city . ":" . $lats[0] . ':' . $lats[1] . PHP_EOL;
}

file_put_contents($target, $s);
