<?php

class DuplicateException extends Exception {
  function __construct($message) {
    parent::__construct($message);
  }

  public function __toString() { return $this->message; }
}

function pset() {
  return new PSet(func_get_args());
}

/*
 * Primitive type set
 */
class PSet {
  protected $data = array();

  function __construct(array $a) {
    if(isset($a)) {
      foreach($a as $i) $this->add($i);
    } else {
      $this->data = array();
    }
  }

  public function add($it) {
    if(!in_array($it, $this->data, true)) {
      $this->data[] = $it;
    }
    return $this;
  }

  public function _add($it) {
    if(!in_array($it, $this->data, true)) {
      $ret = new PSet($this->data);
      $ret->add($it); 
      return $ret;
    } 
    throw new DuplicateException('PSet::_add(): duplicated item.'); 
  }

  public function hasData($it) { return in_array($it, $this->data, true); }

  public function size() { return count($this->data); }

  public function _diff(PSet $target) {} 

  public function _union(PSet $target) {}

  public function _intersect(PSet $target) { }

  public function toArray() { return $this->data; }
  public function view()    { print_r($this->data); }
}

function stack() {
  return new Stack(func_get_args());
}

class Stack {
  private $data = array();

  function __construct(array $a) {
    if(isset($a)) $this->data = array_values($a);
    else          $this->data = array();
  }

  public function push($item) {
    $this->data[] = $item;
  }

  public function pop() {
    if($this->size() != 0) return array_pop($this->data);
    else                   return NULL;
  }

  public function peek() {
    if($this->size() != 0) return $this->data[$this->size()-1];
  }

  public function size() { return count($this->data); }

  public function reset() { $this->data = array(); }
}


/* every inserted element should be the same as previous one. */
class EqStack extends Stack {

  function __construct(array $a) {
    parent::__construct($a);
  }

  public function push($item) {
    if($item == $this->peek()) parent::push($item);
    else throw new LogicException("EqStack::push(): unequal item.");
  }
}


function flist() {
  return new FList(func_get_args());
}

class FList {

  private $data = array();

  // construction from array
  function __construct(array $a) {
    if(isset($a)) $this->data = array_values($a);
    else          $this->data = array();
  }

  /*
  // only for php 5.6+
  function __construct(...$items) {
    $this->data = array_values($items);
  }
  */

  public function cons($item) {
    $data = $this->data;
    array_unshift($data, $item); // add $item in front of array
    return new FList($data);
  }

  public function append(FList $list) {
    return new FList(array_merge($this->data, $list.toArray()));
  }

  public function toArray() { return $this->data; }

  public function length() { return count($this->data); }

  public function item($index) {
    if($index < 0 || $index > ($this->length()-1)) return NULL;
    return $this->data[$index]; 
  }

  /* if empty, return NULL */
  public function head() { 
    if($this->length() == 0) return NULL;
    else                     return $this->data[0]; 
  }

  /* 
   * return new FunList
   */
  public function tail() {
    return new FList(array_slice($this->data, 1));
  }

  /*
   * $fun(mixed $carry , mixed $item)
   *
   * If the list is empty and init is not passed, returns NULL.
   */
  public function foldl(Closure $fun, $init) {
     return array_reduce($this->data, $fun, $init); 
  }
  
  /*
   * $fun(mixed $carry , mixed $item)
   *
   * If the list is empty and init is not passed, returns NULL.
   */
  public function foldr(Closure $fun, $init) {
    $length = $this->length();
    $data = $this->data;  // copy
    $acc  = $init;

    while($length != 0) {
      $item = array_pop($data); // remove latest item
      $acc = $fun($acc, $item);
      $length--;
    }
    return $acc;  
  }
  
  /*
   * mixed $fun(mixed item)
   */
  public function map(Closure $fun) {
    return new FList(array_map($fun, $this->data));
  }
  

  /*
   * boolean $fun(mixed item)
   */
  public function filter(Closure $fun) {
    return new FList(array_filter($this->data, $fun)); 
  }


  /*
   * boolean $fun($item), if all items are tested to be true of $fun, return true. Else, return false;
   * **** TODO: rename to forall *****
   */
  public function test(Closure $fun) {
    $_list = $this->filter(function($item) use ($fun) { return !$fun($item); });
    if($_list->length() != 0) return FALSE;
    else                      return TRUE;
  }

  /*
   * There exist at least one item evaluated to be true on $fun.
   */
  public function some(Closure $fun) {
    foreach($this->data as $item) { if($fun($item)) return TRUE; }
    return FALSE;
  }

  /*
   * Test each $item with $fun, if $fun($item) evaluated to be FALSE, stop iteration and return FALSE.
   * Else return TRUE.
   */
  public function forall(Closure $fun) {
    foreach($this->data as $item) { if(!$fun($item)) return FALSE; }
    return TRUE;
  }
}


/*  
 * TODO:
 * 1. duplicated record 
 */
class RelationTable {

  protected $columns = NULL;
  protected $column_count = 0;
  protected $table = NULL;

  function __construct(array $column_names) {
    //$arglist = new FList(func_get_args());
    //$allstring = $arglist->test(function($item) { return is_string($item); });
    $arglist = new FList($column_names);
    $allstring = $arglist->test(function($item) { return is_string($item); });

    if($allstring) {
      $this->columns = $column_names;
      $this->column_count = count($this->columns);
      $this->table = array(); // array of records
    } else {
      throw new LogicException('RelationTable::ctor(): column is not string.');
    }
  }

