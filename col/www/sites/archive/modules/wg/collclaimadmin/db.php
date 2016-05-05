<?php

define('CLAIM_DB',     'db_claim');    // remenber to modify the settings.php of this site
define('CLAIM_AUTHOR', 'claim_author');
define('CLAIM_COLL',   'claim_coll');

function stringify_query( $query ) {
  $s = preg_replace('/\}|\{/', '', $query->__toString());
  $a = $query->arguments();
  foreach ( $a as $key => $val ) {
    $a[$key] = '\'' . $val . '\'';
  }
  return strtr($s, $a);
}

/*
 * reference:
 *   https://api.drupal.org/api/drupal/includes!database!database.inc/function/db_update/7
 */
function switch_a_claim($claim_id, $vcond) {
  db_set_active(CLAIM_DB);

  if(!db_table_exists(CLAIM_COLL)) { db_set_active(); return false; }

  $num_updated = db_update(CLAIM_COLL)
  ->fields(array(
    'verified' => $vcond
  ))
  ->condition('id', $claim_id, '=')
  ->execute();

  db_set_active();

  //dbug_message($num_updated . ' claim have been updated');
  return true;
}

function unverify_a_claim($claim_id) { return switch_a_claim($claim_id, 0); }

function verify_a_claim($claim_id)   { return switch_a_claim($claim_id, 1); }

/*
 * reference:
 *   1. https://api.drupal.org/api/drupal/includes!database!database.inc/function/db_select/7
 *
 * $cond = 0, return unverified claims
         = 1, return verified claims
         = null, any or undefined, return all claims
 */
function get_claims($cond) {
  db_set_active(CLAIM_DB);

  if(!db_table_exists(CLAIM_COLL)) { db_set_active(); return array(); }

  $query = db_select(CLAIM_COLL, 'c');

  $query->join(CLAIM_AUTHOR, 'a', 'c.uid = a.uid'); //JOIN

  //$query->groupBy('c.uid');//GROUP BY user ID

  $query->fields('c', array('id', 'cid','hd', 'copyright', 'display', 'note', 'created', 'verified', 'openmosaic')) //SELECT the fields from CLAIM_COLL
        ->fields('a',array('real_name', 'email', 'phone', 'address', '4digitid'))                       //SELECT the fields from CLAIM_AUTHOR
        ->orderBy('cid', 'DESC');
        //->orderBy('created', 'DESC'); //ORDER BY created
        //->range(0,2); //LIMIT to 2 records

  switch($cond) {
    case 0:
      $query->condition('verified', 0, '=');
      break;
    case 1:
      $query->condition('verified', 1, '=');
      break;
    default:
  }

  $result = $query->execute();
  $claims = array();
  while($record = $result->fetchAssoc()) { $claims[] = $record; }

  db_set_active();

  return $claims;
}

function get_sortable_claims($cond, $header) {

  db_set_active(CLAIM_DB);

  //if(!db_table_exists(CLAIM_DB . "." . CLAIM_COLL)) { return array(); }
  if(!db_table_exists(CLAIM_COLL)) { return array(); }

  //$query = db_select(CLAIM_DB . "." . CLAIM_COLL, 'c');
  $query = db_select(CLAIM_COLL, 'c');

  //$query->join(CLAIM_DB . "." . CLAIM_AUTHOR, 'a', 'c.uid = a.uid'); //JOIN
  $query->join(CLAIM_AUTHOR, 'a', 'c.uid = a.uid'); //JOIN

  //$query->groupBy('c.uid');//GROUP BY user ID

  $query->fields('c', array('id', 'cid','hd', 'copyright', 'display', 'note', 'created', 'verified', 'openmosaic')) //SELECT the fields from CLAIM_COLL
        ->fields('a',array('real_name', 'email', 'phone', 'address', '4digitid'))                     //SELECT the fields from CLAIM_AUTHOR
        //->orderBy('cid', 'DESC');
        ->extend('TableSort')
        ->orderByHeader($header);
        //->orderBy('created', 'DESC'); //ORDER BY created
        //->range(0,2); //LIMIT to 2 records

  switch($cond) {
    case 0:
      $query->condition('verified', 0, '=');
      break;
    case 1:
      $query->condition('verified', 1, '=');
      break;
    default:
  }

  $result = $query->execute();
  $claims = array();
  while($record = $result->fetchAssoc()) { $claims[] = $record; }

  db_set_active();

  return $claims;
}



function get_verified_claims_of_a_collection($cid) {
  db_set_active(CLAIM_DB);

  if(!db_table_exists(CLAIM_COLL)) { db_set_active(); return array(); }

  $query = db_select(CLAIM_COLL, 'c');
  $query->join(CLAIM_AUTHOR, 'a', 'c.uid = a.uid'); //JOIN

  $query->fields('c', array('id', 'cid', 'hd', 'copyright', 'display', 'note', 'created', 'verified', 'openmosaic')) //SELECT the fields from CLAIM_COLL
        ->fields('a',array('real_name', 'email', 'phone', 'address', '4digitid'))
        ->condition(db_and()
          ->condition('cid', $cid, '=')
          ->condition('verified', 1, '=')
          );

  $result = $query->execute();
  $claims = array();
  while($record = $result->fetchAssoc()) { $claims[] = $record; }

  db_set_active();

  return $claims;
}


/*
 * return True/False
 *
 *<?php
 *   $result = db_select('table_name', 'table_alias')
 *           ->fields('table_alias')
 *           ->execute();
 *   $num_of_results = $result->rowCount();
 *?>
 */
