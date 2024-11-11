<?php

session_start();
define('secure', 'true');

// Composer
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
require_once  dirname(__DIR__) . '/view/Auth.php';
$GoodGin = new Auth();

$limit = 100;
$keyword = $GoodGin->Database->escape($GoodGin->Request->get('query', 'string'));
$feature_id = $GoodGin->Request->get('feature_id', 'integer');

$options = $GoodGin->ProductsFeatures->getOptions(array("feature_id" => $feature_id, "keyword" => $keyword, "limit" => $limit));

$options_value = array();
foreach ($options as $op) {
    $options_value[] = $op->value;
}

$res = new stdClass();
$res->query = $keyword;
$res->suggestions = $options_value;

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($res);
