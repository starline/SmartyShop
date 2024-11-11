<?php

use Bot\BotCore;

/**
 * Handle webhook request only when it's a POST request
 */
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../vendor/autoload.php';

    try {

        $app = new BotCore();
        $app->run(true);

    } catch (\Throwable $e) {
        // Prevent Telegram from retrying
    }

} elseif (isset($_SERVER['GAE_VERSION']) && $_SERVER['PATH_INFO'] === '/admin') {
    require_once __DIR__ . '/../vendor/autoload.php';

    $app = new BotCore();
    $app->run();

} else {
    echo 'no commands';
}
