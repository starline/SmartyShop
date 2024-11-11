<?php

$time_start = microtime(true);

$resources = array(
    'products' => 'RestProducts',
    'orders'   => 'RestOrders',
    'blog'     => 'RestBlog'
);

// api_key = ''
//


// Ресурс с которым будем работать
$resource = $_GET['resource'];

// Если существует соответсвующий класс
if (isset($resources[$resource])) {

    $class_name = $resources[$resource];
    require_once(__DIR__ . "/$class_name.php");
    $rest = new $class_name();

    // Действие с ресурсом
    if ($rest->Request->method('GET')) {
        $result = $rest->get();
    }
    if ($rest->Request->method('POST')) {
        $result = $rest->post();
    }
    if ($rest->Request->method('PUT')) {
        $result = $rest->put();
    }
    if ($rest->Request->method('DELETE')) {
        $result = $rest->delete();
    }

    // Разрешаем доступ к ресурсу с других доменов
    header("Access-Control-Allow-Origin: *");

    // Отдаём результат
    print json_encode($result);
} else {
    // Еслиь не существует соответсвующий класс
    header("HTTP/1.0 404 Not Found");
    exit();
}


// Отладка
$time_end = microtime(true);
$exec_time = round(($time_end - $time_start) * 1000, 0);
//print "[$exec_time ms]";
