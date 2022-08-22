<?php

try {
    $dbh = new PDO('mysql:dbname=parser;host=localhost', 'root', '');
} catch (PDOException $e) {
    die($e->getMessage());
}

$url = "https://www.apple.com/sitemap.xml";


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$content = curl_exec($ch);
$xml = simplexml_load_string($content);

$objJsonDocument = json_encode($xml);
$arrOutput = json_decode($objJsonDocument, TRUE);
$arrOutput = $arrOutput['url'];

$arr = [];

foreach ($arrOutput as $link) {
    $fullLink = str_replace('https://www.apple.com/', '', $link['loc']);
    if (substr($fullLink, -1) === '/') {
        $fullLink = substr($fullLink, 0, -1);
    }
    $category = explode("/", $fullLink);
    if (!in_array($category[0], $arr)) {
        $arr [] = $category[0];
        $sth = $dbh->prepare("INSERT INTO `categories` SET `name` = :name, `parent_id` = :parent_id");
        $sth->execute(['name' => $category[0], 'parent_id' => '0']);
    }
    $sth = $dbh->prepare("SELECT * FROM `categories` WHERE `name` = :name");
    $sth->execute(['name' => $category[0]]);
    $value = $sth->fetch(PDO::FETCH_COLUMN);

    $count = count($category);


    for ($i = 1; $i <= $count; $i++) {

        print_r('|' . $count . ' ');
        print_r($i . '|');
        if (count($category) < $i) {
            if (!in_array($category[$i], $arr)) {
                $arr [] = $category[$i];
                $sth = $dbh->prepare("INSERT INTO `categories` SET `name` = :name, `parent_id` = :parent_id");
                $sth->execute(['name' => $category[$i], 'parent_id' => $value]);
            }
        }
    }
}