<?php

// Composer
require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use GoodGin\GoodGin;
use Bot\BotCore;
use NotexyBot\NotexyBot;

$GoodGin = new Goodgin();

try {

	$notify_settings = $GoodGin->UsersNotify->getNotifySettings('Telegram');

	$Bot = new NotexyBot();
	$Bot->debugOn();
	$Bot->setUserConfig([
		'api_key' =>  $notify_settings->api_key,
		'bot_username' =>  $notify_settings->bot_username,
		'admins' => [$notify_settings->admins],
		'webhook' => [
			'url' => $GoodGin->Config->root_url . '/exchange/telegram/webhook.php',
			'secret_token' =>  $notify_settings->secret_token,
			'max_connections' => 10
		],
		'mysql'        => [
			'host'     => $GoodGin->Config->db_server,
			'user'     => $GoodGin->Config->db_user,
			'password' => $GoodGin->Config->db_password,
			'database' =>  $notify_settings->database
		]
	]);
    

    // If admin
    if ($GoodGin->Users->checkUserAccess($GoodGin->user, 'settings') === true) {

        // Проверка сессии для защиты от xss
        if (!$GoodGin->Request->check_session()) {
            trigger_error('Session expired', E_USER_WARNING);
            exit();
        }

        $Bot->run(true);
    } else {
        $Bot->run(false);
    }

} catch (\Throwable $e) {

    // Prevent Telegram from retrying
    print 'Caught exception: ' . $e->getMessage() . PHP_EOL;
}
