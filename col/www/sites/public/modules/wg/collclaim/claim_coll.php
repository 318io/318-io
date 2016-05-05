<?php

require_once('collclaim.db.inc');

/*
 Claiming collections table creation query

 CREATE TABLE collection_author_table (
    ->   id
    ->   cid INT UNSIGNED NOT NULL,
    ->   uid INT UNSIGNED NOT NULL,
    ->   created INT UNSIGNED NOT NULL,
    ->   hd INT NOT NULL DEFAULT 0,
    ->   copyright INT NOT NULL,
    ->   display TEXT,
    ->   note TEXT,
    ->   verified INT NOT NULL DEFAULT 0
    ->   openmosaic INT NOT NULL DEFAULT 1
    ->   unique(cid, uid)
    -> )
*/
function _build_claiming_table() {
  $schema = array(
    'description' => 'author collection claiming table',
    'fields' => array(
      'id'  => array('type' => 'serial', 'not null' => TRUE),
      'cid' => array('type' => 'int', 'unsigned' => TRUE, 'not null' => TRUE),
      'uid' => array('type' => 'int', 'unsigned' => TRUE, 'not null' => TRUE),
      'hd'  => array('type' => 'int', 'not null' => TRUE),  // 0: no hd download, 1: allow hd download
      'copyright' => array('type' => 'int', 'not null' => TRUE), // 0 - 8
      'display'   => array('type' => 'text'), // display name
      'note'      => array('type' => 'text'),
      'created'   => array('type' => 'int', 'unsigned' => TRUE, 'not null' => TRUE, 'default' => '0'), // created time
      'verified'  => array('type' => 'int', 'not null' => TRUE, 'default' => 0),                       // 0: not verified , 1: verified
      'openmosaic'=> array('type' => 'int', 'not null' => TRUE, 'default' => 1)                        // 0: not open, 1: open
    ),
    'unique keys' => array('ukeys' => array('cid', 'uid')),
    'primary key' => array('id')
  );
  _create_table(CLAIM_COLL, $schema);
}

function _collclaim_del_claim_by_uid($uid) {
  try {
    db_set_active(CLAIM_DB); // Use claim database
    if(db_table_exists(CLAIM_COLL)) db_delete(CLAIM_COLL)->condition('uid', $uid)->execute();
  } catch(Exception $e) {
    drupal_set_message('_collclaim_del_claim_by_uid(): ' . $e->getMessage());
  }
  db_set_active();
}

function _collclaim_add_claim($uid, $collection_id, $hd, $copyright, $display, $note, $openmosaic) {
  db_set_active(CLAIM_DB); // Use CLAIM database
  if(!db_table_exists(CLAIM_COLL)) _build_claiming_table();

  $obj = array(
      'cid'       => $collection_id,
      'uid'       => $uid,
      'hd'        => $hd,
      'copyright' => $copyright,
      'display'   => $display,
      'note'      => $note,
      'created'   => time(),
      'openmosaic'=> $openmosaic
  );

  $claim_id = 0;

  try {
    $query = db_insert(CLAIM_COLL)->fields($obj);
    $claim_id = $query->execute();
  } catch(Exception $e) {
    dbug_message($e->errorInfo[2]);
  }

  db_set_active();
  return $claim_id;
}

function _collclaim_get_all_verified_claims() {
  db_set_active(CLAIM_DB); // Use CLAIM database

  if(!db_table_exists(CLAIM_COLL)) { db_set_active(); return array(); }

  $query = db_select(CLAIM_COLL, 'c');

  $query->join(CLAIM_AUTHOR, 'a', 'c.uid = a.uid'); //JOIN

  //$query->groupBy('c.uid');//GROUP BY user ID

  $query->fields('c', array('id', 'cid','hd', 'copyright', 'display', 'note', 'created', 'verified', 'openmosaic')) //SELECT the fields from CLAIM_COLL
        ->fields('a',array('real_name', 'email', 'phone', 'address', '4digitid'))                       //SELECT the fields from CLAIM_AUTHOR
        ->orderBy('cid', 'DESC');
        //->orderBy('created', 'DESC'); //ORDER BY created
        //->range(0,2); //LIMIT to 2 records

  $query->condition('verified', 1, '=');

  $result = $query->execute();
  $claims = array();
  while($record = $result->fetchAssoc()) { $claims[] = $record; }

  db_set_active();

  return $claims;
}

