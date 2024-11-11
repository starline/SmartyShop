<?php

session_start();
define('secure', 'true');

// Composer
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
require_once  dirname(__DIR__) . '/view/Auth.php';
$GoodGin = new Auth();

$filter['limit'] = 100;

// Поиск (без 'string' - сжирает запятые)
$keyword = $GoodGin->Request->get('query');
if (!empty($keyword)) {
    $filter['keyword'] = $keyword;
}

$movements = $GoodGin->Warehouse->get_movements($filter);

$suggestions = array();
foreach ($movements as $movement) {
    $suggestion = new stdClass();
    $suggestion->value = 'Перемещение №' . $movement->id;
    $suggestion->data = $movement;
    $suggestions[] = $suggestion;
}

$res = new stdClass();
$res->query = $keyword;
$res->suggestions = $suggestions;


header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($res);
