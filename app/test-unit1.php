<?php 
// exit();
/* 
  this is cron scheduled tasks 
  Используется SDK
  Запрос вечного токена через auth.php
*/

require "../vendor/autoload.php";
require "yaml.php";

try {
  $xml = simplexml_load_file('../../../soc_commerce.yml');
  $xi = new yaml($xml);
} catch (Exception $e) {
  exit($e);
}

$vk = new VK\Client\VKApiClient();



if (file_exists('token.dat')) {
  
  if (!$access_token = file_get_contents('token.dat')) {
    exit('empty token');
  }

} else {exit('empty token file');}

define("ID", 191399337);   // ID группы
define("_ID", -191399337); // ID группы минус
define("CAT", 101); // детские коляски по ВК
define("LIMIT", 5); // лимит на один поток
define("PROC", 'proc');
define("IMGCOVER", "/img_cover/");
define("IMGGOOD", "/img_good/");


// $vkGoodsCount = 0; $vkGoodsOffset = 0; $vkGoodsItems = array();
// do {
//   $goods = $vk->market()->get($access_token, array(
//     'owner_id'  => _ID,
//     'count'     => 200,
//     'offset'    => $vkGoodsOffset
//   ));

//   $vkGoodsItems = array_merge($vkGoodsItems, $goods['items']);


//   $vkGoodsCount = (int)$goods['count'];
//   $vkGoodsOffset += 200;

//   usleep(250000);
// // $vkGoodsItems result
// } while ( $vkGoodsOffset <= $vkGoodsCount);




echo "<pre>";

  // file_put_contents('local_db.json', json_encode($vkGoodsItems));
  $vkGoodsItems = json_decode(file_get_contents('local_db.json'));

  print_r($vkGoodsItems[0]);

  var_dump($key = array_search('Втулка для колеса детской коляски на ось', array_column($vkGoodsItems, 'title')));
  print_r($key = array_search('Втулка для колеса детской коляски на ось 10,5мм [003017]', array_column($vkGoodsItems, 'title')));

  print_r($vkGoodsItems[$key]);

echo "</pre>";