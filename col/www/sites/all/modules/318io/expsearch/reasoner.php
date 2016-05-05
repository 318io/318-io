<?php

require_once "fun.php";
require_once "tools.php";

interface ERule {
  public function headName();
  public function getClone();
}


class Variable {
  private $name = "";
  private $value = NULL;

  function __construct($name) { $this->name = $name; }

  public function toString() {
    return $this->name . '[' . (isset($this->value) ? $this->value : '?') . ']';
  }

  // ignore the overwritting
  public function setValue($v) {
    if(!isset($this->value)) { $this->value = $v; }
    // else { throw new LogicException("Variable::setValue(): cannot overwrite."); }
  }
  public function getName() { return $this->name; }
  public function getValue()   { return $this->value; }
  public function hasValue()   { return isset($this->value); }
  public function reset()      { $this->value = NULL; }

  public function getClone() {
    $nobj =  new Variable($this->name);
    $nobj->setValue($this->value);
    return $nobj;
  }

  /* ------------------remove later ------------------------- */
  /*
  private $values = NULL;
  private $pos = 0;
  public function setValues(array $vs) { $this->values = $vs; }
  public function getValues()          { return $this->values; }
  public function reset() { $this->values = array(); }
  */

  /* ------------ implement iterator -------------- */
  /*
  function rewind()  { $this->pos = 0; }
  function next()    { ++$this->pos; }
  function current() { return $this->values[$this->pos]; }
  function key()     { return $this->pos; }
  function valid()   { return isset($this->values[$this->pos]); }
  */
}

/*
 * term('hasChild', 'A', ...) or
 * term('hasChild')
 */
function term() {
  if(func_num_args() < 1) return NULL;
  $argv = new FList(func_get_args());
  return new Term($argv->head(), $argv->tail()->toArray());
}

class Term {
  private $name = "";         // String
  private $variables = NULL;  // [Variable]
  private $next = NULL;       // Term

  /*
   * new Term('hasChild', array('A', 'B')) or
   * new Term('hasChild', array())
   */
  function __construct($name, array $vars) {
    $this->name = $name;
    $_vars = array();
    foreach($vars as $v) { if(is_string($v)) $_vars[] = new Variable($v); }
    $this->variables = $_vars;
  }

  public function getClone() {
    $nobj = new Term($this->name, array());
    foreach($this->variables as $var) {
      $n_var = $var->getClone();
      $nobj->addVariable($n_var);
    }
    return $nobj;
  }

  public function setNext(Term $t) { $this->next = $t; }

  public function next() { return $this->next; }

  public function hasNext() { return isset($this->next); }

  public function ready() {
    if($this->name != "" && isset($this->variables)) return TRUE;
    else return FALSE;
  }

  public function getName() { return $this->name; }

  /* $v : string */
  public function addVariableByName($v) {
    if(is_string($v)) $this->variables[] = new Variable($v);
    else throw new LogicException('Term::addVariable() : not string.');
  }

  public function addVariable(Variable $v) {
    $this->variables[] = $v;
  }

  public function addVariables(array $vs) {
    foreach($vs as $v) $this->addVariable($v); // append
  }

  public function getVariables()     { return $this->variables; }

  public function delVariables()     { $this->variables = array(); }

  public function arity()            { return count($this->variables); }

  public function assignValues(array $instances) {

    $this->reset(); // reset for new value.

    if($this->ready() && ($this->arity() <= count($instances))) {
      $i = 0;
      foreach($this->variables as $var) {
        $var->setValue($instances[$i]); // overwritting will be ignored
        $i++;
      }
    } else {
      echo $this->arity() . "\n";
      echo count($instances) . "\n";
      echo $this->toString() . "\n";
      if(!$this->ready()) throw new LogicException("Term::assignValues(): Not ready.");
      else throw new LogicException("Term::assignValues(): Incomplete value assignment.");
    }
  }

  public function reset() {
    if($this->ready()) {
      foreach($this->variables as $var) { $var->reset(); }
    }
  }

