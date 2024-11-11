<?php

namespace NotexyBot;

use NotexyBot\Helper\Utilities;
use Exception;
use GuzzleHttp\Client;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\TelegramLog;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\DB;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Throwable;
use jacklul\MonologTelegramHandler\TelegramFormatter;
use jacklul\MonologTelegramHandler\TelegramHandler;

define("ROOT_PATH", realpath(dirname(__DIR__)));
define("SRC_PATH", ROOT_PATH . '/src');

/**
 * This is the master loader class, contains console commands and essential code for bootstrapping the bot
 */
class NotexyBot
{
    /**
     * Commands
     * @var array
     */
    private static $commands = [
        'help'    => [
            'function'         => 'showHelp',
            'description'      => 'Shows this help message',
        ],
        'set'     => [
            'function'         => 'setWebhook',
            'description'      => 'Set the webhook',
            'require_telegram' => true,
        ],
        'unset'   => [
            'function'         => 'deleteWebhook',
            'description'      => 'Delete the webhook',
            'require_telegram' => true,
        ],
        'info'    => [
            'function'         => 'webhookInfo',
            'description'      => 'Print webhookInfo request result',
            'require_telegram' => true,
        ],
        'handle'  => [
            'function'         => 'handleWebhook',
            'description'      => 'Handle incoming webhook update',
            'require_telegram' => true,
        ],
        'cron'    => [
            'function'         => 'handleCron',
            'description'      => 'Run scheduled commands once',
            'require_telegram' => true,
        ],
    ];


    /**
     * Config array
     * @var array
     */
    private $config = [];


    /**
     * Telegram object
     * @var Telegram
     */
    private $telegram;


    /**
     * Bot constructor
     * @param bool $debug Optional: Debug mode (will output a lot of data to logs/console)
     */
    public function __construct(Bool $debug = false)
    {
        if (!defined('ROOT_PATH')) {
            throw new Exception('Root path not defined!');
        }

        // Load environment variables from file if it exists
        if (class_exists(Dotenv::class) && file_exists(ROOT_PATH . '/.env')) {
            $dotenv = Dotenv::createUnsafeImmutable(ROOT_PATH);
            $dotenv->load();
        }

        // Do not display errors by default
        ini_set('display_errors', 0);

        // Debug mode
        if ($debug === true) {
            this->debugOn();
        }

        // Set timezone
        date_default_timezone_set(getenv('TIMEZONE') ?: 'UTC');

        // Set custom data path if variable exists, otherwise do not use anything
        // Optional: Bot data storage path (absolute)
        $data_path = "./data";
        if (!empty($data_path)) {
            define('DATA_PATH', str_replace('"', '', str_replace('./', ROOT_PATH . '/', $data_path)));
        }

        // Load DEFAULT config
        $this->loadDefaultConfig();
    }


    /**
     * Load default config values
     */
    private function loadDefaultConfig(): void
    {
        $this->config = [
            'api_key'      => '', # Bot API token obtained from @BotFather
            'bot_username' => '', # Bot username (without '@' symbol)
            'admins'       => [0], # Optional: Admin's Telegram ID
            'commands'     => [
                'paths' => [
                    SRC_PATH . '/Command',
                ],
            ],
            'webhook' => [
                'url'             => '', # Webhook URL
                'max_connections' => 100,
                'allowed_updates' => [
                    'message',
                    'inline_query',
                    'chosen_inline_result',
                    'callback_query',
                ],
                'secret_token'    => '', # Secret variable used to secure the web hook
            ],
            'mysql' => [
                'host'          => '',
                'user'          => '',
                'password'      => '',
                'database'      => '',
                'table_prefix'  => '',
                'encoding'      => ''
            ],
            'paths' => [
                'download' => DATA_PATH . '/data/download', # (string) Custom download path.
                'upload'   => DATA_PATH . '/data/upload',  # (string) Custom upload path.
            ],
            'logging' => [
                'error'  => DATA_PATH . '/logs/Error.log',
                'debug'  => DATA_PATH . '/logs/Debug.log',
                'update' => DATA_PATH . '/logs/Update.log',
            ],
        ];
    }


