<?php 

date_default_timezone_set('Asia/Yekaterinburg');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);
ini_set("log_errors", 1);
ini_set("error_log", dirname(__FILE__) . "/logs/php-error.log");


$ttt = __FILE__;
define("LEGACY_URL", "http://kolaskin.ru/n/");

/* Настройки приложения */
define("CLIENT_ID", 7545874);
define("CLIENT_SECRET", 'ecMkbDVkduJjm2CVdZn4');
define("REDIRECT_URL", 'https://xn-----6kcavojtahc9abe5aii1g0he.xn--p1ai/surweb/yaml2vk/app/auth.php');
define("REDIRECT_AFTER_AUTH", "https://xn-----6kcavojtahc9abe5aii1g0he.xn--p1ai/surweb/yaml2vk/app/");

/* Настройки группы*/
define("ID", 191399337);   // ID группы
define("_ID", -191399337); // ID группы минус
define("CAT", 101); // детские коляски по ВК
define("LIMIT", 5); // лимит на один поток

/* Настройки скрипта*/
define("DOMAIN_NEEDLE", 'https://xn-----6kcavojtahc9abe5aii1g0he.xn--p1ai/');
define("DOMAIN_REPLACE", '/var/www/u7837304/data/www/zapchasti-dlya-kolyasok.rf/');
define("PICTURE_NEEDLE", "/styles/600x450/public");
define("PROC", 'proc');
define("STOP", 'stop');
define("PAUSE", 'PAUSE');
define("IMGCOVER", "/img_cover/");
define("IMGGOOD", "/img_good/");
define("VKSEARCHINDEX", "local_db.json");
define("NOIMAGE", "/no_image.png");

