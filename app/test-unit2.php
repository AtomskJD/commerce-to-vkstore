<?php 
// exit();
/* 
  this is cron scheduled tasks 
  Используется SDK
  Запрос вечного токена через auth.php
*/

require "../vendor/autoload.php";
require "yaml.php";
require "settings.php";


try {
  $xml = simplexml_load_file('../../../soc_commerce.yml');
  $xi = new yaml($xml);
} catch (Exception $e) {
  exit($e);
}

echo "<pre>";
foreach ($xi->getGoods() as $good) {
  if ($good['deleted']) {
    echo $good['name'] . " is deleted \n";
  }
}

echo "</pre>";