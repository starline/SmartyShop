<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 * Работаем над страницей импорта товаров
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class ProductsImportAdmin extends Auth
{
    public $import_files_dir = 'files/imports/';
    public $import_file = '_import.csv';
    public $allowed_extensions = array('csv', 'txt');
    private $locale = 'ru_RU.UTF-8';
    private $price_types = array(
        'gdocs' => 'Цены на комплектующие'
    );


    public function fetch()
    {

        $import_file_path = $this->Config->root_dir . $this->import_files_dir;

        $this->Design->assign('import_files_dir', $this->import_files_dir);
        $this->Design->assign('price_types', $this->price_types);

        if (!is_writable($import_file_path)) {
            $this->Design->assign('message_error', 'no_permission');
        }

        // Проверяем локаль
        $old_locale = setlocale(LC_ALL, 0);
        setlocale(LC_ALL, $this->locale);
        if (setlocale(LC_ALL, 0) != $this->locale) {
            $this->Design->assign('message_error', 'locale_error');
            $this->Design->assign('locale', $this->locale);
        }
        setlocale(LC_ALL, $old_locale);


        if ($this->Request->method('post') && $this->Request->files("file")) {

            $price_type = $this->Request->post('price_type');
            $this->Design->assign('price_type', $price_type);

            if ($price_type) {
                $uploaded_name = $this->Request->files("file", "tmp_name");
                $temp = tempnam($import_file_path, 'temp_');

                if (!move_uploaded_file($uploaded_name, $temp)) {
                    $this->Design->assign('message_error', 'upload_error');
                }

                if (!$this->convert_file($temp, $import_file_path . $price_type . $this->import_file)) {
                    $this->Design->assign('message_error', 'convert_error');
                } else {
                    $this->Design->assign('filename', $this->Request->files("file", "name"));
                }

                unlink($temp);
            } else {
                $this->Design->assign('message_error', 'type_error');
            }
        }

        return $this->Design->fetch('products/products_import.tpl');
    }



    private function convert_file($source, $dest)
    {

        // Узнаем какая кодировка у файла
        $teststring = file_get_contents($source, false, null, false, 1000000);

        if (preg_match('//u', $teststring)) { // Кодировка - UTF8

            // Просто копируем файл
            return copy($source, $dest);
        } else {

            // Конвертируем в UFT8
            if (!$src = fopen($source, "r")) {
                return false;
            }

            if (!$dst = fopen($dest, "w")) {
                return false;
            }

            while (($line = fgets($src, 4096)) !== false) {
                $line = $this->win_to_utf($line);
                fwrite($dst, $line);
            }

            fclose($src);
            fclose($dst);
            return true;
        }
    }



    private function win_to_utf($text)
    {

        if (function_exists('iconv')) {
            return @iconv('windows-1251', 'UTF-8', $text);
        } else {
            $t = '';
            for ($i = 0, $m = strlen($text); $i < $m; $i++) {
                $c = ord($text[$i]);
                if ($c <= 127) {
                    $t .= chr($c);
                    continue;
                }
                if ($c >= 192 && $c <= 207) {
                    $t .= chr(208) . chr($c - 48);
                    continue;
                }
                if ($c >= 208 && $c <= 239) {
                    $t .= chr(208) . chr($c - 48);
                    continue;
                }
                if ($c >= 240 && $c <= 255) {
                    $t .= chr(209) . chr($c - 112);
                    continue;
                }
                if ($c == 184) {
                    $t .= chr(209) . chr(145);
                    continue;
                } #ё
                if ($c == 168) {
                    $t .= chr(208) . chr(129);
                    continue;
                } #Ё
                if ($c == 179) {
                    $t .= chr(209) . chr(150);
                    continue;
                } #і
                if ($c == 178) {
                    $t .= chr(208) . chr(134);
                    continue;
                } #І
                if ($c == 191) {
                    $t .= chr(209) . chr(151);
                    continue;
                } #ї
                if ($c == 175) {
                    $t .= chr(208) . chr(135);
                    continue;
                } #ї
                if ($c == 186) {
                    $t .= chr(209) . chr(148);
                    continue;
                } #є
                if ($c == 170) {
                    $t .= chr(208) . chr(132);
                    continue;
                } #Є
                if ($c == 180) {
                    $t .= chr(210) . chr(145);
                    continue;
                } #ґ
                if ($c == 165) {
                    $t .= chr(210) . chr(144);
                    continue;
                } #Ґ
                if ($c == 184) {
                    $t .= chr(209) . chr(145);
                    continue;
                }; #Ґ
            }
            return $t;
        }
    }
}
