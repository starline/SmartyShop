<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use NotexyBot\Helper\Utilities;
use Spatie\Emoji\Emoji;

/**
 * Start command...
 *
 */
class StartCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var string
     */
    protected $description = 'Стартовая команда';

    /**
     * @var string
     */
    protected $usage = '/start';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * @return mixed
     */
    public function execute(): ServerResponse
    {
        return $this->replyToChat(
            Emoji::wavingHand() . ' Привет, Друг!' . PHP_EOL .
            'Введите /help чтобы увидеть все комманды!'
        );
    }
}
