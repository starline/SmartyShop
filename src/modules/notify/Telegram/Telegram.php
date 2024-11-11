<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 *
 */

use GoodGin\GoodGin;
use NotexyBot\NotexyBot;
use Longman\TelegramBot\DB;

class Telegram extends GoodGin
{
    /**
     * Send Message to telegram Chat
     * @param string $message
     * @param array $message_params
     *
     */
    public function send(String $message, array $params)
    {

        if (!empty($params['user']->te_chat_id) and empty($params['chat_id'])) {
            $params['chat_id'] = $params['user']->te_chat_id;
        }

        if (empty($params['chat_id']) || empty($message) || empty($params['api_key']) || empty($params['bot_username']) || empty($params['database'])) {
            return false;
        }

        // Set default [parse_mode]
        if (empty($params['parse_mode'])) {
            $params['parse_mode'] = 'HTML';
        }

        // TelegramBot initialization
        $TelegrtamBot = new NotexyBot();
        $TelegrtamBot->debugOn();
        $TelegrtamBot->setUserConfig([
            'api_key' =>  $params['api_key'],
            'bot_username' => $params['bot_username'],
            'mysql'        => [
                'host'     => $this->Config->db_server,
                'user'     => $this->Config->db_user,
                'password' => $this->Config->db_password,
                'database' => $params['database']
            ]
        ]);
        $TelegrtamBot->initialize();

        return $result = $TelegrtamBot->sendMessage($params, $message);
    }
}
