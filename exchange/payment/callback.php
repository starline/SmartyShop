<?php

// Composer
require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
use GoodGin\GoodGin;

$GoodGin = new GoodGin();

$payment_name = $GoodGin->Request->get('payment_name', 'string');

if (!empty($payment_name)) {
    require_once($GoodGin->Config->payment_dir . $payment_name . "/$payment_name.php");

    $Payment = new $payment_name();
    $Payment->callback();
}
