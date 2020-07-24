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

$vk = new VK\Client\VKApiClient();



if (file_exists('token.dat')) {
  
  if (!$access_token = file_get_contents('token.dat')) {
    exit('empty token');
  }

} else {exit('empty token file');}

define(ID, 191399337);   // ID группы
define(_ID, -191399337); // ID группы минус
define(CAT, 101); // детские коляски по ВК
define(LIMIT, 5); // лимит на один поток
define(PROC, 'proc');




function setPID($pid, $offset, $limit) {

  file_put_contents(PROC, json_encode(array('pid', 'offset', 'limit', 'activity')));

}

function setLog($message) {
  $log_name = "logs/" . date("d_m_y") . "-log.txt";
  $log_time = date("H:i:s");
  file_put_contents($log_name, "[" . $log_time . "] \t" . $message . "\n", FILE_APPEND);
}

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




/** 
 * Определение точки входа
 */
if (file_exists(PROC)) {
  
} else {
  setPID();
  exec("/opt/php/7.1/bin/php " . __FILE__ . " " .$offset. " > /dev/null &");
  exit();
}




/* Основной код */


if ((is_null($argc)) || ($argc == 1)) {
  if ($_GET['offset']) {
    $offset = filter_var($_GET['offset'], FILTER_SANITIZE_STRING);
    exec("/opt/php/7.1/bin/php " . __FILE__ . " " .$offset. " > /dev/null &");
    exit();
  } else {
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

    

    exec("/opt/php/7.1/bin/php " . __FILE__ . " 0 > /dev/null &");
    echo $argc;
    exit();
  }
} 



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







////////////////
// MAIN_LOOP: //
////////////////
///
///

set_time_limit(300);
for ($offset; $offset < $limit; $offset++) { 
  $good = $yamlGoods[$offset];
  
  // поиск существующих товаров (лаг минуты 3)
  try {
    $search = $vk->market()->search($access_token, array(
      'owner_id'      => _ID,
      'q'             => trim($good['name']),
    ));
  } catch (Exception $e) {
    setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());
  }
    usleep(250000);



  $gid = is_exists($search, $good['name']);
  if ($gid == false) {
  setLog($good['name'] . " *NEW");
    /* upload main image */
    // var_dump($gid);

      try {
        $photo = $vk->photos()->getMarketUploadServer($access_token, array(
            'group_id'   => ID,
            'main_photo' => 1
          ));

        $uploads = uploadFile($photo['upload_url'], trim($good['picture_path']));
        $uploads['group_id'] = ID;
        $photo = $vk->photos()->saveMarketPhoto($access_token, $uploads);
      } catch (Exception $e) {
        setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());
      }

      usleep(250000);

      try {
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
      } catch (Exception $e) {
        setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());    
      }

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
          setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());
        }
      } else {
        // echo 'create album ' . $goog['album'] . '<br>';
      }
    usleep(250000);
  } else {
    setLog($good['name']);
  }
    
}
////////////////////
// MAIN_LOOP: END //
////////////////////

if ($offest < count($yamlGoods)) {

  setLog("[OFFSET] : " . $offset);

  sleep(2);
  exec("/opt/php/7.1/bin/php " . __FILE__ . " $offset > /dev/null &");
} else {

  setLog("END\n");
}





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