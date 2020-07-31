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

$vk = new VK\Client\VKApiClient();

echo "string";

if (file_exists('token.dat')) {
  
  if (!$access_token = file_get_contents('token.dat')) {
    exit('empty token');
  }

} else {exit('empty token file');}



$vkGoodsCount = 0; $vkGoodsOffset = 0; $vkGoodsItems = array();
do {
  $goods = $vk->market()->get($access_token, array(
    'owner_id'  => _ID,
    'count'     => 200,
    'offset'    => $vkGoodsOffset
  ));

  $vkGoodsItems = array_merge($vkGoodsItems, $goods['items']);


  $vkGoodsCount = (int)$goods['count'];
  $vkGoodsOffset += 200;

  usleep(250000);
// $vkGoodsItems result
} while ( $vkGoodsOffset <= $vkGoodsCount);




echo "<pre>";

  file_put_contents('local_db.json', json_encode($vkGoodsItems));
  $vkGoodsItems = json_decode(file_get_contents('local_db.json'));

  print_r($vkGoodsCount);
  print_r($vkGoodsItems[0]);

  $good = $vk->market()->delete($access_token, array(
          'owner_id'    => _ID,
          'item_id'     => 4461191,
        ));

  // print_r($key = array_search('test test', array_column($vkGoodsItems, 'title')));

  // print_r($item = $vkGoodsItems[$key]);
  // print_r($vkItem['price'] = $item->price->amount);



echo "</pre>";