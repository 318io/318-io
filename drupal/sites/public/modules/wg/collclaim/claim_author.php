<?php

require_once('collclaim.db.inc');

/*
 Author table creation query

 CREATE TABLE claim_author_table (
    ->   uid INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    ->   login_name
    ->   real_name
    ->   email
    ->   phone
    ->   address
    ->   4digitid
    ->   created
    -> )

 references:
   * https://dev.mysql.com/doc/refman/5.0/en/integer-types.html
   * https://api.drupal.org/api/drupal/includes!database!schema.inc/group/schemaapi
   * https://api.drupal.org/api/drupal/includes%21database%21database.inc/7
*/
function _build_claim_author_table() {
  $schema = array(
           'description' => 'author table',
           'fields' => array(
             'uid'         => array('type' => 'int', 'unsigned' => TRUE, 'not null' => TRUE),
             'login_name'  => array('type' => 'text'),
             'real_name'   => array('type' => 'text'),
             'email'       => array('type' => 'text'),
             'phone'       => array('type' => 'text'),
             'address'     => array('type' => 'text'),
             '4digitid'    => array('type' => 'text'),
             'created' => array('type' => 'int', 'unsigned' => TRUE, 'not null' => TRUE, 'default' => '0'), // timestamp
           ),
           'primary key' => array('uid')
         );
  _create_table(CLAIM_AUTHOR, $schema);
}

/*
author = array(
  'uid' =>
  'login_name'=>
  'real_name' =>
  'email'     =>
  'phone'     =>
  'address'   =>
  '4digitid'  =>
  'created'   =>
);
 */
function _collclaim_add_author($author) {
  db_set_active(CLAIM_DB); // Use claim database
  if(!db_table_exists(CLAIM_AUTHOR)) _build_claim_author_table();

  // refer https://www.drupal.org/node/310079
  $query = db_insert(CLAIM_AUTHOR)->fields($author);
  $query->execute();

  db_set_active(); //  Go back to the default database
}

function _collclaim_update_author($uid, $author) {
  db_set_active(CLAIM_DB); // Use claim database
  if(!db_table_exists(CLAIM_AUTHOR)) _build_claim_author_table();

  $num_updated = db_update(CLAIM_AUTHOR)
  ->fields($author)
  ->condition('uid', $uid, '=')
  ->execute();

  db_set_active();
  //dbug_message($num_updated . ' author have been updated');
}


function _collclaim_get_author($uid) {
  db_set_active(CLAIM_DB); // Use claim database
  if(!db_table_exists(CLAIM_AUTHOR)) _build_claim_author_table();

  $query = db_select(CLAIM_AUTHOR, 'c');
  $query->fields('c', array('login_name', 'real_name', 'email', 'phone', 'address', '4digitid', 'created'))
        ->condition('uid', $uid, '=');
  $result = $query->execute();

  $num_of_results = $result->rowCount();

  $record = array();

  if($num_of_results > 0) $record = $result->fetchAssoc();

  db_set_active();

  return $record;
}

function _collclaim_del_author($uid) {
  //dbug_message('my delete');
  try {
    db_set_active(CLAIM_DB); // Use claim database
    if(db_table_exists(CLAIM_AUTHOR)) db_delete(CLAIM_AUTHOR)->condition('uid', $uid)->execute();
  } catch(Exception $e) {
    drupal_set_message('_collclaim_del_author(): ' . $e->getMessage());
  }
  db_set_active();

  // delete releated claimed collection
  _collclaim_del_claim_by_uid($uid);
}
