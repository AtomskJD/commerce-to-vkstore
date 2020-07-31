<?php 

date_default_timezone_set('Asia/Yekaterinburg');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ERROR);
ini_set("log_errors", 1);
ini_set("error_log", dirname(__FILE__) . "/logs/php-error.log");


$ttt = __FILE__;

define("ID", 191399337);   // ID группы
define("_ID", -191399337); // ID группы минус
define("CAT", 101); // детские коляски по ВК
define("LIMIT", 5); // лимит на один поток
define("PROC", 'proc');
define("STOP", 'stop');
define("PAUSE", 'PAUSE');
define("IMGCOVER", "/img_cover/");
define("IMGGOOD", "/img_good/");
define("VKSEARCHINDEX", "local_db.json");
define("NOIMAGE", "/no_image.png");

