<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 2.0
 *
 * Управление настройками магазина, хранящимися в базе данных
 * В отличие от класса Config оперирует настройками доступными админу и хранящимися в базе данных.
 *
 */

namespace GoodGin;

class Settings extends GoodGin
{
    private $vars = array();

    public function __construct()
    {

        parent::__construct();

        // Выбираем из базы настройки
        $this->Database->query('SELECT name, value FROM __settings');

        // и записываем их в переменную
        foreach ($this->Database->results() as $result) {
            if (!($this->vars[$result->name] = @unserialize($result->value))) {
                $this->vars[$result->name] = $result->value;
            }
        }
    }


    /**
     * Выбираем переменную
     * @param $name
     */
    public function __get($name)
    {

        // Для определения класса API
        if ($res = parent::__get($name)) {
            return $res;
        }

        // Для определения Settings vars
        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        } else {
            return null;
        }
    }


    /**
     * Установить переменную
     * @param $name
     * @param $value
     */
    public function __set($name, $value): void
    {
        $this->vars[$name] = $value;

        if (is_array($value)) {
            $value = serialize($value);
        } else {
            $value = (string) $value;
        }

        $this->Database->query('SELECT count(*) as count FROM __settings WHERE name=?', $name);
        if ($this->Database->result('count') > 0) {
            $this->Database->query('UPDATE __settings SET value=? WHERE name=?', $value, $name);
        } else {
            $this->Database->query('INSERT INTO __settings SET value=?, name=?', $value, $name);
        }
    }
}
