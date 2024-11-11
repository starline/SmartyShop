<?php

session_start();
define('secure', 'true');

// Composer
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
require_once  dirname(__DIR__) . '/view/Auth.php';
$GoodGin = new Auth();

if (!$GoodGin->access(['orders', 'finance'])) {
    die("Access denied");
}

$filter['limit'] = 50;

// Поиск ('string' - сжирает запятые? не исполььзуем)
$keyword = $GoodGin->Request->get('query');
if (!empty($keyword)) {
    $filter['keyword'] = $keyword;
}

// Сортировка
$sort = $GoodGin->Request->get('sort', 'string');
if (!empty($sort)) {
    $filter['sort'] = $sort;
}

$users = $GoodGin->Users->getUsers($filter);

$suggestions = array();
foreach ($users as $user) {
    $suggestion = new stdClass();
    $suggestion->value = $user->name;
    if (!empty($user->phone)) {
        $suggestion->value  .= 	" ($user->phone)";
    }
    $suggestion->data = $user;
    $suggestions[] = $suggestion;
}

$res = new stdClass();
$res->query = $keyword;
$res->suggestions = $suggestions;


header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($res);
