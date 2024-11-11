<?php

// Composer
require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
use GoodGin\GoodGin;

class Rest extends GoodGin
{
    public function __construct()
    {
        parent::__construct();
    }
}