  // TRUE: all variables have value
  public function readyForResolve() {
    $list = new FList($this->variables);
    return $list->forall(function($var) {
      return $var->hasValue();
    });
  }

  public function getInstances() {
    $list = new FList($this->variables);

    if(!$this->readyForResolve()) return array(); // not ready, return []

    return $list->foldl(function($acc, $var) {
      $acc[] = $var->getValue();
      return $acc;
    }, array());
  }

  /*
   * parameter are arrays of instances, the number of instances array depends on the arity of Term.
   *
   * setCandidate(array(a1,a2,a3), array(b1,b2), ...)
   */
  /*
  public function setCandidates() {
    if(func_num_args() == $this->arity()) {
      $alist = new FList(func_get_args());
      $test_array = $alist->test(function($item) { return is_array($item); });
      if($test_array) {
        $i = 0;
        foreach($this->variables as $variable) { $variable->setCandidates($alist->item($i)); $i++; }
      }
    }
  }*/

  public function toString() {
    $vars = new FList($this->variables);
    $vstring = $vars->foldl(function($acc, $item) {
      return $acc . $item->toString() . ', ';
    }, '');
    $vstring = substr($vstring, 0, strlen($vstring)-2);
    return $this->name . "(" . $vstring . ")";
  }
}

function grule($head, array $body) {
  if(count($body) == 0) throw new LogicException('grule() : body cannot be empty.');
  $bodylist = new FList($body);
  $check = $bodylist->forall(function($term) {if($term instanceof TERM) return TRUE; else return FALSE;});
  if(!$check) throw new LogicException('grule() : body contains non-term item.');
  if(!$head instanceof TERM) throw new LogicException('grule() : name is not term.');
  return new Grule($head, $body);
}

/*
 * General Rule
 *
 * new
 * Grule(term('hasDesc' array('A', 'C')), array(term('hasChild', array('A', 'B')),
                                                term('hasDesc', array('B', 'C'))
                                               )
             )
        )
 */
class Grule implements ERule {
  private $head = NULL;    // Term
  private $body = NULL;    // FList[Term]
  private $symbols = NULL; // [Name => Variable], variable table

  private $symbol_names = NULL; // PSet[Name]

  private function addSymbol($var) {
    if(!isset($this->symbol_names)) $this->symbol_names = pset();
    $name = $var->getName();
    try {
      $this->symbol_names = $this->symbol_names->_add($name);
      $this->symbols[$name] = $var;
      return $var; // return the newly added var
    } catch(DuplicateException $e) {
      return $this->symbols[$name]; // return the old var
    }
  }

  private function unifySymbols(&$term) {
    $vars = $term->getVariables(); // [Variable]
    $nvars = array();
    //echo count($vars) . "\n";
    foreach($vars as $var) {
      $nvars[] = $this->addSymbol($var); // new variables array
    }
    $term->delVariables(); // remove old
    $term->addVariables($nvars);
  }

  function __construct($head, array $body) {
    if(!($head instanceof Term)) throw new LogicException('Grule::__construct() : head is not Term type.');

    $bodylist = new FList($body);
    $_bodylist = $bodylist->filter(function($item) {
       if($item instanceof Term) return TRUE;
       else                      return FALSE;
    });
    if($_bodylist->length() < 1) throw new LogicException('Grule::__construct() : no body defined.');

    //$head_vars = $head->getVariables(); // [Variable]
    $this->unifySymbols($head);
    $this->head = $head;

    $pre = NULL;
    $this->body = $_bodylist->map(function($term) use (&$pre) {
      if(isset($pre)) $pre->setNext($term);
      $pre = $term;
      $this->unifySymbols($term);
      return $term;
    });
  }

  public function getClone() {
    $nhead = $this->head->getClone();
    $nbody_array = $this->body->foldl(function($acc, $term) {
      $acc[] = $term->getClone();
      return $acc;
    }, array());

    $nrule = new Grule($nhead, $nbody_array);

    return $nrule;
  }

