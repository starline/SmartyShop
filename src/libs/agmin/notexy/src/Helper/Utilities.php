<?php

namespace NotexyBot\Helper;

use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\TelegramLog;
use Psr\Log\LoggerInterface;

/**
 * Extra functions
 */
class Utilities
{
    /**
     * Is debug print enabled?
     * @var bool
     */
    private static $debug_print_enabled = false;


    /**
     * Logger instance
     * @var LoggerInterface
     */
    private static $debug_print_logger = null;


    /**
     * Show debug message and (if enabled) write to debug log
     * @param string $text
     * @param array  $context
     */
    public static function debugPrint(string $text, array $context = []): void
    {
        if (PHP_SAPI === 'cli' && self::$debug_print_enabled) {
            if ($text === '') {
                return;
            }

            $prefix = '';
            $backtrace = debug_backtrace();

            if (isset($backtrace[1]['class'])) {
                $prefix = $backtrace[1]['class'] . '\\' . $backtrace[1]['function'];
            }

            TelegramLog::debug('[' . $prefix . '] ' . $text . ' ' . json_encode($context));

            if (self::$debug_print_logger !== null) {
                if (strpos($text, PHP_EOL) !== false) {
                    $text = explode(PHP_EOL, trim($text));

                    foreach ($text as $line) {
                        self::$debug_print_logger->debug('[' . $prefix . '] ' . trim($line), $context ?? []);
                    }
                } else {
                    self::$debug_print_logger->debug('[' . $prefix . '] ' . trim($text), $context ?? []);
                }
            } else {
                $prefix = '[' . date('Y-m-d H:i:s') . '] ' . $prefix . ': ';
                $message = $prefix . trim($text);

                if (!empty($context)) {
                    $message .= ' ' . json_encode($context);
                }

                $message = preg_replace('~[\r\n]+~', PHP_EOL . $prefix, $message);

                print $message . PHP_EOL;
            }
        }
    }


    /**
     * Enable/disable debug print
     * @param bool $enabled
     */
    public static function setDebugPrint(bool $enabled = true): void
    {
        self::$debug_print_enabled = $enabled;
    }


    /**
     * Check if debug print is enabled
     */
    public static function isDebugPrintEnabled(): bool
    {
        if (PHP_SAPI === 'cli') {
            return self::$debug_print_enabled;
        }

        return false;
    }
}
