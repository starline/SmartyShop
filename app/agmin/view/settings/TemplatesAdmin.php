<?php

/**
 * GoodGin CMS - The Best of gins
 * Templates
 *
 * @author Andi Huga
 * @version 1.1
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class TemplatesAdmin extends Auth
{
    private $templates_dir;

    public function __construct()
    {
        parent::__construct();

        $this->templates_dir = $this->Config->root_dir . 'templates/'.$this->Settings->theme.'/html/';
    }


    public function fetch()
    {

        $templates = array();

        // Порядок файлов в меню
        $sort = array(
            'index.tpl',
            'page.tpl',
            'products.tpl',
            'main.tpl',
            'product.tpl',
            'blog.tpl',
            'post.tpl',
            'cart.tpl',
            'cart_informer.tpl',
            'order.tpl',
            'user_login.tpl',
            'user_register.tpl',
            'user_password_remind.tpl',
            'user.tpl',
            'feedback.tpl',
            'pagination.tpl'
        );

        // Читаем все tpl-файлы
        if ($handle = opendir($this->templates_dir)) {
            $i = count($sort);
            while (false !== ($file = readdir($handle))) {
                if (is_file($this->templates_dir . $file) && $file[0] != '.'  && pathinfo($file, PATHINFO_EXTENSION) == 'tpl') {
                    if (($key = array_search($file, $sort)) !== false) {
                        $templates[$key] = $file;
                    } else {
                        $templates[$i++] = $file;
                    }
                }
            }
            closedir($handle);
            ksort($templates);
        }

        // Текущий шаблон
        $template_file = $this->Request->get('file');

        if (!empty($template_file) && pathinfo($template_file, PATHINFO_EXTENSION) != 'tpl') {
            exit();
        }


        // Если не указан - вспоминаем его из сессии
        if (empty($template_file) && !empty($_SESSION['last_edited_template'])) {
            $template_file = $_SESSION['last_edited_template'];
        }

        // Иначе берем первый файл из списка
        elseif (empty($template_file)) {
            $template_file = reset($templates);
        }

        // Передаем имя шаблона в дизайн
        $this->Design->assign('template_file', $template_file);

        // Если можем прочитать файл - передаем содержимое в дизайн
        if (is_readable($this->templates_dir . $template_file)) {
            $template_content = file_get_contents($this->templates_dir . $template_file);
            $this->Design->assign('template_content', $template_content);
        }

        // Если нет прав на запись - передаем в дизайн предупреждение
        if (!empty($template_file) && !is_writable($this->templates_dir . $template_file) && !is_file($this->templates_dir . '../locked')) {
            $this->Design->assign('message_error', 'permissions');
        } elseif (is_file($this->templates_dir . '../locked')) {
            $this->Design->assign('message_error', 'theme_locked');
        } else {
            // Запоминаем в сессии имя редактируемого шаблона
            $_SESSION['last_edited_template'] = $template_file;
        }

        $this->Design->assign('theme', $this->Settings->theme);
        $this->Design->assign('templates', $templates);


        return $this->Design->fetch('settings/templates.tpl');
    }
}
