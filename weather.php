<?php
include ('simple_html_dom.php');

$html = file_get_html('https://www.gismeteo.by/');

$div = $html->find('div[class="current-weather"]', 0)->find('div[class="temperature"]', 0);
$temp = $div->find('span[class="unit unit_temperature_c"]', 0)->plaintext;


echo $temp;