    /**
     * Set user configuration
     * @param array $user_config
     */
    public function setUserConfig(array $user_config): void
    {
        $this->config = array_replace_recursive($this->config, $user_config);
    }


    /**
     * Turn ON debuging to admin telegram chat
     */
    public function debugOn(): void
    {
        // Debug mode
        Utilities::setDebugPrint();

        // Display errors
        ini_set('display_errors', 1);
    }


    /**
     * Run the bot
     * @param bool $admin
     */
    public function run(bool $admin = false): void
    {

        // For telegram webspace allow only handling webhook
        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] === 'POST') {
            $arg = 'handle';
        }

        // For command line request
        elseif (isset($_SERVER['argv'][1])) {
            $arg = strtolower(trim($_SERVER['argv'][1]));
        }

        // For GET request. Available only to Admin
        elseif (isset($_GET['a']) and $admin === true) {
            $arg = strtolower(trim($_GET['a']));
        }


        try {

            // Run Telegram Bot command
            if (!empty($arg) && isset(self::$commands[$arg]['function'])) {
                if (!$this->telegram instanceof Telegram && isset(self::$commands[$arg]['require_telegram']) && self::$commands[$arg]['require_telegram'] === true) {
                    $this->initialize();
                }

                $function = self::$commands[$arg]['function'];
                $this->$function();
            }

            // No Command
            else {

                // Show help information. Available only to Admin
                if ($admin === true) {
                    $this->showHelp();
                }

                if (!empty($arg)) {
                    print PHP_EOL . 'Invalid parameter specified!' . PHP_EOL;
                } else {
                    print PHP_EOL . 'No parameter specified!' . PHP_EOL;
                }
            }
        } catch (Throwable $e) {
            $ignored_errors = getenv('IGNORED_ERRORS');

            if (!empty($ignored_errors) && !Utilities::isDebugPrintEnabled()) {
                $ignored_errors = explode(';', $ignored_errors);
                $ignored_errors = array_map('trim', $ignored_errors);

                foreach ($ignored_errors as $ignored_error) {
                    if (strpos($e->getMessage(), $ignored_error) !== false) {
                        return;
                    }
                }
            }

            TelegramLog::error($e);
            throw $e;
        }
    }


    /**
     * Initialize Telegram object
     * @throws TelegramException
     * @throws Exception
     */
    public function initialize(): void
    {
        if ($this->telegram instanceof Telegram) {
            return;
        }

        Utilities::debugPrint('DEBUG MODE ACTIVE');

        $this->telegram = new Telegram($this->config['api_key'], $this->config['bot_username']);
        $monolog = new Logger($this->config['bot_username']);

        if (isset($this->config['logging']['error'])) {
            $monolog->pushHandler((new StreamHandler($this->config['logging']['error'], Logger::ERROR))->setFormatter(new LineFormatter(null, null, true)));
        }

        if (isset($this->config['logging']['debug'])) {
            $monolog->pushHandler((new StreamHandler($this->config['logging']['debug'], Logger::DEBUG))->setFormatter(new LineFormatter(null, null, true)));
        }

        if (isset($this->config['logging']['update'])) {
            $update_logger = new Logger(
                $this->config['bot_username'] . '_update',
                [
                    (new StreamHandler($this->config['logging']['update'], Logger::INFO))->setFormatter(new LineFormatter('%message%' . PHP_EOL)),
                ]
            );
        }

        if (isset($this->config['admins']) && !empty($this->config['admins'][0])) {
            $this->telegram->enableAdmins($this->config['admins']);

            $handler = new TelegramHandler($this->config['api_key'], (int)$this->config['admins'][0], Logger::ERROR);
            $handler->setFormatter(new TelegramFormatter());

            $handler = new DeduplicationHandler($handler);
            $handler->setLevel(Utilities::isDebugPrintEnabled() ? Logger::DEBUG : Logger::ERROR);

            $monolog->pushHandler($handler);
        }

        if (!empty($monolog->getHandlers())) {
            TelegramLog::initialize($monolog, $update_logger ?? null);
        }

        if (isset($this->config['custom_http_client'])) {
            Request::setClient(new Client($this->config['custom_http_client']));
        }

        if (isset($this->config['commands']['paths'])) {
            $this->telegram->addCommandsPaths($this->config['commands']['paths']);
        }


        // Enambe MySql
        if (isset($this->config['mysql']['host']) && !empty($this->config['mysql']['host'])) {
            if (isset($this->config['mysql']['table_prefix'])) {
                $table_prefix = trim($this->config['mysql']['table_prefix']) ?: '';
            }

            // Set own encoding
            if (isset($this->config['mysql']['encoding']) and !empty(trim($this->config['mysql']['encoding']))) {
                $this->telegram->enableMySql($this->config['mysql'], $table_prefix, trim($this->config['mysql']['encoding']));
            }

            // Enable Default encoding utf8mb4
            else {
                $this->telegram->enableMySql($this->config['mysql'], $table_prefix);
            }

        }


        if (isset($this->config['paths']['download'])) {
            $this->telegram->setDownloadPath($this->config['paths']['download']);
        }

        if (isset($this->config['paths']['upload'])) {
            $this->telegram->setDownloadPath($this->config['paths']['upload']);
        }

        if (!empty($this->config['commands']['configs'])) {
            foreach ($this->config['commands']['configs'] as $command => $config) {
                $this->telegram->setCommandConfig($command, $config);
            }
        }

        if (!empty($this->config['limiter']['enabled'])) {
            if (!empty($this->config['limiter']['options'])) {
                $this->telegram->enableLimiter($this->config['limiter']['options']);
            } else {
                $this->telegram->enableLimiter();
            }
        }
    }


    /**
     * Send message to Chat
     * @param array $message_params
     * @param string $message_text
     */
    public function sendMessage(array $message_params, string $message_text)
    {

        $send_params['chat_id'] = $message_params['chat_id'];
        $send_params['text'] = $message_text;

        // Value = HTML|MarkdownV2|Markdown
        if (!empty($message_params['parse_mode'])) {
            $send_params['parse_mode'] = $message_params['parse_mode'];
        }

        if (!empty($message_params['url']) and !empty($message_params['url_text'])) {
            $inline_keyboard = new InlineKeyboard([
                ['text' => $message_params['url_text'], 'url' => $message_params['url']]
            ]);
            $send_params['reply_markup'] = $inline_keyboard;
        }

        try {
            $result = Request::sendMessage($send_params);
            if ($result->isOk()) {
                return $result->getResult()->getMessageId();
            }
        } catch (TelegramException $e) {
            if (strpos($e->getMessage(), "Telegram returned an invalid response") === false) {
                throw $e;
            }
        }
    }


    /**
     * Display usage help
     */
    private function showHelp(): void
    {

        // Check the type of interface between web server and PHP
        // Example: cli | fpm-fcgi
        if (PHP_SAPI !== 'cli') {
            print '<pre>';
        }

        print 'Bot Console' . ($this->config['bot_username'] ? ' (@' . $this->config['bot_username'] . ')' : '') . PHP_EOL . PHP_EOL;
        print 'Available commands:' . PHP_EOL;

        $commands = '';
        foreach (self::$commands as $command => $data) {
            if (isset($data['hidden']) && $data['hidden'] === true) {
                continue;
            }

            if (!empty($commands)) {
                $commands .= PHP_EOL;
            }

            if (!isset($data['description'])) {
                $data['description'] = 'No description available';
            }

            $commands .= ' ' . $command . str_repeat(' ', 10 - strlen($command)) . '- ' . trim($data['description']);
        }

        print $commands . PHP_EOL;

        if (PHP_SAPI !== 'cli') {
            print '</pre>';
        }
    }


    /**
     * Handle webhook method request
     * @throws TelegramException
     */
    private function handleWebhook(): void
    {
        if ($this->validateRequest()) {
            try {
                $this->telegram->handle();
            } catch (TelegramException $e) {
                if (strpos($e->getMessage(), 'Telegram returned an invalid response') === false) {
                    throw $e;
                }
            }
        }
    }


    /**
     * Validate request to check if it comes from the Telegram servers
     * and also does it contain a secret string
     * @return bool
     */
    private function validateRequest(): bool
    {
        if (PHP_SAPI !== 'cli') {
            $header_secret = null;
            foreach (getallheaders() as $name => $value) {
                if (stripos($name, 'X-Telegram-Bot-Api-Secret-Token') !== false) {
                    $header_secret = $value;
                    break;
                }
            }
            $secret = $this->config['webhook']['secret_token'];
            if (!isset($secret, $header_secret) || $secret !== $header_secret) {
                return false;
            }
        }

        return true;
    }


    /**
     * Set webhook
     */
    private function setWebhook(): void
    {
        if (empty($this->config['webhook']['url'])) {
            throw new Exception('Webhook URL is empty!');
        }

        if (!isset($this->config['webhook']['secret_token'])) {
            throw new Exception('Secret is empty!');
        }

        $result = $this->telegram->setWebhook($this->config['webhook']['url'], $this->config['webhook']);

        if ($result->isOk()) {
            print 'Webhook URL: ' . $this->config['webhook']['url'] . PHP_EOL;
            print $result->getDescription() . PHP_EOL;
        } else {
            print 'Request failed: ' . $result->getDescription() . PHP_EOL;
        }
    }


    /**
     * Delete webhook
     */
    private function deleteWebhook(): void
    {
        $result = $this->telegram->deleteWebhook();

        if ($result->isOk()) {
            print $result->getDescription() . PHP_EOL;
        } else {
            print 'Request failed: ' . $result->getDescription();
        }
    }


    /**
     * Get webhook info
     */
    private function webhookInfo(): void
    {
        $result = Request::getWebhookInfo();

        if ($result->isOk()) {
            if (PHP_SAPI !== 'cli') {
                print '<pre>' . print_r($result->getResult(), true) . '</pre>' . PHP_EOL;
            } else {
                print print_r($result->getResult(), true) . PHP_EOL;
            }
        } else {
            print 'Request failed: ' . $result->getDescription() . PHP_EOL;
        }
    }


    /**
     * Run scheduled commands
     * @throws Exception
     * @throws TelegramException
     */
    private function handleCron(): void
    {
        $commands = [];

        $cronlock = new TempFile('cron');
        if ($cronlock->getFile() === null) {
            exit("Couldn't obtain lockfile!" . PHP_EOL);
        }

        $file = $cronlock->getFile()->getPathname();

        $fh = fopen($file, 'wb');
        if (!$fh || !flock($fh, LOCK_EX | LOCK_NB)) {
            if (PHP_SAPI === 'cli') {
                print "There is already another cron task running in the background!" . PHP_EOL;
            }
            exit;
        }

        if (!empty($this->config['cron']['groups'])) {
            foreach ($this->config['cron']['groups'] as $command_group => $commands_in_group) {
                foreach ($commands_in_group as $command) {
                    $commands[] = $command;
                }
            }
        }

        if (empty($commands)) {
            throw new Exception('No commands to run!');
        }

        $this->telegram->runCommands($commands);

        if (flock($fh, LOCK_UN)) {
            fclose($fh);
            unlink($file);
        }
    }


    /**
     * Select Chats with filter
     * @param array $param
     */
    public function selectChats(array $params)
    {
        return DB::selectChats($params);
    }
}