  public function reset() {
    // all variables' value are cleared
    foreach($this->symbols as $symbol) { $symbol->reset(); }
  }

  public function toString() {
    $body_string = $this->body->foldl(function($acc, $item){
      return $acc . $item->toString() . " o ";
    }, '');
    $body_string = substr($body_string, 0, strlen($body_string)-2);
    return $this->head->toString() . " :- " . $body_string ;
  }

  public function ready() {
    if(isset($this->head) && isset($this->body)) return true;
    else                                         return false;
  }

  public function headName() {
    return $this->head->getName();
  }

  /* return Term */
  public function getHead() { return $this->head; }

  /* return FList[Term] */
  public function getBody() { return $this->body; }
}

/*
 * chain('hasDesc', array('hasChild', 'hasDesc'))
 */
function chain($name, array $body) {
  if(count($body) == 0) throw new LogicException('chain() : body cannot be empty.');

  $bodylist = new FList($body);

  $_bodylist = $bodylist->map(function($item) {
    if(is_string($item)) return term($item);
    else throw new LogicException('chain() : body cantains nonstring.');
  })->toArray();

  if(is_string($name)) $head = term($name);
  else throw new LogicException('chain() : name is not string.');

  return new Chain($head, $_bodylist);
}


class Chain implements ERule {
  private $head = NULL;    // Term
  private $body = NULL;    // FList[Term]
  private $symbols = NULL; // [Name => Variable], variable table

  protected function checkVariableByName($name) {
    if(!isset($this->symbols[$name])) {
      $this->symbols[$name] = new Variable($name);
    }
    return $this->symbols[$name];
  }

  /*
   * new Chain(term('hasDesc'), array(term('hasChild'), term('hasDesc')))
   */
  function __construct($head, array $body) {
    if(!($head instanceof Term)) throw new LogicException('Chain::__construct() : head is not Term type.');

    $bodylist = new FList($body);
    $_bodylist = $bodylist->filter(function($item) {
       if($item instanceof Term) return TRUE;
       else                      return FALSE;
    });
    if($_bodylist->length() < 1) throw new LogicException('Chain::__construct() : no body defined.');

    $letters = up_letter_gen(); // [A-Z] : generator

    $head->addVariable($this->checkVariableByName($letters->current()));

    $pre = NULL;
    $this->body = $_bodylist->map(function($item) use ($letters, &$pre) {
      // link body terms.
      if(isset($pre)) $pre->setNext($item);
      $pre = $item;
      // set variables
      $item->addVariable($this->checkVariableByName($letters->current()));
      $letters->next();
      $item->addVariable($this->checkVariableByName($letters->current()));
      return $item;
    });
    $head->addVariable($this->checkVariableByName($letters->current()));
    $this->head = $head;
  }

  public function getClone() {
    $nhead = new Term($this->head->getName(), array());
    $nbody_array = $this->body->foldl(function($acc, $term) {
      $acc[] = new Term($term->getName(), array());
      return $acc;
    }, array());

    $nchain = new Chain($nhead, $nbody_array);

    // set up values.
    foreach($this->symbols as $vname => $var) {
      $nchain->symbols[$vname]->setValue($var->getValue());
    }
    return $nchain;
  }

  public function reset() {
    // all variables' value are cleared
    foreach($this->symbols as $symbol) { $symbol->reset(); }
  }

  public function toString() {
    $body_string = $this->body->foldl(function($acc, $item){
      return $acc . $item->toString() . " o ";
    }, '');
    $body_string = substr($body_string, 0, strlen($body_string)-2);
    return $this->head->toString() . " :- " . $body_string ;
  }

  public function ready() {
    if(isset($this->head) && isset($this->body)) return true;
    else                                         return false;
  }

  public function headName() {
    return $this->head->getName();
  }

  /* return Term */
  public function getHead() { return $this->head; }

  /* return FList[Term] */
  public function getBody() { return $this->body; }

}

class KBase {

