<?php


use Bat\CaseTool;
use Core\Services\A;
use Symfony\Component\DomCrawler\Crawler;


require_once __DIR__ . "/../boot.php";
require_once __DIR__ . "/../init.php";


A::testInit();


$link = "/recherche?lat=47.902964&lng=1.9092510000000402&city=Orl%C3%A9ans&query=Orl%C3%A9anspage=1";
$uri = "https://www.masalledesport.com/rechercheApi?lat=47.902964&lng=1.9092510000000402&city=Orléans&query=Orléans&page=2";
$uri = "https://www.masalledesport.com/rechercheApi?lat=47.394144&lng=0.68484&city=tours&query=Tours&page=2";
$link = "/recherche?lat=47.39414399999999&lng=0.6848400000000083&city=Tours&query=Tourspage=1";
$link = "/recherche?lat=47.39414399999999&lng=0.6848400000000083&city=Tours&query=Tourspage=1";


$uri = "https://www.masalledesport.com/salle-de-sport,a,tours";


header('Content-type: text/plain');
function getClubs($uri)
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
    $s = "";
    foreach ($crawler as $domElement) {

        $els = $crawler->filter('.club-address');
        $els->each(function (Crawler $a) use (&$ret) {
            $clubName = $a->filter('a')->html();
            $address = $a->filter('p')->html();


            $addressString = "";
            $zip = "";
            $city = "";

            $p = explode('<br>', $address);
            if (2 === count($p)) {
                $addressString = trim($p[0]);
                $rest = $p[1];
                $p2 = preg_split('!<|>!', $rest);
                if (count($p2) > 2) {
                    $zip = $p2[0];
                    $city = array_pop($p2);
                } else {
                    $zip = "error with address";
                    $addressString = $address;
                }
            } else {
                $zip = "error with address";
                $addressString = $address;
            }


            $ret[] = [
                'name' => trim($clubName),
                'address' => trim($addressString),
                'zip' => trim($zip),
                'city' => trim($city),
            ];
        });
    }
    return $ret;
}


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


//--------------------------------------------
// START --
//--------------------------------------------
a(getLatitudeLongitudeByUri($uri));









