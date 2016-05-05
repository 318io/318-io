<?php

$subject = "taxo_test dfasdf";
$pattern = '/^taxo_(.*)/';
preg_match($pattern, $subject, $matches);
print_r($matches);

?>