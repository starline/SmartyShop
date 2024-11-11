<?php

/**
 * Сначало проверяетя есть ли уже файл на сервере через Ngix
 *
 * @author Andi Huga
 * @version 2.2
 */

// Composer
require dirname(__DIR__) . '/vendor/autoload.php';
use GoodGin\GoodGin;

$GoodGin = new GoodGin();

// Если нет входных  данных
if (empty($_GET['file']) || empty($_GET['token'])) {
    die("Access denied");
}

$filename = urldecode($_GET['file']);
$token = $_GET['token'];

if (!$GoodGin->Misc->checkToken($filename, $token)) {
    header("http/1.0 404 not found");
    print 'File not found. Bad token';
    exit();
}

$resized_filename = $GoodGin->Images->resize($filename);

if (is_readable($resized_filename)) {
    header('Content-type: image');
    print file_get_contents($resized_filename);
}
