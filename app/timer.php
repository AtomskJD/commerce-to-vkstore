<?php
require "settings.php";
require "functions.php";

if (file_exists(PROC)) {
  $proc = json_decode(file_get_contents(PROC));
  $comm = dirname(__FILE__) . "/" .$proc->pid . ".php";
  if (time() - strtotime($proc->time) > 5 * 60) {

    exec("/opt/php/7.1/bin/php " . $comm . " > /dev/null &");
    setLog("[RETURN by timer] : offset " . $proc->offset);
    exit();
  }
}