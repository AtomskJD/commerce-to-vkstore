<?php 
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


if (file_exists('token.dat')) {
  
  if (!$access_token = file_get_contents('token.dat')) {
    exit('empty token');
  }

} else {exit('empty token file');}

define(ID, 191399337);
define(_ID, -191399337);
define(CAT, 101);
define(LIMIT, 3);

function deleteAlbum($id) {
  $vk->market()->deleteAlbum($access_token, array(
    'owner_id' => _ID,
    'album_id' => $id,
  ));
  usleep(500000);
}

function uploadFile($url, $path) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);

    if (class_exists('\CURLFile')) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['file1' => new \CURLFile($path)]);
    } else {
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['file1' => "@".$path]);
    }

    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data, true);
}

function is_exists($search, $name) {
  $is_new = true;
  // print_r($search);

  if ($search['count'] > 0) {
    foreach ($search['items'] as $item) {
        if($item['title'] == $name) {
          return $item['id'];
        }
      }  
  }

  return false;
}



/* Основной код */

$vk = new VK\Client\VKApiClient();


/**
 * Проверка и альбомов
 * удаление вручную deleteAlbum()
 */

$albums = $vk->market()->getAlbums($access_token, array(
  'owner_id' => _ID,
  'count'    => 100,
));
$vkAlbums = array();
foreach ($albums['items'] as $album) {
  $vkTemp[] = trim($album['title']);
  $vkAlbums[$album['id']] = $album['title'];
}
// print_r($vkAlbums);

/**
 * Добавление новых альбов
 */
foreach ($xi->getCatalog() as $yamlAlbum) {
  if (!in_array($yamlAlbum, $vkAlbums)) {
    $id = $vk->market()->addAlbum($access_token, array(
      'owner_id' => _ID,
      'title'    => $yamlAlbum,
    ));
    usleep(500000);
  }
}
// 
// 
//  Такаяже хрень что и с search
// $offset = 0;
// $vkAll = array();
// do {
//   $search = $vk->market()->search($access_token, array(
//     'owner_id'      => _ID,
//     'count'         => 200,
//     'offset'        => $offset,
//   ));
//   foreach ($search['items'] as $item) {
//     $vkAll[] = $item['title'];
//   }
//   usleep(500000);
// } while ( empty($search['items']) );



/**
 * Добавдение товара
 */
// список альбомов
$vkAlbums = array();
$yamlGoods = $xi->getGoods();

$albums = $vk->market()->getAlbums($access_token, array(
  'owner_id' => _ID,
  'count'    => 100,
));
foreach ($albums['items'] as $album) {
  $vkAlbums[$album['id']] = $album['title'];
}
// TEST:
// $yamlGoods = array($yamlGoods[1]);

////////////////
// MAIN_LOOP: //
////////////////
///
///

// print_r(count($yamlGoods));
// print_r($argv);

if ((is_null($argc)) || ($argc == 1)) {
  exec("/opt/php/7.1/bin/php " . __FILE__ . " 0 > /dev/null &");
  echo $argc;
  exit();
} 

if ($argv[1] > 0) {
  $offset = $argv[1];
} else {
  $offset = 0;
}
$limit = $offset + LIMIT;
// TODO: заменить временное значение на count
if ($limit > count($yamlGoods)) {
  $limit = count($yamlGoods);
}

set_time_limit(30);
for ($offset; $offset < $limit; $offset++) { 
  $good = $yamlGoods[$offset];
  file_put_contents('ttt.txt', $good['name'] . "\n", FILE_APPEND);
}
  file_put_contents('ttt.txt', $offset . "\n", FILE_APPEND);
  sleep(2);
  exec("/opt/php/7.1/bin/php " . __FILE__ . " $offset > /dev/null &");



// echo 'test';
// for ($i=0; $i < 10; $i++) { 
//   sleep(1);
//   file_put_contents('test.txt', $i);
// }

exit();
foreach ($yamlGoods as $good) {
  set_time_limit(300);
    $search = $vk->market()->search($access_token, array(
    'owner_id'      => _ID,
    'q'             => trim($good['name']),
  ));
    usleep(250000);



  $gid = is_exists($search, $good['name']);
  if ($gid == false) {
    /* upload main image */
    var_dump($gid);
    
      $photo = $vk->photos()->getMarketUploadServer($access_token, array(
          'group_id'   => ID,
          'main_photo' => 1
        ));

      $uploads = uploadFile($photo['upload_url'], trim($good['picture_path']));
      $uploads['group_id'] = ID;
      $photo = $vk->photos()->saveMarketPhoto($access_token, $uploads);
    usleep(250000);


      $gid = $vk->market()->add($access_token, array( 
            'owner_id'      => _ID,
            'name'          => $good['name'],
            'description'   => filter_var ($good['description'], FILTER_SANITIZE_STRING),
            'category_id'   => CAT,
            'price'         => $good['price'],
            'deleted'       => $good['deleted'],
            'main_photo_id' => $photo[0]['id'],
            'url'           => $good['url'],
          ));                                         

          $gid = $gid['market_item_id'];
    /*  это после  */
      if ($vkAlbumId = array_search($good['album'], $vkAlbums)) {
       
        try {
          $result = $vk->market()->addToAlbum($access_token, array(
            'owner_id'      => _ID,
            'item_id'       => $gid,
            'album_ids'     => $vkAlbumId,
          ));
        // var_dump($result);
        } catch (Exception $e) {
          if ($e->getErrorCode() == 1404) {
            echo  "уже там";
          }
          echo $e->getErrorCode();
        }
      } else {
        // echo 'create album ' . $goog['album'] . '<br>';
      }
    usleep(250000);
  }
    
    // var_dump($gid);
      // usleep(500000);
    
}
////////////////////
// MAIN_LOOP: END //
////////////////////





?>
<pre>
  <?php 



    //////////////////
    // upload image //
    //////////////////
 // $photo = $vk->photos()->getMarketUploadServer($access_token, array(
 //      'group_id'   => ID,
 //      'main_photo' => 1

 //    ));

 //    $uploads = uploadFile($photo['upload_url'], "/var/www/u7837304/data/www/zapchasti-dlya-kolyasok.rf/sites/default/files/styles/600x450/public/field/image/001042-1.jpg");
 //    $uploads['group_id'] = ID;
    
 //    $photo = $vk->photos()->saveMarketPhoto($access_token, $uploads);
 //    print_r($photo);

 //    $good = $vk->market()->add($access_token, array(
 //      'owner_id'   => _ID,
 //      'name' => 'tesx',
 //      'description' => '123456789012',
 //      'main_photo_id' => $photo[0]['id'],
 //      'category_id'   => 101,
 //      'price'         => 10.00,
 //    ));
 //    print_r($good);
   ?>
</pre>