<?php

session_start();

// Composer
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
use GoodGin\GoodGin;

$GoodGin = new GoodGin();

$GoodGin->Config->setTemplateSubdir('templates/');
$GoodGin->Design->setTemplateDir($GoodGin->Config->root_dir . $GoodGin->Config->templates_subdir . $GoodGin->Settings->theme . '/html');

// Add product to cart
$GoodGin->Cart->addCartProduct($GoodGin->Request->get('variant', 'integer'), $GoodGin->Request->get('amount', 'integer'));

$cart = $GoodGin->Cart->getCart();

if (isset($_SESSION['currency_id'])) {
    $currency = $GoodGin->Money->getCurrency($_SESSION['currency_id']);
} else {
    $currency = $GoodGin->Money->getMainCurrency();
}

$GoodGin->Design->assign('currency', $currency);
$GoodGin->Design->assign('cart', $cart);

$result = $GoodGin->Design->fetch('parts/cart_informer.tpl');

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($result);
