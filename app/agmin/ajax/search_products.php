<?php

session_start();
define('secure', 'true');

// Composer
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
require_once  dirname(__DIR__) . '/view/Auth.php';
$GoodGin = new Auth();

if (!$GoodGin->access(array('orders', 'products_price', 'warehouse_add', 'warehouse_edit'))) {
    die("Access denied");
}


// Поиск (без 'string' - сжирает запятые)
$keyword = $GoodGin->Request->get('query');
if (!empty($keyword)) {
    $filter['keyword'] = $keyword;
}

$filter['limit'] = 100;

$products = $GoodGin->Products->get_products($filter, array("image"));

$variants = array();
if (!empty($products)) {
    $variants = $GoodGin->ProductsVariants->getVariants(array('product_id' => array_keys($products)));
}

foreach ($variants as $variant) {
    $variant->movements = $GoodGin->Warehouse->get_product_movements($variant->id);
    $mov_amount = 0;
    foreach ($variant->movements as $mov) {
        $mov_amount += $mov->amount;
    }
    $variant->movements_amount = $mov_amount;

    if (isset($products[$variant->product_id])) {
        $products[$variant->product_id]->variants[] = $variant;
    }
}

$suggestions = array();
foreach ($products as $product) {
    if (!empty($product->variants)) {
        $suggestion = new stdClass();
        if (!empty($product->image_filename)) {
            $product->image = $GoodGin->Design->resize_modifier($product->image_filename, 50, 50);
        }
        $suggestion->value = $product->name;
        $suggestion->data = $product;
        $suggestions[] = $suggestion;
    }
}

$res = new stdClass();
$res->query = $keyword;
$res->suggestions = $suggestions;


header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($res);
