<?php 
/* 
  this is cron scheduled tasks 
  Используется SDK
  Запрос вечного токена через auth.php
*/

require "../vendor/autoload.php";
require "yaml.php";
require "settings.php";
require "functions.php";

if (file_exists(PAUSE)) {
  unlink(PAUSE);
  exit();
}

if (file_exists(STOP)) {
  unlink(STOP);
  unlink(PROC);
  unlink(VKSEARCHINDEX);
  exit();
}

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
  if ($search['count'] > 0) {
    foreach ($search['items'] as $item) {
        if($item['title'] == $name) {
          return $item['id'];
        }
      }  
  }

  return false;
}


function renderAlbumCover($source) {
  $cp_path = dirname(__FILE__) . IMGCOVER;
  if (file_exists($source)) {
    $absolutePath = $cp_path . basename($source);
  } else {
    $source = dirname(__FILE__) . NOIMAGE;
    $absolutePath = $cp_path . basename($source);
  }

  exec("cp $source $absolutePath");
  exec("convert $absolutePath -resize 1280x720 -background white -gravity center -extent 1280x720 -gravity center logo.png -compose dissolve -composite $absolutePath");

    return $absolutePath;
}

function renderGoodCover($source) {
  $cp_path = dirname(__FILE__) . IMGGOOD;

  if (file_exists($source)) {
    $absolutePath = $cp_path . basename($source);
  } else {
    $source = dirname(__FILE__) . NOIMAGE;
    $absolutePath = $cp_path . basename($source);
  }

  exec("cp $source $absolutePath");
  exec("convert $absolutePath -resize 400x400 -background white -gravity center -extent 400x400 -gravity center logo.png -compose dissolve -composite $absolutePath");

    return $absolutePath;
}


/** 
 * Определение точки входа
 */
if (file_exists(PROC)) {
  $proc = json_decode(file_get_contents(PROC));
  if ($proc->pid != basename(__FILE__, ".php")) {
    error_log('Another PROC already run');
    exit();
  }
  $offset = $proc->offset;
} else {
  // если ПРОЦесса нет первый заход проверка прав
  if ((!isset($_GET['key'])) || ($_GET['key'] != 'Iddqd2011')) {
    error_log('Попытка входа без ключа ' . basename(__FILE__));
    exit('Не указан ключ');
  }
  /* Локальная база для поиска товаров local DB */
  $vkGoodsCount = 0; $vkGoodsOffset = 0; $vkGoodsItems = array();
  do {
    try {
      $goods = $vk->market()->get($access_token, array(
        'owner_id'  => _ID,
        'count'     => 200,
        'offset'    => $vkGoodsOffset
      ));
    } catch (Exception $e) {
      setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());
    }

    $vkGoodsItems = array_merge($vkGoodsItems, $goods['items']);
    $vkGoodsCount = (int)$goods['count'];
    $vkGoodsOffset += 200;
    usleep(100000);
  // $vkGoodsItems result
  } while ( $vkGoodsOffset <= $vkGoodsCount);
  file_put_contents(VKSEARCHINDEX, json_encode($vkGoodsItems));



    /**
     * Проверка и альбомов
     * удаление вручную deleteAlbum()
     */
    try {
      $albums = $vk->market()->getAlbums($access_token, array(
        'owner_id' => _ID,
        'count'    => 100,
      ));
    } catch (Exception $e) {
      setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());
    }

    $vkAlbums = array();
    foreach ($albums['items'] as $album) {
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

  setPID(__FILE__, $offset = 0);
  exec("/opt/php/7.1/bin/php " . __FILE__ . " > /dev/null &");
  sleep(1);
  header('Location: index.php');
  exit();
}




/* Основной код */



/**
 * Добавдение товара
 */
// список альбомов
$vkAlbums = array();
$vkAlbumsNoPhoto = array();

$yamlGoods = $xi->getGoods();
try {
  $albums = $vk->market()->getAlbums($access_token, array(
    'owner_id' => _ID,
    'count'    => 100,
  ));
} catch (Exception $e) {
  setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());
}
foreach ($albums['items'] as $album) {
  $vkAlbums[$album['id']] = $album['title'];
  if (!isset($album['photo'])) {
    $vkAlbumsNoPhoto[$album['id']] = $album['title'];
  }
}





$limit = $offset + LIMIT;
// TODO: заменить временное значение на count
if ($limit > count($yamlGoods)) {
  $limit = count($yamlGoods);
}


$vkGoodsItems = json_decode(file_get_contents(VKSEARCHINDEX));


////////////////
// MAIN_LOOP: //
////////////////
///
///

