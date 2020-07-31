<?php 
// exit();

require "settings.php";
require "functions.php";
require "../vendor/autoload.php";


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



$vk = new VK\Client\VKApiClient();

if (file_exists('token.dat')) {
  
  if (!$access_token = file_get_contents('token.dat')) {
    exit('empty token');
  }

} else {exit('empty token file');}

if (file_exists(PROC)) {
  $proc = json_decode(file_get_contents(PROC));
  if ($proc->pid != basename(__FILE__, ".php")) {
    exit('Another PROC already run');
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

  setPID(__FILE__, $offset = 0);
  exec("/opt/php/7.1/bin/php " . __FILE__ . " > /dev/null &");
  
  sleep(1);
  header('Location: index.php');
  exit();
}

$vkGoodsItems = json_decode(file_get_contents(VKSEARCHINDEX));
set_time_limit(300);

$limit = $offset + LIMIT;
// TODO: заменить временное значение на count
if ($limit > count($vkGoodsItems)) {
  $limit = count($vkGoodsItems);
}

for ($offset; $offset < $limit; $offset++) {
  $goodItem = $vkGoodsItems[$offset];
  try {
    $result = $vk->market()->delete($access_token, array(
      'owner_id'  => _ID,
      'item_id'   => $goodItem->id,
    ));

    if ($result) {
      setLog($goodItem->title . " !!DELETED");
    } 
  } catch (Exception $e) {
    setLog("[ERROR] (line):" . __LINE__ . "; (code):" . $e->getErrorCode() . "; (message):" . $e->getErrorMessage());
    
  }
  usleep(100000);
}

if ($offset < count($vkGoodsItems)) {

  setLog("[OFFSET] : " . $offset);

  sleep(2);
  setPID(__FILE__, $offset, count($vkGoodsItems));
  exec("/opt/php/7.1/bin/php " . __FILE__ . " > /dev/null &");
} else {

  setLog("END\n");
  unlink(PROC);
}
