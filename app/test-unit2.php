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

echo "<pre>";
phpinfo();
print_r(stream_context_get_default());
print_r(stream_get_transports());
print_r(stream_get_wrappers());
print_r(stream_get_filters());
// try {
//   $xml = simplexml_load_file('../../../soc_commerce.yml');
//   $xi = new yaml($xml);
// } catch (Exception $e) {
//   exit($e);
// }

// echo "<pre>";
// foreach ($xi->getGoods() as $good) {
//   print_r($good);
// }

echo "</pre>";