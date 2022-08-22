<?php

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
$categories = [];
$grouped = [];

foreach ($arrOutput as $link) {
    $fullLink = str_replace('https://www.apple.com/', '', $link['loc']);
    if (substr($fullLink, -1) === '/') {
        $fullLink = substr($fullLink, 0, -1);
    }
    $category = explode("/", $fullLink);


    print_r($category[0]);
    foreach ($category as $c) {
        try {
            $dbh = new PDO('mysql:dbname=parser;host=localhost', 'root', '');
        } catch (PDOException $e) {
            die($e->getMessage());
        }


        $sth = $dbh->prepare("SELECT * FROM `categories` WHERE `name` = :name");
        $sth->execute(['name' => $c]);
        $value = $sth->fetch(PDO::FETCH_COLUMN);
        if (!$value) {
            $sth = $dbh->prepare("INSERT INTO `categories` SET `name` = :name, `parent_id` = :parent_id");
            $sth->execute(['name' => $c, 'parent_id' => '0']);
        } else {
            $sth = $dbh->prepare("INSERT INTO `categories` SET `name` = :name, `parent_id` = :parent_id");
            $sth->execute(['name' => $c, 'parent_id' => $value]);
        }

    }
}


foreach (array_unique($arr, SORT_REGULAR,) as $el) {
    $categories[] = $el;
    categoryToBD($el);
}
array_shift($categories);

foreach ($arrOutput as $link) {
    $fullLink = str_replace('https://www.apple.com/', '', $link['loc']);
    $category = strstr($fullLink, '/', true);
    foreach ($categories as $el) {
        if ($category === $el) {
            $grouped [] = [
                'category' => $el,
                'link' => $fullLink
            ];
        }
    }
}


/*foreach ($grouped as $item){
    var_dump($item);
    try {
        $dbh = new PDO('mysql:dbname=parser;host=localhost', 'root', '');
    } catch (PDOException $e) {
        die($e->getMessage());
    }
    $sth = $dbh->prepare("INSERT INTO `links` SET `id_category` = :name, `link` = :link");
    $sth->execute( ['id_category' => $item['category'], 'link' => $item['link']]);
}*/

/*var_dump($arrOutput);*/


