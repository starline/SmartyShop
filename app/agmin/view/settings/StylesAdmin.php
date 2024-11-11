<?php

/**
 * GoodGin CMS - The Best of gins
 * Style
 *
 * @author Andi Huga
 * @version 1.1
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class StylesAdmin extends Auth
{
    private $styles_dir;

    public function __construct()
    {
        parent::__construct();

        $this->styles_dir = $this->Config->root_dir . 'templates/' . $this->Settings->theme . '/css/';
    }

    public function fetch()
    {

        $styles = array();

        // Порядок файлов в меню
        $sort = array('style.css');

        // Читаем все css-файлы
        if ($handle = opendir($this->styles_dir)) {
            $i = count($sort);
            while (false !== ($file = readdir($handle))) {
                if (is_file($this->styles_dir . $file) && $file[0] != '.'  && pathinfo($file, PATHINFO_EXTENSION) == 'css') {
                    if (($key = array_search($file, $sort)) !== false) {
                        $styles[$key] = $file;
                    } else {
                        $styles[$i++] = $file;
                    }
                }
            }
            closedir($handle);
        }
        ksort($styles);

        // Текущий шаблон
        $style_file = $this->Request->get('file');

        if (!empty($style_file) && pathinfo($style_file, PATHINFO_EXTENSION) != 'css') {
            exit();
        }


        // Если не указан - вспоминаем его из сессии
        if (empty($style_file) && isset($_SESSION['last_edited_style'])) {
            $style_file = $_SESSION['last_edited_style'];
        }
        // Иначе берем первый файл из списка
        elseif (empty($style_file)) {
            $style_file = reset($styles);
        }

        // Передаем имя шаблона в дизайн
        $this->Design->assign('style_file', $style_file);

        // Если можем прочитать файл - передаем содержимое в дизайн
        if (is_readable($this->styles_dir . $style_file)) {
            $style_content = file_get_contents($this->styles_dir . $style_file);
            $this->Design->assign('style_content', $style_content);
        }

        // Если нет прав на запись - передаем в дизайн предупреждение
        if (!empty($style_file) && !is_writable($this->styles_dir . $style_file) && !is_file($this->styles_dir . '../locked')) {
            $this->Design->assign('message_error', 'permissions');
        } elseif (is_file($this->styles_dir . '../locked')) {
            $this->Design->assign('message_error', 'theme_locked');
        } else {

            // Запоминаем в сессии имя редактируемого шаблона
            $_SESSION['last_edited_style'] = $style_file;
        }

        $this->Design->assign('theme', $this->Settings->theme);
        $this->Design->assign('styles', $styles);

        return $this->Design->fetch('settings/styles.tpl');
    }
}
