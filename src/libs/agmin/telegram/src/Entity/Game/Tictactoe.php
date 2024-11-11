<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bot\Entity\Game;

use Bot\Entity\Game;
use Bot\Exception\BotException;
use Bot\Exception\StorageException;
use Bot\Helper\Utilities;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Spatie\Emoji\Emoji;

/**
 * Tic-Tac-Toe
 */
class Tictactoe extends Game
{
    /**
     * Game unique ID
     * @var string
     */
    protected static $code = 'xo';

    /**
     * Game name / title
     * @var string
     */
    protected static $title = 'Tic-Tac-Toe';

    /**
     * Game description
     * @var string
     */
    protected static $description = 'Tic-tac-toe is a game for two players, X and O, who take turns marking the spaces in a 3×3 grid.';

    /**
     * Game thumbnail image
     * @var string
     */
    protected static $image = 'http://i.imgur.com/yU2uexr.png';

    /**
     * Order on the games list
     * @var int
     */
    protected static $order = 1;

    /**
     * Base starting board
     * @var array
     */
    protected static $board = [
        ['', '', ''],
        ['', '', ''],
        ['', '', ''],
    ];

    /**
     * Game handler
     *
     * @return ServerResponse
     *
     * @throws BotException
     * @throws TelegramException
     * @throws StorageException
     */
    protected function gameAction(): ServerResponse
    {
        if ($this->getCurrentUserId() !== $this->getUserId('host') && $this->getCurrentUserId() !== $this->getUserId('guest')) {
            return $this->answerCallbackQuery(__("You're not in this game!"), true);
        }

        $game_data = &$this->data['game_data'];

        $this->defineSymbols();

        $callbackquery_data = $this->manager->getUpdate()->getCallbackQuery()->getData();
        $callbackquery_data = explode(';', $callbackquery_data);

        $command = $callbackquery_data[1];

        $args = null;
        if (isset($callbackquery_data[2])) {
            $args = explode('-', $callbackquery_data[2]);
        }

        if ($command === 'start') {
            if (isset($game_data['settings']) && $game_data['settings']['X'] == 'host') {
                $game_data['settings']['X'] = 'guest';
                $game_data['settings']['O'] = 'host';
            } else {
                $game_data['settings']['X'] = 'host';
                $game_data['settings']['O'] = 'guest';
            }

            $game_data['current_turn'] = 'X';
            $game_data['board'] = static::$board;

            Utilities::debugPrint('Game initialization');
        } elseif ($args === null) {
            Utilities::debugPrint('No move data received');
        }

        if (isset($game_data['current_turn']) && $game_data['current_turn'] == 'E') {
            return $this->answerCallbackQuery(__("This game has ended!"), true);
        }

        if ($this->getCurrentUserId() !== $this->getUserId($game_data['settings'][$game_data['current_turn']]) && $command !== 'start') {
            return $this->answerCallbackQuery(__("It's not your turn!"), true);
        }

        if (isset($args) && isset($game_data['board'][$args[0]][$args[1]]) && $game_data['board'][$args[0]][$args[1]] !== '') {
            return $this->answerCallbackQuery(__("Invalid move!"), true);
        }

        $this->max_y = count($game_data['board']);
        $this->max_x = count($game_data['board'][0]);

        if (isset($args)) {
            if ($game_data['current_turn'] == 'X') {
                $game_data['board'][$args[0]][$args[1]] = 'X';
                $game_data['current_turn'] = 'O';
            } elseif ($game_data['current_turn'] == 'O') {
                $game_data['board'][$args[0]][$args[1]] = 'O';
                $game_data['current_turn'] = 'X';
            } else {
                Utilities::debugPrint('Invalid move data: ' . ($args[0]) . ' - ' . ($args[1]));

                return $this->answerCallbackQuery(__("Invalid move!"), true);
            }

            Utilities::debugPrint($game_data['current_turn'] . ' placed at ' . ($args[1]) . ' - ' . ($args[0]));
        }

        $isOver = $this->isGameOver($game_data['board']);
        $gameOutput = '';

        if (!empty($isOver) && in_array($isOver, ['X', 'O'])) {
            $gameOutput = Emoji::trophy() . ' <b>' . __("{PLAYER} won!", ['{PLAYER}' => '</b>' . $this->getUserMention($game_data['settings'][$isOver]) . '<b>']) . '</b>';
        } elseif ($isOver == 'T') {
            $gameOutput = Emoji::chequeredFlag() . ' <b>' . __("Game ended with a draw!") . '</b>';
        }

        if (!empty($isOver) && in_array($isOver, ['X', 'O', 'T'])) {
            $game_data['current_turn'] = 'E';
        } else {
            $gameOutput = Emoji::playButton() . ' ' . $this->getUserMention($game_data['settings'][$game_data['current_turn']]) . ' (' . $this->symbols[$game_data['current_turn']] . ')';
        }

        if ($this->saveData($this->data)) {
            return $this->editMessage(
                $this->getUserMention('host') . ' (' . (($game_data['settings']['X'] == 'host') ? $this->symbols['X'] : $this->symbols['O']) . ')' . ' vs. ' . $this->getUserMention('guest') . ' (' . (($game_data['settings']['O'] == 'guest') ? $this->symbols['O'] : $this->symbols['X']) . ')' . PHP_EOL . PHP_EOL . $gameOutput,
                $this->gameKeyboard($game_data['board'], $isOver)
            );
        }

        return parent::gameAction();
    }

