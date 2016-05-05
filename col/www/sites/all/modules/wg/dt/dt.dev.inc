<?php
function dbugcli($v, $flag) {
  if($flag) echo 'FLAG: '.$flag."\n";
  if(is_string($v)) echo $v."\n";
  else var_export($v);
}

function dbug($v, $flag="", $printfriendly = TRUE) {
  global $argc;
  if(!is_null($argc)) return dbugcli($v, $flag);
  ob_start();
  var_export($v);
  $s = ob_get_clean();

  if($printfriendly) {
    $s0 = "<div style='border:1px solid #ccc;background:LightGreen;width:800px; '>";
    if(!empty($flag)) $s0 .= "<h1>$flag</h1>";
    $s1 = htmlspecialchars($s);
    $s1 = str_replace("\n", "<br/>", $s1);
    $s1 = str_replace(" ", "&nbsp;", $s1);
    $s0 .= $s1;
    $s0 .= "</div>";
    $s = $s0;
  }
  echo $s;
}

function dbug_message($v, $flag='') {
  $s = "<div style='border:1px solid #ccc;background:LightGreen '>";
  if(!empty($flag)) $s .=  "<h1>$flag</h1>";
  $s .= "<pre>";
  ob_start();
  var_dump($v);
  $s2 = ob_get_clean();
  $s .= htmlspecialchars($s2);
  $s .= "</pre>";
  $s .= "</div>";
  drupal_set_message($s);
}

function get_render_time() {
  global $render_start_time;
  $render_time = microtime(true);
  if(!$render_start_time) $render_start_time = $render_time;
  $t = round($render_time-$render_start_time, 2);
  return $t.'s';
}

function dbug_wd($o, $flag='test') {
  $x = var_export($o, true);
  watchdog($flag, $x);

}

function dbug_echo($msg) {
  trigger_error($msg, E_USER_NOTICE);
}

function dt_progress($c, $step = 100) {
  return DT::progressinfo($c, $step);
}
