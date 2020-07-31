<?php 
date_default_timezone_set('Asia/Yekaterinburg');
echo date('r');
echo ini_get('date.timezone');

include "settings.php";

function renderAlbumCover($source) {
  $cp_path = dirname(__FILE__) . IMGCOVER;
  if (file_exists($source)) {
  } else {
    $source = dirname(__FILE__) . NOIMAGE;
  }

  $absolutePath = $cp_path . basename($source);

  echo "<br>";
  echo "cp $source $absolutePath";

  exec("cp $source $absolutePath");
  exec("convert $absolutePath -resize 1280x720 -background white -gravity center -extent 1280x720 -gravity center logo.png -compose dissolve -composite $absolutePath");

    return $absolutePath;
}
$s = '/var/www/u7837304/data/www/zapchasti-dlya-kolyasok.rf/sites/default/files/field/image/1.jpg';
echo "<br>";
renderAlbumCover($s);
echo "<br>";