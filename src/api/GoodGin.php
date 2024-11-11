<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 3.0
 *
 * Основной класс для доступа к API
 *
 */

namespace GoodGin;

class GoodGin
{
    // Классы API
    // Class name => Class filenema
    private static $apiClasses = array();

    // Созданные объекты
    private static $objects = array();

    /**
     * Конструктор определим на случай обращения parent::__construct() в классах API
     */
    public function __construct()
    {

        // Init. Only for first time
        if (empty(self::$apiClasses)) {

            $api_files = scandir(__DIR__);
            foreach ($api_files as $key => $php_name) {

                // Пропускаем ненужные файлы. Убираем .php
                if ($php_name != "." and $php_name != ".." and $php_name != "GoodGin.php") {
                    self::$apiClasses[substr($php_name, 0, -4)] = $php_name;
                }
            }

            // If user is logined
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
            }

            // If session is over, check Cookie
            else {
                $user_id = $this->Users->checkRememberMeCookie();
            }

            if (!empty($user_id)) {
                $user = $this->Users->getUser($user_id);
                if (!empty($user) && $user->enabled == 1) {
                    if ($user->manager == 1) {
                        $user->permissions = $this->Users->getUserPermissionsName($user->id);
                    }
                    self::$objects['user'] = $user;
                }
            }
        }
    }


    /**
     * Магический метод, создает нужный объект API
     * @param string $name
     */
    public function __get(string $name)
    {
        // Если такой объект уже существует, возвращаем его
        if (isset(self::$objects[$name])) {
            return (self::$objects[$name]);
        }

        // Если запрошенного API не существует - null
        // For use __get in Settings
        if (!class_exists("GoodGin\\{$name}")) {
            return null;
        }

        // Сохраняем для будущих обращений к нему
        $ClassName =  "GoodGin\\{$name}";
        self::$objects[$name] = new $ClassName();

        // Возвращаем созданный объект
        return self::$objects[$name];
    }

    
    /**
     * Set variable in to Goodgin->$name
     * @param string $name
     * @param string $value
     */
    public function __set(string $name, string $value): void
    {
        self::$objects[$name] = $value;
    }
}
