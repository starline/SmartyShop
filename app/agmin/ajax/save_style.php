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

if (!$GoodGin->access('design')) {
    return false;
}

$content = $GoodGin->Request->post('content', 'string');
$style = $GoodGin->Request->post('style', 'string');
$theme = $GoodGin->Request->post('theme', 'string');

if (pathinfo($style, PATHINFO_EXTENSION) != 'css') {
    exit();
}

$file = $GoodGin->Config->root_dir . 'templates/' . $theme . '/css/' . $style;

if (is_file($file) && is_writable($file) && !is_file($GoodGin->Config->root_dir . 'templates/' . $theme . '/locked')) {
    file_put_contents($file, $content);
    $result = true;
} else {
    $result = false;
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($result);
