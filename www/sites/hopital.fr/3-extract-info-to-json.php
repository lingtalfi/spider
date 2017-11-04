<?php


use Core\Services\A;
use DirScanner\YorgDirScannerTool;
use Symfony\Component\DomCrawler\Crawler;


require_once __DIR__ . "/../boot.php";
require_once __DIR__ . "/../init.php";


A::testInit();

//--------------------------------------------
// EXTRACT INFO FROM departements files
//--------------------------------------------
/**
 * - nom
 * - latitude
 * - longitude
 * - lien
 * - indicateur_qualite
 * - avis_patients
 * - nb_avis_patients
 * - capacite
 * - type_structure
 * - depend_de
 * - telephone
 * - site_internet
 * - adresse
 *
 */
$dstDir = "/pathto/php/projects/spider/www/sites/hopital.fr/raw-html/departements_no_script";
$files = YorgDirScannerTool::getFilesWithExtension($dstDir, "html");


function getAttribute($text)
{
    $p = explode(":", $text, 2);
    return trim(array_pop($p));
}


//--------------------------------------------
// COLLECT DATA
//--------------------------------------------
foreach ($files as $file) {


    $hopitaux = [];
    $html = file_get_contents($file);


    $crawler = new Crawler($html);

    $cpt = 0;
    $crawler->filter("#section_31")->filter('article')->each(function (Crawler $c) use (&$hopitaux, &$cpt) {
        $cpt++;
        $latitude = $c->attr("data-lat");
        $longitude = $c->attr("data-lng");
        $link = $c->filter('h3 a')->attr("href");
        $name = $c->filter('h3 a')->text();
        $indicateurQualiteStars = $c->filter('.list_rating li')->eq(0)->filter('[itemprop="ratingValue"]')->text();
        $indicateurQualite = strlen($indicateurQualiteStars);
        $avisPatientsEl = $c->filter('.list_rating li')->eq(1)->filter('[itemprop="ratingValue"]');

        if ($avisPatientsEl->count() > 0) {

            $avisPatientsStars = $c->filter('.list_rating li')->eq(1)->filter('[itemprop="ratingValue"]')->text();
            $avisPatients = strlen($avisPatientsStars);
            $nbAvisPatients = $c->filter('.list_rating li')->eq(1)->filter('[itemprop="ratingCount"]')->text();
        } else {
            $avisPatients = 0;
            $nbAvisPatients = 0;
        }


        $liste1 = $c->filter('.list_attributes')->eq(0);
        $capacite = getAttribute($liste1->filter("li")->eq(0)->text());
        $capacite = (int)explode(' ', $capacite)[0];
        $typeStructure = getAttribute($liste1->filter("li")->eq(1)->text());
        $dependDeUri = $liste1->filter("li")->eq(2)->filter("a")->attr('href');


        $liste2 = $c->filter('.list_attributes')->eq(1);
        $telephone = getAttribute($liste2->filter("li")->eq(0)->text());
        $siteInternetUri = $liste2->filter("li")->eq(1)->filter("a")->attr('href');
        $adresse = getAttribute($liste2->filter("li")->eq(2)->text());

        $hopital = [
            'nom' => $name,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'lien' => $link,
            'indicateur_qualite' => $indicateurQualite,
            'avis_patients' => $avisPatients,
            'nb_avis_patients' => $nbAvisPatients,
            'capacite' => $capacite,
            'type_structure' => $typeStructure,
            'depend_de' => $dependDeUri,
            'telephone' => $telephone,
            'site_internet' => $siteInternetUri,
            'adresse' => $adresse,
        ];
        $hopitaux[] = $hopital;
    });

    $dstFile = str_replace([
        'raw-html/departements_no_script',
        '.html',
    ], [
        'json',
        '.json',
    ], $file);

    $content = json_encode($hopitaux);
    file_put_contents($dstFile, $content);
    a($dstFile);
}
