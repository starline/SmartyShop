<?php

/**
 * GoodGin CMS - The Best of gins
 * Скрипты
 *
 * @author Andi Huga
 * @version 2.1
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class SettingsAdmin extends Auth
{
    private $allowed_image_extentions = array('png', 'gif', 'jpg', 'jpeg', 'ico');

    public function fetch()
    {

        $params_name = array(
            'site_name',
            'company_name',
            'company_description',
            'date_format',
            'decimals_point',
            'thousands_separator',
            'products_num',
            'rel_products_num',
            'products_num_admin',
            'max_order_amount',
            'units',
            'weight_units',
            'product_meta_description',
            'emojis',
            'expense_finance_category_id',
            'income_finance_category_id'
        );

        if ($this->Request->method('POST')) {

            // Выбираем найстройки из POST
            foreach ($params_name as $name) {

                // save settings
                $this->Settings->$name = $this->Request->post($name);
            }

            // Водяной знак
            $clear_image_cache = false;
            $watermark = $this->Request->files('watermark_file', 'tmp_name');

            if (!empty($watermark) && in_array(pathinfo($this->Request->files('watermark_file', 'name'), PATHINFO_EXTENSION), $this->allowed_image_extentions)) {
                if (@move_uploaded_file($watermark, $this->Config->root_dir . $this->Config->images_watermark_file)) {
                    $clear_image_cache = true;
                } else {
                    $this->Design->assign('message_error', 'watermark_is_not_writable');
                }
            }

            if ($this->Settings->watermark_offset_x != $this->Request->post('watermark_offset_x')) {
                $this->Settings->watermark_offset_x = $this->Request->post('watermark_offset_x');
                $clear_image_cache = true;
            }

            if ($this->Settings->watermark_offset_y != $this->Request->post('watermark_offset_y')) {
                $this->Settings->watermark_offset_y = $this->Request->post('watermark_offset_y');
                $clear_image_cache = true;
            }

            if ($this->Settings->watermark_transparency != $this->Request->post('watermark_transparency')) {
                $this->Settings->watermark_transparency = $this->Request->post('watermark_transparency');
                $clear_image_cache = true;
            }

            if ($this->Settings->images_sharpen != $this->Request->post('images_sharpen')) {
                $this->Settings->images_sharpen = $this->Request->post('images_sharpen');
                $clear_image_cache = true;
            }


            // Удаление все заресайзеных изображений со старым watermark
            if ($clear_image_cache) {
                $dir = $this->Config->images_resized_dir;
                if ($handle = opendir($dir)) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                            @unlink($dir . "/" . $file);
                        }
                    }
                    closedir($handle);
                }
            }

            // Проверяем доступ к библиотеке Imagick
            if (class_exists('Imagick') && $this->Config->images_use_imagick) {
                $this->Design->assign('imagick', true);
            }

            $this->Design->assign('message_success', 'saved');
        }

        // Выбираем финансовые категории
        $this->Design->assign([
            'income_finance_categories' => $this->Finance->get_categories(1),
            'expense_finance_categories' =>  $this->Finance->get_categories(0)
        ]);

        return $this->Design->fetch('settings/settings.tpl');
    }
}