  /*
   * The basic fact tables
   */
  protected $fact_tables = array(); // fact_tables[relation_name] = new RelationTable()

  /*
   * extended fact tables built from fact_tables and eq_tables
   */
  protected $ext_fact_tables = array(); // ext_fact_tables[relation_name] = new RelationTable

  /*
   * The equal table used to denote any two distinct instances are equal.
   * Denoting two instances are equal should rebuild whole knowledge base.
   */
  protected $eq_table = NULL; // eq_table = new RelationTable()

  /*
   * The infered relation tables, used to save the infered relation for speeding up reasoning.
   */
  protected $if_tables   = array(); // if_tables[relation_name] = new RelationTable()

  /*
   * Save the not true relations for speeding up reasoning
   */
  protected $ne_tables = array(); // ne_tables[relation_name] = new RelationTable()

  /*
   * The rule table
   */
  protected $rule_table  = array(); // rule_table[head_name] = instances of ERule classes

  /*
   * instances are distincit and of type string.
   */
  protected $instances   = NULL;    // Set

  function __construct() {
    $this->instances = pset();
    $this->eq_table  = $this->getRelationTable(2); // eq is binary relation
  }

  // generate a relation table with column A, B, C, ....
  private function getRelationTable($arity) {
    $letters = up_letter_gen(); // [A-Z] : generator
    $i = 0;
    $columns = array();
    for($i=0; $i < $arity; $i++) {
      $columns[] = $letters->current();
      $letters->next();
    }
    return new RelationTable($columns);
  }

  // clean the infered data.
  public function clearInfered() {
    $this->if_tables = array();
    $this->ne_tables = array();
  }

/*
  protected function getRangeOf($fact_name, $instance) {
    $table = $this->fact_tables[$fact_name];
    if(!isset($table)) return array();
    $table = $table->select(array('A' => $instance));
    if(!$table->isEmpty()) return $table->valuesOfColumn('B');
    return array();
  }

  protected function getDomainOf($fact_name, $instance) {
    $table = $this->fact_tables[$fact_name];
    if(!isset($table)) return array();
    $table = $table->select(array('B' => $instance));
    if(!$table->isEmpty()) return $table->valuesOfColumn('A');
    return array();
  }
*/

  // select * from $fact_name where $column = $from
  private function selectRelated($fact_name, $from, $to, $column_name) {
    $table = $this->fact_tables[$fact_name];
    if(!isset($table)) return array();
    $table = $table->select(array($column_name => $from)); // new selected tmp table

    if(isset($this->ext_fact_tables[$fact_name])) {
      $ext_table = $this->ext_fact_tables[$fact_name];
      $ext_table = $ext_table->select(array($column_name => $from));
      $table->insert_array($ext_table->records()); // merge the previous infered results for next inference.
    }

    if($table->isEmpty()) return array();

    $for_product = array();
    foreach($table->columns() as $column) {
      if(strcmp($column, $column_name) == 0) $for_product[] = array($to);
      else $for_product[] = $table->valuesOfColumn($column);
    }

    return $for_product;
  }

  private function write_ext_fact($fact_name, $g) {
    foreach($g as $tuple) { $this->addExtFact($fact_name, $tuple); }
  }

  private function infer_equal($fact_name, $from, $to) {
    $column_names = $this->fact_tables[$fact_name]->columns();

    foreach($column_names as $column_name) {
      $for_product = $this->selectRelated($fact_name, $from, $to, $column_name);
      $this->write_ext_fact($fact_name, cartesian_product_gen($for_product));
    }
  }

  /*
   *  eq(a,b) === eq(b,a)
   */
  protected function isEqual($instance1, $instance2) {
    $table = $this->eq_table->select(array('A' => $instance1, 'B' => $instance2));
    if(!$table->isEmpty()) return TRUE;
    $table = $this->eq_table->select(array('A' => $instance2, 'B' => $instance1));
    if(!$table->isEmpty()) return TRUE;
    return FALSE;
  }

