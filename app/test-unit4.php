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
require "functions.php";

try {
  $xml = simplexml_load_file('../../../soc_commerce.yml');
  $xi = new yaml($xml);
} catch (Exception $e) {
  exit($e);
}
$yamlGoods = $xi->getGoods();
echo "<pre>";
print_r($yamlGoods[0]);
$vkGoodsItems = json_decode(file_get_contents(VKSEARCHINDEX));
$s = 'Пруток пластикой 5х1000мм для вшивания в ткань детской коляски [016016]';
$searchKey = array_search($s, array_column($vkGoodsItems, 'title'));
// var_dump($searchKey === false);
$vk = new VK\Client\VKApiClient();
if (file_exists('token.dat')) {
  
  if (!$access_token = file_get_contents('token.dat')) {
    exit('empty token');
  }

} else {exit('empty token file');}
    try {
      $albums = $vk->market()->getAlbums($access_token, array(
        'owner_id' => _ID,
        'count'    => 100,
      ));usleep(250000);
    } catch (Exception $e) {
      setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());
    }

    $vkAlbums = array();
    foreach ($albums['items'] as $album) {
      $vkAlbums[$album['id']] = $album['title'];
    }


    foreach ($vkGoodsItems as $vkItem) {
      if (count($vkItem->albums_ids) !== 1){
        echo $vkItem->title . "\n";
        echo count($vkItem->albums_ids) . "\n";
        if (count($vkItem->albums_ids)) {
          foreach ($vkItem->albums_ids as $aid) {
            echo "\t - " . $vkAlbums[$aid] . "\n";
          }
        }
      }
    }
      var_dump(in_array("Подшипники", $vkAlbums));
echo "</pre>";