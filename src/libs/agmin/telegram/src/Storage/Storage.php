<?php

namespace Bot\Storage;

use Bot\Exception\StorageException;
use Bot\Helper\Utilities;
use Bot\Storage\Driver\BotDB;
use Bot\Storage\Driver\File;
use Longman\TelegramBot\DB;

/**
 * Picks the best storage driver available
 */
class Storage
{
    /**
     * Supported database engines
     */
    private static $storage_drivers = [
        'mysql'     => 'MySQL',
        'pgsql'     => 'PostgreSQL',
        'postgres'  => 'PostgreSQL',
        'memcache'  => 'Memcache',
        'memcached' => 'Memcache',
    ];

    /**
     * Return which driver class to use
     *
     * @return string
     *
     * @throws StorageException
     */
    public static function getClass(): string
    {
        
        if (DB::isDbConnected()) {
            $storage = BotDB::class; # Bot\Storage\Driver\BotDB
        } elseif (getenv('DATABASE_URL')) {
            $dsn = parse_url(getenv('DATABASE_URL'));

            if (!isset(self::$storage_drivers[$dsn['scheme']])) {
                throw new StorageException('Unsupported database type!');
            }

            $storage = 'Bot\Storage\Driver\\' . (self::$storage_drivers[$dsn['scheme']] ?: '');
        } elseif (defined('DATA_PATH')) {
            $storage = File::class;
        }

        if (empty($storage)) {
            throw new StorageException('Storage class not provided');
        }

        if (!class_exists($storage)) {
            throw new StorageException('Storage class doesn\'t exist: ' . $storage);
        }

        Utilities::debugPrint('Using storage: \'' . $storage . '\'');

        return $storage;
    }
}
