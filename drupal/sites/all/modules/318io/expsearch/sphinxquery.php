<?php
require_once "sphinxapi-2.0.4.php";

$cl = new SphinxClient();

#$cl->SetServer(’192.168.1.150′, 9312); //注意这里的主机
#$cl->SetMatchMode(SPH_MATCH_EXTENDED); //使用多字段模式

$cl->SetLimits(0, 1);
$query_string = "詩婷";
$result = $cl->Query($query_string);
$total = intval($result['total_found']);
echo $total;
$cl->SetLimits(0,$total);
$result = $cl->Query($query_string);


#$err = $cl->GetLastError();

var_dump($result);
#var_dump($err);