  /*
   * A = B = C = D = E
   * should be translated to :
   * A = B   B = C   C = D   D = E
   * A = C   B = D   C = E
   * A = D   B = E
   * A = E
   */
  public function allEqual(array $instances) {
    if(count($instances) > 1) { // at least two instnaces
      $ilist = new FList($instances);
      $first = $ilist->head();
      $others = $ilist->tail();
      $this->one_many_equal($first, $others->toArray());
      $this->allEqual($others->toArray());
    }
  }

  public function one_many_equal($instance, array $others) {
    foreach($others as $o) { $this->setEqual($instance, $o); }
  }

  public function setEqual($instance1, $instance2) {

    if($this->isEqual($instance1, $instance2)) return FALSE;

    $this->eq_table->insert_row_array(array($instance1, $instance2), TRUE);

    // add ext_fact_table
    foreach($this->fact_tables as $fact_name => $fact_table) {
      $this->infer_equal($fact_name, $instance1, $instance2);
      $this->infer_equal($fact_name, $instance2, $instance1);
    }

    /*
    foreach($this->ext_fact_tables as $table_name => $table) {
      echo $table_name . "\n";
      $table->view();
    }
    */

    // clear infered relations
    $this->clearInfered();

    return TRUE;
  }

  public function delEqual($instance1, $instance2) {

  }

  public function rebuildEqual() {

  }

  public function addInstance($ins) {
    $this->instances->add($ins); // duplicate instance will be ignored.
  }

  public function addFact($relation, array $instances) {
    if(!array_key_exists($relation, $this->fact_tables)) { // table doesn't exists
      $this->fact_tables[$relation] = $this->getRelationTable(count($instances));
    }
    // add facts
    $this->fact_tables[$relation]->insert_row_array($instances, TRUE);

    // add instance
    foreach($instances as $ins) { $this->instances->add($ins); }  // duplicate instance will be ignored
  }

  protected function addExtFact($relation, array $instances) {
    if(!array_key_exists($relation, $this->ext_fact_tables)) { // table doesn't exists
      $this->ext_fact_tables[$relation] = $this->getRelationTable(count($instances));
    }
    // add infered facts
    try {
      $this->ext_fact_tables[$relation]->insert_row_array($instances, TRUE);
    } catch(DuplicateException $e) {
      // do nothing
    }
  }

  protected function addInfered($relation, array $instances) {
    if(!array_key_exists($relation, $this->if_tables)) { // table doesn't exists
      $this->if_tables[$relation] = $this->getRelationTable(count($instances));
    }
    // add facts
    $this->if_tables[$relation]->insert_row_array($instances, TRUE);
  }

  protected function addNegated($relation, array $instances) {
    if(!array_key_exists($relation, $this->ne_tables)) { // table doesn't exists
      $this->ne_tables[$relation] = $this->getRelationTable(count($instances));
    }
    // add facts
    $this->ne_tables[$relation]->insert_row_array($instances, TRUE);
  }


  /*
   * rule can have the same head, for example :
   *
   * rule_table['hasDesc'][0] = Rule
   *                      [1] = Rule
   * rule_table['hasAnce'][0] = Rule
   * ....
   */
  public function addRule(ERule $rule) {
    $this->rule_table[$rule->headName()][] = $rule;
  }

/* ------------------------------------------------------------------------------------------------ */

  // $instances contains no question mark
  protected function prepare_for_selection(array $instances) {
    $letters = up_letter_gen(); // [A-Z] : generator
    $result = array();
    foreach($instances as $ins) {
      $result[$letters->current()] = $ins;
      $letters->next();
    }
    return $result;
  }

  private function checkRelation(RelationTable $reltable, array $instances) {
    $pattern  = $this->prepare_for_selection($instances);
    $get = $reltable->select($pattern);
    if($get->isEmpty()) return FALSE;
    else                return TRUE;
  }

  // check if those instances are related
  // $instances contains no question mark
  protected function checkFact($relation, array $instances) {
    if(!isset($this->fact_tables[$relation])) return FALSE;
    $reltable = $this->fact_tables[$relation];
    return $this->checkRelation($reltable, $instances);
  }