function _collclaim_get_claims_of_an_author($user_id) {
  db_set_active(CLAIM_DB); // Use CLAIM database

  if(!db_table_exists(CLAIM_COLL)) { db_set_active(); return array(); }

  $query = db_select(CLAIM_COLL, 'c');

  //$query->join(CLAIM_AUTHOR, 'a', 'c.uid = a.uid'); //JOIN
  //$query->groupBy('c.uid');//GROUP BY user ID

  $query->fields('c', array('id', 'cid', 'uid', 'hd', 'copyright', 'display', 'note', 'created', 'verified', 'openmosaic')) //SELECT the fields from CLAIM_COLL
        ->orderBy('id', 'ASC');
        //->orderBy('created', 'DESC'); //ORDER BY created
        //->range(0,2); //LIMIT to 2 records

  $query->condition('uid', $user_id, '=');

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

  $query->fields('c', array('id', 'cid','open', 'note', 'created', 'verified', 'openmosaic')) //SELECT the fields from CLAIM_COLL
        ->fields('a',array('real_name', 'email', 'phone', 'address'))
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

  //drupal_set_message($cid);

  $query = db_select(CLAIM_COLL, 'c');

  $query->fields('c', array('uid', 'cid')) //SELECT the fields from CLAIM_COLL
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


function get_claim_by_id($claim_id) {
  db_set_active(CLAIM_DB);

  if(!db_table_exists(CLAIM_COLL)) { db_set_active(); return array(); }

  $query = db_select(CLAIM_COLL, 'c');
  $query->join(CLAIM_AUTHOR, 'a', 'c.uid = a.uid'); //JOIN

  $query->fields('c', array('id', 'cid', 'hd', 'copyright', 'display', 'note', 'created', 'verified', 'openmosaic')) //SELECT the fields from CLAIM_COLL
        ->fields('a',array('login_name', 'real_name', 'email', 'phone', 'address', '4digitid'))
        ->condition('id', $claim_id, '=');                                      // WHERE id = $claim_id

  $result = $query->execute();

  $num_of_result = $result->rowCount(); // should be 1 if fetching is successful.

  $record = array(); // empty array

  if($num_of_result > 0) $record = $result->fetchAssoc();

  //$claims = array();
  //while($record = $result->fetchAssoc()) { $claims[] = $record; }

  db_set_active();

  return $record; // if no record found, an empty array is returned;
}

function get_raw_claim_by_id($claim_id) {
  db_set_active(CLAIM_DB);

  if(!db_table_exists(CLAIM_COLL)) { db_set_active(); return array(); }

  $query = db_select(CLAIM_COLL, 'c');
  //$query->join(CLAIM_AUTHOR, 'a', 'c.uid = a.uid'); //JOIN

  $query->fields('c', array('id', 'cid', 'uid', 'hd', 'copyright', 'display', 'note', 'created', 'verified', 'openmosaic')) //SELECT the fields from CLAIM_COLL
        ->condition('id', $claim_id, '=');                                      // WHERE id = $claim_id

  $result = $query->execute();

  $num_of_result = $result->rowCount(); // should be 1 if fetching is successful.

  $record = array(); // empty array

  if($num_of_result > 0) $record = $result->fetchAssoc();

  //$claims = array();
  //while($record = $result->fetchAssoc()) { $claims[] = $record; }

  db_set_active();

  return $record; // if no record found, an empty array is returned;
}
