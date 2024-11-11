<?php

chdir(dirname(__DIR__));

// Засекаем время
$time_start = microtime(true);
session_start();

$_SESSION['id'] = session_id();

@ini_set('session.gc_maxlifetime', 86400); // 86400 = 24 часа
@ini_set('session.cookie_lifetime', 0); // 0 - пока браузер не закрыт

// Кеширование в админке нам не нужно
Header("Cache-Control: no-cache, must-revalidate");
header("Expires: -1");
Header("Pragma: no-cache");

// Set security parametre for ViewAdmin files
define('secure', 'true');

// Composer
require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

// Agmin
require_once(__DIR__ . '/view/IndexAdmin.php');
$GoodGin = new IndexAdmin();


/**
 * Проверка сессии для защиты от xss
 * XSS — тип атаки на веб-системы,
 * заключающийся во внедрении в выдаваемую веб-системой страницу вредоносного кода
 * и взаимодействии этого кода с веб-сервером злоумышленника.
 */
if (!$GoodGin->Request->check_session()) {
    unset($_POST);
    trigger_error('Session expired', E_USER_WARNING);
}

header("Content-type: text/html; charset=UTF-8");
print $GoodGin->fetch();


// Отладочная информация
if ($GoodGin->Config->php_debug) {
    print "<!--\r\n";

    $time_end = microtime(true);
    $exec_time = $time_end - $time_start;

    if (function_exists('memory_get_peak_usage')) {
        print "Memory peak usage: " . $GoodGin->Misc->convertBytes(memory_get_peak_usage()) . " \r\n";
    }

    print "Page generation time: " . round($exec_time, 4) . " seconds\r\n";
    print "DB queries count: " . $GoodGin->Database->get_query_count() . " pcs\r\n";
    print "-->";
}
