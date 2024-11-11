<?php

session_start();

// Composer
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
use GoodGin\GoodGin;

$GoodGin = new GoodGin();

// Поиск (без 'string' - сжирает запятые)
$keyword = $GoodGin->Request->get('query');
if (!empty($keyword)) {
    $filter['keyword'] = $keyword;
}

$filter['limit'] = 30;
$filter['visible'] = 1;

$products = $GoodGin->Products->get_products($filter, array("image"));
$suggestions = array();

foreach ($products as $product) {
    $suggestion = new stdClass();
    if (!empty($product->image_filename)) {
        $product->image = $GoodGin->Design->resize_modifier($product->image_filename, 35, 35);
    }

    $suggestion->value = $product->name;
    $suggestion->data = $product;
    $suggestions[] = $suggestion;
}

if (count($suggestions) == 0) {
    $suggestion = new stdClass();
    $suggestion->value = 'По вышему запрос не найдено товаров';
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
