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


// В день
//
if ($GoodGin->Request->get('filter', 'string') == 'byDay') {

    // Заказы
    // request_type
    // totalPrice - выручка
    // profitPrice - прибыль
    // amout - кол-во заказов
    $result = $GoodGin->Statistics->ordersSum($request_type, 'byDay', $from_date, $to_date);
}

// В месяц
//
elseif ($GoodGin->Request->get('filter')  == 'byMonth') {


    // Для  опред. товара
    if (!empty($product_id = $GoodGin->Request->get('product_id', 'integer'))) {

        // Поставка или списание со склада
        // add - поставка
        // delete - списание
        if ($request_type == 'add' || $request_type == 'delete') {
            $result = $GoodGin->Statistics->productWarehouseMovemetByMonth($product_id, $request_type);
        }

        // totalPrice - Сумма выручки
        // profitPrice -  Сумма пртбыли
        // amount - Кол-во проданых
        elseif ($request_type == 'totalPrice' || $request_type == 'profitPrice' || $request_type == 'amount') {
            $result = $GoodGin->Statistics->productByMonth($product_id, $request_type);
        }
    }


    // Для  опред. категории
    elseif (!empty($category_id = $GoodGin->Request->get('category_id', 'integer'))) {

        // totalPrice - Сумма выручки
        // profitPrice -  Сумма пртбыли
        // amount - Кол-во проданых
        if ($request_type == 'totalPrice' || $request_type == 'profitPrice' || $request_type == 'amount') {
            $result = $GoodGin->Statistics->productsCategoryByMonth($category_id, $request_type);
        }
    }


    // Для опред. менеджера
    elseif (!empty($manager_id = $GoodGin->Request->get('manager_id', 'integer'))) {

        // totalPrice - Общая сумма комиссии менеджера
        // amount - кол-во обработаных заказов
        if ($request_type == 'totalPrice' || $request_type == 'amount') {
            $result = $GoodGin->Statistics->managerOrdersByMonth($manager_id, $request_type);
        }

        // Платежи менеджеру (траты)
        elseif ($request_type == 'totalPayments') {
            $user_rel_payments = $GoodGin->Finance->get_user_payments($manager_id);

            $payments_ids = [];
            foreach ($user_rel_payments as $urp) {
                $payments_ids[] = $urp->payment_id;
            }

            if (!empty($payments_ids)) {
                $result = $GoodGin->Statistics->financeByMonth(array('payments_ids' => $payments_ids, 'type' => 'minus'));
            }
        }
    }


    // Заказы
    // totalPrice - выручка
    // profitPrice - прибыль
    // amout - кол-во заказов
    else {
        $filters = array();
        if ($GoodGin->Request->get('paymentMethod', 'integer')) {
            $filters['payment_method_id'] = $GoodGin->Request->get('paymentMethod', 'integer');
        }

        $result = $GoodGin->Statistics->ordersSum($request_type, "byMonth", null, null, $filters);
    }
}

// Выводим
header("Content-type: application/json; charset=utf-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($result);
