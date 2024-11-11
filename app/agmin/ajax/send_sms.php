<?php

session_start();
define('secure', 'true');

// Composer
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
require_once  dirname(__DIR__) . '/view/Auth.php';
$GoodGin = new Auth();

if (!$GoodGin->access('orders')) {
    die("Access denied");
}

// Проверка сессии для защиты от xss
if (!$GoodGin->Request->check_session()) {
    trigger_error('Session expired', E_USER_WARNING);
    print 'Session expired';
    exit();
}

$id = intval($GoodGin->Request->post('id', 'integer'));

// Выбрать данные заказа
$order = $GoodGin->Orders->getOrder(intval($id));

$result["result"]  = 'no';
if (!empty($order->phone)) {


    // смс с трекномером доставки
    if ($GoodGin->Request->post('type') == 'delivery') {
        if (!empty($order->delivery_note)) {

            $sms_result = $GoodGin->UsersNotify->sendNotify('Turbosms', 'deliveryTrackNumber', [
                'order_id' => $order->id
            ]);

            if (!empty($sms_result['status'])) {
                if ($sms_result['status'] == 'Сообщения успешно отправлены') {

                    // Отмечаем, что смс доставлено. Подсчитываем сколько раз отправили SMS
                    $order->settings->delivery_sms = (!isset($order->settings->delivery_sms)) ? 1 : $order->settings->delivery_sms + 1;
                    $order_settings = serialize((array)$order->settings);
                    $result["result"] = $GoodGin->Orders->update_order($id, array('settings' => $order_settings), false);
                } else {
                    $result["result"] = "error: " . $sms_result['status'];
                }
            } else {
                $result["result"] = "Not delivered";
            }
        } else {
            $result["result"] = "Empty delivery_note";
        }
    }


    // смс с реквизитами оплаты
    elseif ($GoodGin->Request->post('type') == 'payment') {
        if (!empty($order->payment_method_id)) {

            // Отправляем СМС
            $sms_result = $GoodGin->UsersNotify->sendNotify('Turbosms', 'paymentDetails', [
                'order_id' => $order->id
            ]);

            if (!empty($sms_result['status'])) {
                if ($sms_result['status'] == 'Сообщения успешно отправлены') {

                    // Отмечаем, что смс доставлено. Подсчитываем сколько раз отправили SMS
                    $order->settings->payment_sms = (!isset($order->settings->payment_sms)) ? 1 : $order->settings->payment_sms + 1;
                    $order_settings = serialize((array)$order->settings);
                    $result["result"] = $GoodGin->Orders->update_order($id, array('settings' => $order_settings), false);
                } else {
                    $result["result"] = "error: " . $sms_result['status'];
                }
            }
        } else {
            $result["result"] = "empty payment_method_id";
        }
    }


    // Если не указан type
    else {
        $result["result"] = "empty type";
    }
} else {
    $result["result"] = "empty phone";
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($result);
