<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 1.5
 *
 * Класс-обертка для конфигурационного файла с настройками магазина
 * В отличие от класса Settings, Config оперирует низкоуровневыми настройками, например найстройками базы данных.
 *
 */

namespace GoodGin;

class Config
{
    // Файл для хранения настроек
    public $config_file = 'src/config/config.php';

    private $vars = array();

    // В конструкторе записываем настройки файла в переменные этого класса
    // для удобного доступа к ним. Например: $GoodGin->Config->db_user
    public function __construct()
    {

        // Определяем корневую директорию сайта
        $local_path = getenv("SCRIPT_NAME"); // /index.php
        $absolute_path = getenv("SCRIPT_FILENAME");  // /var/www/site.com/index.php
        $root_dir = str_replace($local_path, "/", $absolute_path); // /var/www/site.com/


        // Читаем настройки из дефолтного файла
        $ini = parse_ini_file($root_dir . $this->config_file);

        // Записываем настройку как переменную класса
        foreach ($ini as $var => $value) {
            $this->vars[$var] = $value;
        }


        // Протокол
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https' ? 'https' : 'http';
        if ($_SERVER["SERVER_PORT"] == 443) {
            $protocol = 'https';
        }

        $this->vars['protocol'] = $protocol;
        $this->vars['root_url'] = $protocol . '://' . rtrim($_SERVER['HTTP_HOST']);


        // Root directory
        $this->vars['root_dir'] =  $root_dir;

        // Template
        $this->vars['templates_subdir'] = null;

        // Directory for library
        $this->vars['libs_dir']  = $root_dir . 'src/libs/';

        // Directory for modules
        $this->vars['modules_dir'] = $root_dir . 'src/modules/';

        // Directory for payment modules
        $this->vars['payment_dir']  = $root_dir . 'src/modules/payment/';

        // Directory for delivery modules
        $this->vars['delivery_dir']  = $root_dir . 'src/modules/delivery/';

        // Directory for notify modules
        $this->vars['notify_dir']  = $root_dir . 'src/modules/notify/';


        // Максимальный размер загружаемых файлов
        $max_upload = (int)(ini_get('upload_max_filesize'));
        $max_post = (int)(ini_get('post_max_size'));
        $memory_limit = (int)(ini_get('memory_limit'));
        $this->max_upload_filesize = min($max_upload, $max_post, $memory_limit) * 1024 * 1024;

        // Часовой пояс
        if (!empty($this->php_timezone)) {
            date_default_timezone_set($this->php_timezone);
        }
    }


    /**
     * Магическим методов возвращаем нужную переменную
     */
    public function __get($name)
    {
        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        } else {
            return null;
        }
    }


    /**
     * Магическим методов задаём нужную переменную
     */
    public function __set($name, $value)
    {
        # Запишем конфиги
        if (isset($this->vars[$name])) {
            $conf = file_get_contents($this->root_dir . $this->config_file);
            $conf = preg_replace("/" . $name . "\s*=.*\n/i", $name . ' = ' . $value . "\r\n", $conf);
            $cf = fopen($this->root_dir . $this->config_file, 'w');
            fwrite($cf, $conf);
            fclose($cf);
            $this->vars[$name] = $value;
        }
    }


    public function setRootURL($root_url)
    {
        $this->vars['root_url'] = $root_url;
    }


    public function setTemplateSubdir($templates_subdir)
    {
        $this->vars['templates_subdir'] = $templates_subdir;
    }
}