  public function getEmptyClone() { return new RelationTable($this->columns); }

  public function getClone() {
    $new_table = new RelationTable($this->columns);
    foreach($this->table as $record) {
      $new_table->insert($record);
    }
    return $new_table;
  }

  public function reset() { $this->table = array(); }

  protected function column_match($record) {
    $i = 0;
    foreach($record as $column => $value) {
      if(!in_array($column, $this->columns)) return FALSE;
      $i++;
    }
    if($i != $this->column_count) return FALSE; // incomplete record    
    return TRUE;
  }

  public function arity() { return $this->column_count; }

  public function isEmpty() {
    if(!isset($this->table) || count($this->table) == 0) return TRUE;
    else return FALSE;
  }

  public function columns() { return $this->columns; }

  public function records() { return $this->table; }

  /*
   * array(array(v1,v2,...),
   *       array(v3,v4,...) 
   *      )
   */
  public function tuples() {
    $tuples = array();
    foreach($this->table as $record) {
      $tuple = array();
      foreach($record as $column => $value) {
        $tuple[] = $value;
      }
      $tuples[] = $tuple;
    }
    return $tuples;
  }

  public function tuples_gen() {
    foreach($this->table as $record) {
      $tuple = array();
      foreach($record as $column => $value) {
        $tuple[] = $value;
      }
      yield $tuple;
    }
  }

  /* 
   * $a = array(column_name => value, ....)
   */
  public function insert(array $a, $unique = FALSE) {
    if($this->column_match($a)) {
      //print_r($a);
      if($unique && (!$this->select($a)->isEmpty())) throw new DuplicateException("RelationTable::insert(): duplicated record.");
      $this->table[] = $a;
      return TRUE;
    }
    return FALSE;
  }

  /*
   * $records = array(
   *     [0] => array(column_name => value, ....),
   *     [1] => array(column_name => value, ....),
   *     [2] => array(column_name => value, ....), ...
   * )
   */
  public function insert_array(array $records, $unique = FALSE, $ignore = FALSE) {
    foreach($records as $record) { 
      try {
        $this->insert($record, $unique);
      } catch(DuplicateException $e) {
        if(!$ignore) throw $e;
      } 
    }
  }

  public function insert_unique_row() {
    $this->insert_row_array(func_get_args(), TRUE);
  }

  /* 
   * argument are ordered as column order
   * insert_record('a', 'b', 'c')   
   */
  public function insert_row() {
    $this->insert_row_array(func_get_args(), FALSE);
  }

  public function insert_row_array(array $row, $unique = FALSE) {
    if($this->column_count == count($row)) {
      $i   = 0;
      $record = array();
      foreach($row as $instance) {
        $record[$this->columns[$i]] = $instance;
        $i++;
      }
      if(!$this->insert($record, $unique)) throw new LogicException('RelationTable::insert_row_array(): unmatched column.');
    } else {
      throw new LogicException('RelationTable::insert_row_array(): unmatched arguments.');
    }
  }

  /* 
   * if $pattern = array(), return TRUE always.
   * any records will be matched.
   */
  protected function record_match($pattern, $record) {
    foreach($pattern as $key => $value) {
      if( !array_key_exists($key, $record) ) return FALSE;
      if( $record[$key] != $value)           return FALSE;
    }
    return TRUE;
  }

  /* 
   * for example
   * $record_pattern = array('id' => 2, 'name' => 'Harry')
   *
   * return a new FTable back
   *
   * if $record_pattern is empty array, return the full table back.
   *
   */
  public function select(array $record_pattern) {
    return $this->select_on(function($record) use ($record_pattern) {
      return $this->record_match($record_pattern, $record);
    });
  }

  public function select_on(Closure $fun) {
    $result = new RelationTable($this->columns);
    foreach($this->table as $record) {
      $match = $fun($record);
      if($match) $result->insert($record);
    }
    return $result;
  }

  protected function record_project(array $columns_to_keep, array $record) {
    $new_record = array();
    foreach($record as $column => $value) {
      if(in_array($column, $columns_to_keep)) $new_record[$column] = $value;
    }
    return $new_record;
  }

  /*
   * project the denoted columns
   */
  public function project(array $columns) {
    $new_table = new RelationTable($columns);

    if(array_diff($columns, $this->columns) == array()) {
      $list = new FList($this->table);
      $new_records = $list->foldl(function($acc, $record) use ($columns) {
        $acc[] = $this->record_project($columns, $record);
        return $acc;
      }, array());
      $new_table->insert_array($new_records);
    }
    return $new_table;
  }


  public function valuesOfColumn($column_name) {
    $list = new FList($this->table);
    $values = $list->foldl(function($acc, $record) use($column_name) {
      $acc[] = $record[$column_name];
      return $acc;
    }, array());
    return $values;
  }

  public function theSameColumns(RelationTable $from) {

  }

  public function merge(RelationTable $from) {

  }

  public function view() {
    foreach($this->table as $record) {
      echo "(";
      foreach($record as $col => $val) {
        echo $val . ", ";
      }
      echo ")\n";
    }
  }

  /*
   * 1. every column should be in equal length.
   * 
   */
  protected function check_integrity() {
    $list = new FList($this->table);
    $length_array = $list.foldl(function($acc, $item) {
      $acc[] = count($item);
      return $acc;
    }, []);
  }

  /* ------------- Iterator --------------- */
}


?>