  protected function checkExtFact($relation, array $instances) {
    if(!isset($this->ext_fact_tables[$relation])) return FALSE;
    $reltable = $this->ext_fact_tables[$relation];
    return $this->checkRelation($reltable, $instances);
  }


  protected function checkInfered($relation, array $instances) {
    if(!isset($this->if_tables[$relation])) return FALSE;
    $reltable = $this->if_tables[$relation];
    return $this->checkRelation($reltable, $instances);
  }

  protected function checkNegated($relation, array $instances) {
    if(!isset($this->ne_tables[$relation])) return FALSE;
    $reltable = $this->ne_tables[$relation];
    return $this->checkRelation($reltable, $instances);
  }

  protected function isRule($relation_name) { return isset($this->rule_table[$relation_name]); }

  protected function instance_generator_for_rule($term) {

    // TODO: if no rule found ??

    $var_list = new FList($term->getVariables());

    $candidates_input = $var_list->foldl(function($acc, $var) {
      if($var->hasValue()) $acc[] = array($var->getValue());
      else                 $acc[] = $this->instances->toArray();
      return $acc;
    }, array());

    return cartesian_product_gen($candidates_input); // yield array(i1, i2, i3, ...) generator
  }

  protected function instance_generator_for_fact($term) {
    $var_list = new FList($term->getVariables());

    // TODO: if no fact table found
    $fact_table = $this->fact_tables[$term->getName()];
    $ext_fact_table = isset($this->ext_fact_tables[$term->getName()]) ? $this->ext_fact_tables[$term->getName()]
                                                                      : $fact_table->getEmptyClone();

    $select_pattern = $var_list->foldl(function($acc, $var) {
      if($var->hasValue()) $acc[$var->getName()] = $var->getValue();
      return $acc;
    }, array());

    // if select_pattern == array(), all record of this fact table are selected.
    $instance_table = $fact_table->select($select_pattern);
    //$instance_table->view();
    $ext_records = $ext_fact_table->select($select_pattern)->records();
    $instance_table->insert_array($ext_records, TRUE, TRUE);
    //$instance_table->view();

    // if $instance_table is EMPTY, throw Exception.
    if($instance_table->isEmpty()) throw new LogicException('KBase::instance_generator_for_fact(): no candidates available.');

    return $instance_table->tuples_gen();
/*
    $candidates_input = $var_list->foldl(function($acc, $var) use ($instance_table) {
      if($var->hasValue()) {
        $acc[] = array($var->getValue());
      } else {
        $acc[] = $instance_table->valuesOfColumn($var->getName());
      }
      return $acc;
    }, array());

    return cartesian_product_gen($candidates_input); // yield array(i1, i2, i3, ...) generator
*/
  }

  private function eval_term(Term $term) {
    $rel = $term->getName();
    $vars = $term->getVariables(); // $vars: [Variable]

    // all variables have value, no need to generate/iterate candidates
    if($term->readyForResolve()) { return $this->isTrue($rel, $term->getInstances()); }

    /* ---------Have to fill the variables with a candidate value ----------- */
    try {
      if($this->isRule($rel)) $candidates = $this->instance_generator_for_rule($term);
      else                    $candidates = $this->instance_generator_for_fact($term);
    } catch(Exception $e) {
      //echo "no candidates for: \n"; echo "  " . $term->toString() . "\n";
      return FALSE;
    }

    foreach($candidates as $_instances) {
      //echo $term->toString() . "\n";
      //print_r($_instances);

      $term->assignValues($_instances); // if $term is fact, isTrue() will not assign value for
                                        // variables, it could make wrong variable value for next
                                        // term. for example:
                                        // hasDesc(A, C) :- hasChild(A, B) o hasDesc(A, C)
                                        // assign A = 'x', C = 'z'
                                        // hasDesc('x', 'z') :- hasChild('x', ?) o hasDesc(?, 'z')
                                        // assign ? = 'y'
                                        // hasDesc('x', 'z') :- hasChild('x', 'y') o hasDesc('y', 'z')
                                        // if we don't assigin value to ?
                                        // hasDesc(?, 'z') will be evaulated
                                        // the candicate of ? could be ['x', 'y', 'z'], then
                                        // hasDesc('x', 'z') cause infinite loop

      if($this->isTrue($rel, $_instances)) {
        // recursive eval next, if return true, return true
        // if next term false, continue the iteration
        if(NULL == $term->next())           return TRUE;  // no more for eval
        if($this->eval_term($term->next())) return TRUE;
      }
    }
    return FALSE; // all candidate are false.
  }