set_time_limit(300);
for ($offset; $offset < $limit; $offset++) { 
  $good = $yamlGoods[$offset];
  
  // поиск существующих товаров в локальной базе
  $searchKey = array_search($good['name'], array_column($vkGoodsItems, 'title'));
  // $vkGoodsItems[$searchKey]

  /* Создание нового товара если он не существовал */
  if ($searchKey == false) {
    // Проверка доступности товара
    if ($good['deleted']) {
      setLog($good['name'] . " *NOT_AVAILABLE");
      # code...
    } else {
      setLog($good['name'] . " *NEW");
      /* upload main image */
      // var_dump($gid);

      try {
        $photo = $vk->photos()->getMarketUploadServer($access_token, array(
            'group_id'   => ID,
            'main_photo' => 1
          ));

        $uploads = uploadFile($photo['upload_url'], renderGoodCover($good['picture_path']));
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
              'description'   => $good['description'],
              'category_id'   => CAT,
              'price'         => $good['price'],
              'deleted'       => (int)$good['deleted'],
              'main_photo_id' => $photo[0]['id'],
              'url'           => $good['url'],
          ));                                         
      $gid = $gid['market_item_id'];
      } catch (Exception $e) {
        setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());    
      }

      /*  Добавление в альбом  */
      if ($vkAlbumId = array_search($good['album'], $vkAlbums)) {
       
        try {
          $result = $vk->market()->addToAlbum($access_token, array(
            'owner_id'      => _ID,
            'item_id'       => $gid,
            'album_ids'     => $vkAlbumId,
          ));
        } catch (Exception $e) {
          setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());
        }
      } else {
        try {
          $id = $vk->market()->addAlbum($access_token, array(
            'owner_id' => _ID,
            'title'    => $good['album'],
          ));
          usleep(500000);
        } catch (Exception $e) {
          setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());
        }
      }

    usleep(250000);
    }
  } 
  /* Обновление существующего товара */
  else {
    // getById
    $vkItem   = array();
    $yamlItem = array();

    $item = $vkGoodsItems[$searchKey];
    $gid = $item->id;
    $vkItem['title'] = $item->title;
    $vkItem['price'] = $item->price->amount;
    $vkItem['description'] = str_replace(array("\n", " ", "\t"), "",$item->description);
    if ($item->availability == 0) {
      $vkItem['deleted'] = 0;
    } else {
      $vkItem['deleted'] = 1;
    }

    $yamlItem['title']   = $good['name'];
    $yamlItem['deleted'] = $good['deleted'];
    $yamlItem['price']   = $good['price']*100;
    $yamlItem['description'] = str_replace(array("\n", " ", "\t"), "",$good['description']);

    // если изменения - это НЕ ДОСТУПНО - удаляем
    if ($yamlItem['deleted']) {
      try {
        $delete = $vk->market()->delete($access_token, array(
          'owner_id'    => _ID,
          'item_id'     => 4461191,
        ));        
      } catch (Exception $e) {
          setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());
      }
    } else {

      // внести изменения если значения не совпадают
      if (($vkItem['title'] != $yamlItem['title']) ||
          ($vkItem['price'] != $yamlItem['price']) ||
          ($vkItem['description'] != $yamlItem['description'])) {
        setLog($good['name'] . " *UPDATE");
        usleep(250000);
        try {
          $good = $vk->market()->edit($access_token, array(
            'owner_id'    => _ID,
            'item_id'     => $gid,
            'name'        => $good['name'],
            'price'       => (float)$good['price'],
            'description' => $good['description'],
          ));
        } catch (Exception $e) {
            setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());    
        }
      } else {
        setLog($good['name']);
      }
    }

/*  << ТЕСТ что не так
    if ($vkItem['title'] != $yamlItem['title']) {
      setLog($vkItem['title']);
      setLog($yamlItem['title']);
    }
    if ($vkItem['price'] != $yamlItem['price']) {
      setLog($vkItem['price']);
      setLog($yamlItem['price']);
    }
    if ($vkItem['description'] != $yamlItem['description']) {
      setLog($vkItem['description']);
      setLog($yamlItem['description']);
    }

*/
  }

  /* Добавление изображения к альбому */
  if ($vkAlbumId = array_search($good['album'], $vkAlbumsNoPhoto)) {
    setLog($good['album'] . " *NEW IMAGE");
    try {
      $albumPhoto = $vk->photos()->getMarketUploadServer($access_token, array(
          'group_id'   => ID
        ));
      $albomPic = renderAlbumCover($good['picture_path']);
      sleep(1);
      $uploads = uploadFile($albumPhoto['upload_url'], $albomPic);
      $uploads['group_id'] = ID;
      $albumPhoto = $vk->photos()->saveMarketPhoto($access_token, $uploads);
    } catch (Exception $e) {
      setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());
    }

    usleep(250000);

    try {
      $aid = $vk->market()->editAlbum($access_token, array( 
            'owner_id'    => _ID,
            'album_id'    => $vkAlbumId,
            'title'       => $good['album'],
            'photo_id'    => $albumPhoto[0]['id'],
            'main_album'  => 0,
        ));                                         
    } catch (Exception $e) {
      setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());    
    }


    unset($vkAlbumsNoPhoto[$vkAlbumId]);
  }
    
}
////////////////////
// MAIN_LOOP: END //
////////////////////

if ($offset < count($yamlGoods)) {

  setLog("[OFFSET] : " . $offset);

  sleep(2);
  setPID(__FILE__, $offset, count($yamlGoods));
  exec("/opt/php/7.1/bin/php " . __FILE__ . " > /dev/null &");
} else {

  setLog("END\n");
  unlink(PROC);
}