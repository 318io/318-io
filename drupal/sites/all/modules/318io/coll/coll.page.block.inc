<?php

function _coll_block_searchbox() {
  $count = db_query('SELECT count(*) from {node} where type=:type', array(':type'=>'collection'))->fetchField();
  $lase_updated = db_query('SELECT MAX(`created`) from {node} where type=:type', array(':type'=>'collection'))->fetchField();
  $ss = '<p style="text-align:center">共'.$count.'筆，最後更新於：'.date('Y/m/d', $lase_updated).'</p>';
  $r = '<form class="search-form" action="search">
       <input class="searchbox form-text" type="text" name="qs" id="qs"/>
       <input class="searchbox-submit" type="submit" name="su" value="找"/>
       </form>'.$ss;
  return $r;
}