    /**
     * Define game symbols (emojis)
     */
    protected function defineSymbols(): void
    {
        $this->symbols['empty'] = '.';

        $this->symbols['X'] = Emoji::crossMark();
        $this->symbols['O'] = Emoji::hollowRedCircle();

        $this->symbols['X_won'] = $this->symbols['X'];
        $this->symbols['O_won'] = $this->symbols['O'];

        $this->symbols['X_lost'] = Emoji::multiply();
        $this->symbols['O_lost'] = Emoji::radioButton();
    }

    /**
     * Check whenever game is over
     *
     * @param array $board
     *
     * @return string
     */
    protected function isGameOver(array &$board): ?string
    {
        $empty = 0;
        for ($x = 0; $x < $this->max_x; $x++) {
            for ($y = 0; $y < $this->max_y; $y++) {
                if ($board[$x][$y] == '') {
                    $empty++;
                }

                if (isset($board[$x][$y]) && isset($board[$x][$y + 1]) && isset($board[$x][$y + 2])) {
                    if ($board[$x][$y] != '' && $board[$x][$y] == $board[$x][$y + 1] && $board[$x][$y] == $board[$x][$y + 2]) {
                        $winner = $board[$x][$y];
                        $board[$x][$y + 1] = $board[$x][$y] . '_won';
                        $board[$x][$y + 2] = $board[$x][$y] . '_won';
                        $board[$x][$y] = $board[$x][$y] . '_won';

                        return $winner;
                    }
                }

                if (isset($board[$x][$y]) && isset($board[$x + 1][$y]) && isset($board[$x + 2][$y])) {
                    if ($board[$x][$y] != '' && $board[$x][$y] == $board[$x + 1][$y] && $board[$x][$y] == $board[$x + 2][$y]) {
                        $winner = $board[$x][$y];

                        $board[$x + 1][$y] = $board[$x][$y] . '_won';
                        $board[$x + 2][$y] = $board[$x][$y] . '_won';
                        $board[$x][$y] = $board[$x][$y] . '_won';

                        return $winner;
                    }
                }

                if (isset($board[$x][$y]) && isset($board[$x + 1][$y + 1]) && isset($board[$x + 2][$y + 2])) {
                    if ($board[$x][$y] != '' && $board[$x][$y] == $board[$x + 1][$y + 1] && $board[$x][$y] == $board[$x + 2][$y + 2]) {
                        $winner = $board[$x][$y];

                        $board[$x + 1][$y + 1] = $board[$x][$y] . '_won';
                        $board[$x + 2][$y + 2] = $board[$x][$y] . '_won';
                        $board[$x][$y] = $board[$x][$y] . '_won';

                        return $winner;
                    }
                }

                if (isset($board[$x][$y]) && isset($board[$x - 1][$y + 1]) && isset($board[$x - 2][$y + 2])) {
                    if ($board[$x][$y] != '' && $board[$x][$y] == $board[$x - 1][$y + 1] && $board[$x][$y] == $board[$x - 2][$y + 2]) {
                        $winner = $board[$x][$y];
                        $board[$x - 1][$y + 1] = $board[$x][$y] . '_won';
                        $board[$x - 2][$y + 2] = $board[$x][$y] . '_won';
                        $board[$x][$y] = $board[$x][$y] . '_won';

                        return $winner;
                    }
                }
            }
        }

        if ($empty == 0) {
            return 'T';
        }

        return null;
    }
}
