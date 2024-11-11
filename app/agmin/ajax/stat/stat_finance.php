<?php

session_start();
define('secure', 'true');

// Composer
require dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
require_once  dirname(dirname(__DIR__)) . '/view/Auth.php';
$GoodGin = new Auth();

$result = null;
$request_type = $GoodGin->Request->get('type', 'string');

$today = date("d.m.Y");
if (!$from_date = $GoodGin->Request->get('fromDate')) {
    $from_date = null;
}

if (!$to_date = $GoodGin->Request->get('toDate')) {
    $to_date = null;
}


// В месяц
//
if ($GoodGin->Request->get('filter', 'string')  == 'byMonth') {

    $category_id = null;
    if (!empty($GoodGin->Request->get('category_id', 'integer'))) {
        $category_id = $GoodGin->Request->get('category_id', 'integer');
    }

    // Для опред. кошелка
    // plus - приход
    // minus - расход
    if (!empty($purse_id = $GoodGin->Request->get('purse_id', 'integer'))) {
        $result = $GoodGin->Statistics->financeByMonth(array("purse_id" => $purse_id, "type" => $request_type, "category_id" => $category_id));
    }


    // Общий финансовый график.
    // Убираем из дaнных переводы между кошельками related_payment_id = "NULL"
    // plus - приход
    // minus - расход
    else {
        $result = $GoodGin->Statistics->financeByMonth(array("related_payment_id" => "NULL", "type" => $request_type, "category_id" => $category_id));
    }
}


// Выводим
header("Content-type: application/json; charset=utf-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($result);
