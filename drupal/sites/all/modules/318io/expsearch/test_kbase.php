<?php

require_once "reasoner.php";

$kb = new KBase();


$kb->addFact("hasChild", array('A', 'B'));
$kb->addFact("hasChild", array('A', 'C'));
$kb->addFact("hasChild", array('B', 'I'));
$kb->addFact("hasChild", array('C', 'D'));
$kb->addFact("hasChild", array('E', 'F'));
$kb->addFact("hasChild", array('E', 'G'));
$kb->addFact("hasChild", array('H', 'A'));


$kb->addRule(chain('hasDesc', array('hasChild')));
$kb->addRule(chain('hasDesc', array('hasChild', 'hasDesc')));

$kb->addRule(grule(term('hasAnc', 'A', 'B'), 
                   array(term('hasDesc', 'B', 'A'))
                  )
            );

if($kb->isTrue('hasAnc', array('D', 'H'))) echo "true\n";
else echo "false\n";

$kb->setEqual('H', 'E');
/*
H = E
hasChild E A
hasChild H F
hasChild H G
hasChild H A
*/

if($kb->isTrue('hasAnc', array('D', 'E'))) echo "true\n";
else echo "false\n";

if($kb->isTrue('hasDesc', array('E', 'A'))) echo "true\n";
else echo "false\n";


$result = $kb->query('hasChild', array('A', '?'));
print_r($result);

$result = $kb->query('hasAnc', array('D', '?'));
print_r($result);
