<?php
function _dt_switch_to_maintenance($msg='maintenance mode') {
  $msg0 = &drupal_static('MAINTENANCE_MODE_MSG');
  drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  $msg0 = variable_get('maintenance_mode_message', 'maintenance mode');
  variable_set('maintenance_mode_message', $msg);
  variable_set('maintenance_mode', '1');
}

function _dt_switch_to_online() {
  $msg0 = &drupal_static('MAINTENANCE_MODE_MSG');
  drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  variable_set('maintenance_mode_message', $msg0);
  variable_set('maintenance_mode', '0');
}

function dtf_xlsx2csv($excel, $csv, $reset = false) {
  if($reset || !file_exists($csv)) {
    if(file_exists($csv)) file_unmanaged_delete($csv);
    require_once 'dt.exceltool.class.inc';
    DTExcelTool::init();
    DTExcelTool::xlsx2csv(DH::realpath($excel), DH::realpath($csv));
  }
}

function dtf_csv2xlsx($csv, $excel, $reset = false) {
  if($reset || !file_exists($excel)) {
    if(file_exists($excel)) file_unmanaged_delete($excel);
    require_once 'dt.exceltool.class.inc';
    DTExcelTool::init();
    DTExcelTool::csv2xlsx(DH::realpath($csv), DH::realpath($excel));
  }
}

function dtf_process_csv($vars, $csv, $cb= null, $cb_field = null) {
  $rownum = 0;
  $successed = array();

  $fields = array();

  if (($handle = fopen($csv, "r")) !== FALSE) {
    fseek($handle, 0);
    while (($data = fgetcsv($handle)) !== FALSE) {
      $rownum++;

      $num = count($data);
      if(!$fields) {
        for ($c=0; $c < $num; $c++) {
          $d = $data[$c];
          $d = trim($d);
          if($d!='' && ($d[0] != '*')) $fields[$c] = $d;
        }
        $args = array($fields, $vars);
        if($cb_field) {
          $fields = call_user_func_array($cb_field, $args);
        }
        continue;
      } else {
        $row = array();
        for ($c=0; $c < $num; $c++) {
          $d = $data[$c];
          $d = trim($d);
          if(array_key_exists($c, $fields) && ($fields[$c] != '')) {
            $row[$fields[$c]] = $d;
          }
        }
        $args = array($row, $fields, $vars);

        if($r = call_user_func_array($cb['row'], $args)) {
          $successed[] = $r;
        }
      }
    }
    fclose($handle);
    if($cb['end']) {
      $args = array();
      call_user_func_array($cb['end'], $args);
    }
    echo $rownum."\n";
  }
  return $successed;
}

function dtf_module_weight_more($modl, $modh) {
  $weight = db_select('system', 's')
            ->fields('s', array('weight'))
            ->condition('name', $modl, '=')
            ->execute()
            ->fetchField();
  db_update('system')
  ->fields(array('weight' => $weight +1))
  ->condition('name', $modh, '=')
  ->execute();
}

function _dt_hook_invoke($hook) {
  $modules = module_implements($hook);
  foreach ($modules as $key =>  $module) {
    $function = $module . '_' . $hook;
    if (!function_exists($function)) {
      unset($modules[$key]);
    }
  }
  return $modules;
}
