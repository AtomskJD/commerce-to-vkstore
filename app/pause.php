<?php 
include "settings.php";
  if (file_exists(PROC)) {
    file_put_contents(PAUSE, json_encode(time()));
  }
  sleep(1);
  header('Location: index.php');
  exit();
 ?>