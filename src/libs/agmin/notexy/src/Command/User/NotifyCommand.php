<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use NotexyBot\Helper\Utilities;
use Spatie\Emoji\Emoji;
use GoodGin\GoodGin;

/**
 * Start command...
 *
 */
class NotifyCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'notify';

    /**
     * @var string
     */
    protected $description = 'Получать увидомления';

    /**
     * @var string
     */
    protected $usage = '/notify';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * @return mixed
     */
    public function execute(): ServerResponse
    {

        $message = $this->getMessage();
        $message_id = $message->getMessageId();
        $command_str = trim($message->getText(true));
        $chat_id = $message->getChat()->getId();
        $reply_to_message = $message->getReplyToMessage();
        $user_namme = $message->getUsername();

        $text =  Emoji::upsideDownFace() . ' Укажите токен в команде: /notify <token>';

        if (!empty($command_str)) {

            $GG = new GoodGin();
            $user = $GG->Users->getUser(['token' =>  $command_str]);

            if (!empty($user->id)) {

                // Save chat_id
                $GG->Users->updateUser($user->id, ['te_chat_id' =>  $chat_id]);

                $text = Emoji::upsideDownFace() .' '. $user->name .', Вы успешно подписались на оповещения';
            }
        }

        return $this->replyToChat($text);
    }
}