  // resolve the rule
  // $instances contains no question mark
  protected function resolveRule($rule_name, array $instances) {

    if(!isset($this->rule_table[$rule_name])) return FALSE;

    //echo $rule_name . "\n";
    //print_r($instances);

    $same_head_rules = (new FList($this->rule_table[$rule_name]))->map(function($rule) {
      return $rule->getClone();
    });

    $eval_rule = function($rule) use ($instances) {
      $rule->reset();
      $head = $rule->getHead(); // $head: Term
      $body = $rule->getBody(); // $body: FList[Term]

      $head->assignValues($instances); // assign values, the body will be assigned as well.

      //echo "after asiginment: \n";
      //echo "  " .  $rule->toString() . "\n";

      $first_term = $body->head();

      return $this->eval_term($first_term);
    };

    return $same_head_rules->some($eval_rule);
  }

  // check if $instances are related using the $relation.
  // $pattern contains no question mark
  public function isTrue($relation, array $instances) {

    // check negated table
    if($this->checkNegated($relation, $instances)) return FALSE;

    // check fact
    if($this->checkFact($relation, $instances)) return TRUE;

    // check ext fact
    if($this->checkExtFact($relation, $instances)) return TRUE;

    // check infered relation table
    if($this->checkInfered($relation, $instances)) return TRUE;

    // resolve rule
    if($this->resolveRule($relation, $instances)) {
      // if resolve success, write it into $this->if_tables
      $this->addInfered($relation, $instances);
      return TRUE;
    }

    $this->addNegated($relation, $instances);
    return FALSE;
  }

  protected function hasFact($relation) { return isset($this->fact_tables[$relation]); }
  protected function hasRule($relation) { return isset($this->rule_table[$relation]); }


  /*
   * query('hasChild', array('a', '?')), return array( array('b'), array('c') )
   * query('hasChild', array('?', '?')), return array( array('a','b'), array('a','c') )
   * query('hasChild', array('a', 'b')), return boolean
   */
  public function query($relation, array $instances ) {
    $term = term($relation);
    $letters = up_letter_gen();

    $to_project = array();
    foreach($instances as $i) {
      if(strcmp($i, '?') == 0) {
        $to_project[] = $letters->current();
        $term->addVariableByName($letters->current());
      } else {
        $v = new Variable($letters->current());
        $v->setValue($i);
        $term->addVariable($v);
      }
      $letters->next();
    }

    // all variables have value, no need to generate/iterate candidates
    if($term->readyForResolve()) { return $this->isTrue($relation, $term->getInstances()); }

    /* ---------Have to fill the variables with a candidate value ----------- */
    try {
      if($this->isRule($relation)) $candidates = $this->instance_generator_for_rule($term);
      else                         $candidates = $this->instance_generator_for_fact($term);
    } catch(Exception $e) {
      echo "no candidates for: \n"; echo "  " . $term->toString() . "\n";
      return array();
    }

    //foreach($candidates as $i) print_r($i);

    $candidate_table = $this->getRelationTable($term->arity());

    foreach($candidates as $_instances) {
      if($this->isTrue($relation, $_instances)) {
        $candidate_table->insert_row_array($_instances, TRUE);
      }
    }

    return $candidate_table->project($to_project)->tuples();
  }
}

?>
