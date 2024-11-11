<?php

/**
 * GoodGin CMS - The Best of gins
 * Images
 *
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class ImagesAdmin extends Auth
{
    private $images_dir;
    private $images_url;

    public function __construct()
    {
        parent::__construct();

        $this->images_dir = $this->Config->root_dir . 'templates/' . $this->Settings->theme . '/images/';
        $this->images_url = $this->Config->root_url . '/templates/' . $this->Settings->theme . '/images/';
    }

    public function fetch()
    {

        $allowed_extentions = array('png', 'gif', 'jpg', 'jpeg', 'ico');
        $images = array();

        // Сохраняем
        if ($this->Request->method('post') && !is_file($this->images_dir.'../locked')) {
            $old_names = $this->Request->post('old_name');
            $new_names = $this->Request->post('new_name');
            if (is_array($old_names)) {
                foreach ($old_names as $i => $old_name) {
                    $new_name = $new_names[$i];
                    $new_name = trim(pathinfo($new_name, PATHINFO_FILENAME).'.'.pathinfo($old_name, PATHINFO_EXTENSION), '.');

                    if (is_writable($this->images_dir) && is_file($this->images_dir . $old_name) && !is_file($this->images_dir . $new_name)) {
                        rename($this->images_dir . $old_name, $this->images_dir . $new_name);
                    } elseif (is_file($this->images_dir . $new_name) && $new_name != $old_name) {
                        $message_error = 'name_exists';
                    }
                }
            }

            $delete_image = trim($this->Request->post('delete_image'), '.');

            if (!empty($delete_image)) {
                @unlink($this->images_dir . $delete_image);
            }

            // Загрузка изображений
            if ($images = $this->Request->files('upload_images')) {
                for ($i = 0; $i < count($images['name']); $i++) {
                    $name = trim($images['name'][$i], '.');
                    if (in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), $allowed_extentions)) {
                        move_uploaded_file($images['tmp_name'][$i], $this->images_dir . $name);
                    }
                }
            }


            if (!isset($message_error)) {
                header("Location: ".$_SERVER['REQUEST_URI']);
                exit();
            } else {
                $this->Design->assign('message_error', $message_error);
            }

        }



        // Чтаем все файлы
        if ($handle = opendir($this->images_dir)) {
            while (false !== ($file = readdir($handle))) {
                if (is_file($this->images_dir . $file) && $file[0] != '.' && in_array(pathinfo($file, PATHINFO_EXTENSION), $allowed_extentions)) {
                    $image = new stdClass();
                    $image->name = $file;
                    $image->size = filesize($this->images_dir . $file);
                    list($image->width, $image->height) = @getimagesize($this->images_dir . $file);
                    $images[$file] = $image;
                }
            }
            closedir($handle);
            ksort($images);
        }

        // Если нет прав на запись - передаем в дизайн предупреждение
        if (!is_writable($this->images_dir)) {
            $this->Design->assign('message_error', 'permissions');
        } elseif (is_file($this->images_dir . '../locked')) {
            $this->Design->assign('message_error', 'theme_locked');
        }

        $this->Design->assign('theme', $this->Settings->theme);
        $this->Design->assign('images', $images);
        $this->Design->assign('images_dir', $this->images_dir);
        $this->Design->assign('images_url', $this->images_url);

        return $this->Design->fetch('settings/images.tpl');
    }

}
