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

$orders = $GoodGin->Orders->getOrders($filter);

$suggestions = array();
foreach ($orders as $order) {
    $suggestion = new stdClass();
    $suggestion->value = 'Заказ №' . $order->id;
    $suggestion->data = $order;
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
