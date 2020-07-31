<?php 
function setPID($file, $offset, $endpoint = 0) {
  $pid = basename($file, ".php");
  $time = date("r");

  file_put_contents(PROC, json_encode(array('pid' => $pid, 'offset' => $offset, 'endpoint' => $endpoint, 'time' => $time)));

}


function setLog($message) {
  $log_name = "logs/" . date("d_m_y") . "-log.txt";
  $log_time = date("H:i:s");
  file_put_contents($log_name, "[" . $log_time . "] \t" . $message . "\n", FILE_APPEND);
}
