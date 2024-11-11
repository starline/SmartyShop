<?php

namespace Bot\Command\User;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
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
    protected $description = 'Start command';

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
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        return $this->replyToChat(
            Emoji::wavingHand() . 'Hi there!' . PHP_EOL .
            'Type /help to see all commands!'
        );
    }
}