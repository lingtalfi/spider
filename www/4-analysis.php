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
$file = "/pathto/php/projects/spider/www/json-pages/villeparisis---1.json";
$data = json_decode(file_get_contents($file), true);
a($data);