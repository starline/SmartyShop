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

$result = "";
$request_type = $GoodGin->Request->post('request_type');

// Выбираем информацию о доставке НовояПочта
if ($request_type == 'checkTracking') {

    // Выбрать данные заказа
    $order_id = intval($GoodGin->Request->post('id'));
    $module_name = $GoodGin->Request->post('module');
    $module_path = $GoodGin->Config->delivery_dir . "$module_name/$module_name.php";

    if (!empty($module_name) && is_file($module_path)) {
        include_once($module_path);
        $module = new $module_name();
        $result = $module->get_delivery_info($order_id);
    }
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($result);
