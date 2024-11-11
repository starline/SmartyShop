<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author 	Andi Huga
 *
 */

// Засекаем время
$time_start = microtime(true);
session_start();

// Set security parametre for View files
define('secure', 'true');

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/public/view/View.php';
$GoodGin = new View();

$GoodGin->Config->setTemplateSubdir('templates/');
$GoodGin->Design->setTemplateDir($GoodGin->Config->root_dir . $GoodGin->Config->templates_subdir . $GoodGin->Settings->theme . '/html');

// Делаем выход из системы
if (isset($_GET['logout'])) {
    header('WWW-Authenticate: Basic realm="GoodGin CMS"');
    header('HTTP/1.0 401 Unauthorized');
    unset($_SESSION['admin']);
}

// Пропускаем только допустимые знаки
// PHP не видит anchor #
$original_uri = $_SERVER["REQUEST_URI"]; // C символом "/" в начале
$original_path = parse_url($original_uri, PHP_URL_PATH);

// p{L} - буква любого языка, p{Nd} - Десятичная цифра, \d - числовой символ (тоже, что [0-9])
// Преобразовываем все буквы в нижний регистр. Убираем недопустимые знаки с URL_PATH: _№#!?$@ и т.д.
$clean_path = preg_replace('/[^\p{L}\d\-\/\.]/ui', '', strtolower($original_path));

$original_query = parse_url($original_uri, PHP_URL_QUERY);

$clean_uri = $clean_path;
if (!empty($original_query)) {
    $clean_uri .= '?' . $original_query;
}

// Если есть недопустимые знаки в path, делаем 301 редирект
if ($original_path != $clean_path) {
    $GoodGin->Misc->makeRedirect($clean_uri, '301');
}


// Если все хорошо: 200
if (($res = $GoodGin->fetch()) !== false) {

    // Выводим результат
    header("Content-type: text/html; charset=UTF-8");
    print $res;

    // Если такой страницы нет: 404
} else {

    // Иначе страница об ошибке
    header("http/1.0 404 not found");

    // Подменим переменную GET, чтобы вывести страницу 404
    $_GET['page_url'] = '404';
    $_GET['view'] = 'PageView';
    print $GoodGin->fetch();
}


// Отладочная информация
if ($GoodGin->Config->php_debug) {
    print "<!--\r\n";

    $time_end = microtime(true);
    $exec_time = $time_end - $time_start;

    if (function_exists('memory_get_peak_usage')) {
        print "Memory peak usage: " . $GoodGin->Misc->convertBytes(memory_get_peak_usage()) . " bytes\r\n";
    }

    print "Page generation time: " . round($exec_time, 4) . " seconds\r\n";
    print "DB queries count: " . $GoodGin->Database->get_query_count() . " pcs\r\n";
    print "-->";
}
