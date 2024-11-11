<?php

session_start();
define('secure', 'true');

// Composer
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
require_once  dirname(__DIR__) . '/view/Auth.php';
$GoodGin = new Auth();

$category_id = $GoodGin->Request->get('category_id', 'integer');
$product_id = $GoodGin->Request->get('product_id', 'integer');

if (!empty($category_id)) {
    $features = $GoodGin->ProductsFeatures->get_features(array('category_id' => $category_id));
} else {
    $features = $GoodGin->ProductsFeatures->get_features();
}

$options = array();
if (!empty($product_id)) {
    $opts = $GoodGin->ProductsFeatures->get_product_options($product_id);
    foreach ($opts as $opt) {
        $options[$opt->feature_id] = $opt;
    }
}

foreach ($features as &$f) {
    if (isset($options[$f->id])) {
        $f->value = $options[$f->id]->value;
    } else {
        $f->value = '';
    }
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($features);
