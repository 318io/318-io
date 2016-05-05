<?php

function array_foldl(array $ar, Closure $fun, $init) {
  $length = count($ar);
  $data = $ar;
  $acc = $init;
  
  while($length != 0) {
    $item = $data[0];
    $data = array_slice($data, 1);
    $acc = $fun($acc, $item);
    $length--;
  }
  return $acc;
}

/* 
  $input[] = array(1,2,3,4);
  $input[] = array('a','b');
  $input[] = array('X', 'Y', 'Z');
  $input[] = array('hello', 'world');

  product_array($input);
  
  run:
  
  result[0] = array(1, 'a', 'X', 'hello')
  result[1] = array(1, 'a', 'X', 'world')
  result[2] = array(1, 'a', 'Y', 'hello')
  .....
*/
function cartesian_product(array $arrays) {
  $pair_product = function(array $a1, array $a2) {
    if(count($a1) == 0) return $a2;     
    $result = array();
    foreach($a1 as $i1) {
      if(count($a2) == 0) return $a1;
      foreach($a2 as $i2) {
        if(is_array($i1)) {
          $result[] = array_merge($i1, array($i2));
        } else {
          $result[] = array($i1, $i2);
        }
      }
    }
    return $result;
  };
  return array_foldl($arrays, $pair_product, array());
}


function cartesian_product_gen(array $arrays) {
  $pair_product_gen = function($a1, $a2) {
    if(is_array($a1) && count($a1) == 0) { foreach($a2 as $i2) yield $i2; }
    foreach($a1 as $i1) {
      foreach($a2 as $i2) {
        if(is_array($i1)) {
          yield array_merge($i1, array($i2));
        } else {
          yield array($i1, $i2);
        }
      }
    }
  };
  return array_foldl($arrays, $pair_product_gen, array());  
}


/* character generator */
function chr_gen($start, $end) {
  if(isset($start) && isset($end)) {
  if($start <= $end) {
    for($i = $start; $i <= $end; $i++) {
      yield chr($i);
    }
  }
  }
  //throw new LogicException('Arguments error');  
}

/*
foreach(chr_gen(48,57) as $n) {
  echo $n;
}

$iter = chr_gen(48,57);

echo $iter->current();
$iter->next();
echo $iter->current();
*/

function lo_letter_gen() {
  return chr_gen(97, 122);  
}

function up_letter_gen() {
  return chr_gen(65, 90);
}

function num_gen() {
  return chr_gen(48, 57);
}

/*
$iter = up_letter_gen();
echo $iter->current() . "\n";
$iter->next();
echo $iter->current() . "\n";
*/

function chr_array($start, $end) {
  if(isset($start) && isset($end)) {
  if($start <= $end) {
    for($i = $start; $i <= $end; $i++) {
      $return[] = chr($i);
    }
    return $return;
  }
  }
  return array();
}

function lo_letter_array() {
  return chr_array(97, 122);  
}

function up_letter_array() {
  return chr_array(65, 90);
}

function num_array() {
  return chr_array(48, 57);
}


?>