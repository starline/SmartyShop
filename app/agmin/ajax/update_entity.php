<?php

session_start();
define('secure', 'true');

// Composer
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
require_once  dirname(__DIR__) . '/view/Auth.php';
$GoodGin = new Auth();

// Проверка сессии для защиты от xss
if (!$GoodGin->Request->check_session()) {
    trigger_error('Session expired', E_USER_WARNING);
    exit();
}

$result = new stdClass();

$id = intval($GoodGin->Request->post('id'));
$entity = $GoodGin->Request->post('entity');
$values = $GoodGin->Request->post('values');

switch ($entity) {
    case 'product':
        if ($GoodGin->access('products')) {
            $result = $GoodGin->Products->update_product($id, $values);
        }
        break;
    case 'category':
        if ($GoodGin->access('products_categories')) {
            $result = $GoodGin->ProductsCategories->update_category($id, $values);
        }
        break;
    case 'brands':
        if ($GoodGin->access('products_brands')) {
            $result = $GoodGin->ProductsBrands->update_brand($id, $values);
        }
        break;
    case 'feature':
        if ($GoodGin->access('products_features')) {
            $result = $GoodGin->ProductsFeatures->updateFeature($id, $values);
        }
        break;
    case 'page':
        if ($GoodGin->access('pages')) {
            $result = $GoodGin->Pages->update_page($id, $values);
        }
        break;
    case 'blog':
        if ($GoodGin->access('blog')) {
            $result = $GoodGin->Blog->updatePost($id, $values);
        }
        break;
    case 'delivery':
        if ($GoodGin->access('orders_delivery')) {
            $result = $GoodGin->OrdersDelivery->update_delivery($id, $values);
        }
        break;
    case 'payment_method':
        if ($GoodGin->access('orders_payment')) {
            $result = $GoodGin->OrdersPayment->updatePaymentMethod($id, $values);
        }
        break;
    case 'currency':
        if ($GoodGin->access('finance')) {
            $result = $GoodGin->Money->updateCurrency($id, $values);
        }
        break;
    case 'comment':
        if ($GoodGin->access('comments')) {
            $result = $GoodGin->Comments->updateComment($id, $values);
        }
        break;
    case 'user':
        if ($GoodGin->access('users')) {
            $result = $GoodGin->Users->updateUser($id, $values);
        }
        break;
    case 'label':
        if ($GoodGin->access('orders_labels')) {
            $result = $GoodGin->OrdersLabels->update_label($id, $values);
        }
        break;
    case 'order':
        if ($GoodGin->access('orders')) {
            $result = $GoodGin->Orders->update_order($id, $values);
        }
        break;
    case 'purse':
        if ($GoodGin->access('finance')) {
            $result = $GoodGin->Finance->update_purse($id, $values);
        }
        break;
    case 'payment':
        if ($GoodGin->access('finance')) {
            if (!empty($values['verified'])) {
                $values['verified_user_id'] = $GoodGin->user->id;
            }
            $result = $GoodGin->Finance->update_payment($id, $values);
        }
        break;
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($result);