function has_verified_claims_of_a_collection($cid) {
  db_set_active(CLAIM_DB);

  if(!db_table_exists(CLAIM_COLL)) { db_set_active(); return array(); }

  $query = db_select(CLAIM_COLL, 'c');

  $query->fields('c', array('uid', 'cid', 'hd')) //SELECT the fields from CLAIM_COLL
        ->condition(db_and()
          ->condition('cid', $cid, '=')
          ->condition('verified', 1, '=')
          );

  //drupal_set_message(sprintf($query, $arg1, $arg2), "status");
  //drupal_set_message(stringify_query($query));

  $result = $query->execute();
  $num_of_results = $result->rowCount();

  //drupal_set_message('has_verified_claims_of_a_collection(' . $cid . ') :'. $num_of_results);

  db_set_active();

  //return ($num_of_results > 0) ? true : false;
  return $num_of_results;
}

/*
 * vcond : the verified condition, 0 -> unverified, 1 -> verified
 */
function get_claim_by_id_and_vcond($claim_id, $vcond) {
  db_set_active(CLAIM_DB);
  if(!db_table_exists(CLAIM_COLL)) { db_set_active(); return array(); }

  $query = db_select(CLAIM_COLL, 'c');
  $query->join(CLAIM_AUTHOR, 'a', 'c.uid = a.uid'); //JOIN

  $query->fields('c', array('id', 'cid', 'hd', 'copyright', 'display', 'note', 'created', 'verified', 'openmosaic')) //SELECT the fields from CLAIM_COLL
        ->fields('a',array('real_name', 'email', 'phone', 'address', '4digitid'))
        ->condition(db_and()
          ->condition('id', $claim_id, '=')      // WHERE id = $claim_id
          ->condition('verified', $vcond, '=')   // WHERE verified = 0 for verified claims, and  verified = 1 for unverified claims
        );

  //$query->fields('c', array('cid', 'uid', 'open', 'note', 'created', 'verified')) // SELECT the fields from CLAIM_COLL
  //      ->condition('id', $claim_id, '=');                                        // WHERE id = $claim_id

  $result = $query->execute();

  $num_of_result = $result->rowCount(); // should be 1 if fetching is successful.

  $record = array(); // empty array

  if($num_of_result > 0) $record = $result->fetchAssoc();

  db_set_active();
  return $record; // if no record found, an empty array is returned;
}

function get_verified_claim_by_id($claim_id) { return get_claim_by_id_and_vcond($claim_id, 1); }

function get_unverified_claim_by_id($claim_id) { return get_claim_by_id_and_vcond($claim_id, 0); }

function get_claim_by_id($claim_id) {
  db_set_active(CLAIM_DB);

  if(!db_table_exists(CLAIM_COLL)) { db_set_active(); return array(); }

  $query = db_select(CLAIM_COLL, 'c');
  $query->join(CLAIM_AUTHOR, 'a', 'c.uid = a.uid'); //JOIN

  $query->fields('c', array('id', 'cid', 'hd', 'copyright', 'display', 'note', 'created', 'verified', 'openmosaic')) //SELECT the fields from CLAIM_COLL
        ->fields('a',array('login_name', 'real_name', 'email', 'phone', 'address', '4digitid'))
        ->condition('id', $claim_id, '=');                                      // WHERE id = $claim_id

  //$query->fields('c', array('cid', 'uid', 'open', 'note', 'created', 'verified')) // SELECT the fields from CLAIM_COLL
  //      ->condition('id', $claim_id, '=');                                        // WHERE id = $claim_id

  $result = $query->execute();

  $num_of_result = $result->rowCount(); // should be 1 if fetching is successful.

  $record = array(); // empty array

  if($num_of_result > 0) $record = $result->fetchAssoc();

  //$claims = array();
  //while($record = $result->fetchAssoc()) { $claims[] = $record; }

  db_set_active();

  return $record; // if no record found, an empty array is returned;
}

/*
 * will update ft_search table
 * need run bg_index() after the execution.
 */
function _make_collection_copyright_unknown($collection_id) {

  $node = node_load($collection_id);
  if($node) {

    $record = _get_form_record('collection', $node);
    //dbug_message($record);
    $record_to_save = _normalize_for_collection_save($record);
    //dbug_message($nrecord);

    $record_to_save['field_release']           = "否"; // 公眾授權與否
    $record_to_save['field_license_note']      = "";   // 姓名標示值
    $record_to_save['field_high_resolution']   = "";   // 高解析開放與否
    $record_to_save['field_license']           = "";   // 釋出條款
    $record_to_save['field_rightgranted_note'] = "";   // 權利依據
    $record_to_save['field_rightgranted']      = "權利狀態不明";
    $node = _coll_item_save($record_to_save); // @ coll/coll.inc
    ft_table_update($node); // @ expsearch
    return true;
  } else {
    drupal_set_message("collclaimadmin::_make_collection_copyright_unknown(): node_load() fail.");
    return false;
  }
}



function delete_claim_by_id($claim_id) {

  $claim = get_claim_by_id($claim_id);
  //dbug_message($claim);

  if(!empty($claim)) {
    if($claim['verified'] == 1) { // the verified claim
      if(has_verified_claims_of_a_collection($claim['cid']) == 1) { // only 1 verified claim of a collection
        // make collection's copyright status uncertain.
        if(_make_collection_copyright_unknown($claim['cid'])) bg_index();
      }
    }
  } else {
    return false;
  }

  db_set_active(CLAIM_DB);
  if(!db_table_exists(CLAIM_COLL)) { db_set_active(); return false; }

  $num_deleted = db_delete(CLAIM_COLL)
                 ->condition('id', $claim_id)
                 ->execute();
  db_set_active();

  if(empty($num_deleted)) return false;
  else                    return true;
}
