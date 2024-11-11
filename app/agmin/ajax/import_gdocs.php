<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 *
 * Import gooogle doc csv
 * 2020.11.18
 */

session_start();
define('secure', 'true');

// Composer
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
require_once  dirname(__DIR__) . '/view/Auth.php';

class ImportAjax extends Auth
{
    // Соответствие полей в базе и номера колоноки в файле
    private $columns_names = array(
        'name' =>             	array('product', 'name', 'товар', 'название', 'наименование'),
        'price' =>            	array('price', 'цена', 'на продажу, грн', 'на продажу, руб'),
        'cost_price' =>     	array('cost price', 'оптовая цена', 'оптовая цена, грн', 'Оптовая цена, руб'),
        'sku' =>              	array('sku', 'артикул', 'арт'),
        'weight' =>				array('weight', 'вес', 'вес, кг', 'масса', 'кг')
    );

    // Соответствие имени колонки и поля в базе
    private $internal_columns_names = array();

    private $import_files_dir		= 'files/imports/'; 		// Временная папка
    private $import_file			= 'gdocs_import.csv';   	// Временный файл
    private $subcategory_delimiter 	= '/';                   	// Разделитель подкатегорий в файле
    private $column_delimiter      	= ',';						// Разделитель колонок
    private $products_count        	= 20;						// Импортируем по N строк
    private $columns               	= array();
    private $currency;


    public function import()
    {

        if (!$this->access('products_import')) {
            return false;
        }

        // Для корректной работы установим локаль UTF-8
        setlocale(LC_ALL, 'ru_RU.UTF-8');

        $result = new stdClass();

        $this->currency = $this->Money->getCurrency();
        $import_file_path = $this->Config->root_dir . $this->import_files_dir . $this->import_file;

        // Открываем файл
        $f = fopen($import_file_path, 'r');

        // Определяем колонки из первой строки файла
        $this->columns = fgetcsv($f, null, $this->column_delimiter);

        // Заменяем имена колонок из файла на внутренние имена колонок
        foreach ($this->columns as &$column) {
            if ($internal_name = $this->internal_column_name($column)) {
                $this->internal_columns_names[$column] = $internal_name;
                $column = $internal_name;
            }
        }

        // Если нет названия товара и артикула - не будем импортировать
        if (!in_array('name', $this->columns) && !in_array('sku', $this->columns)) {
            return false;
        }

        // Переходим на заданную позицию, если импортируем не сначала
        if ($from = $this->Request->get('from')) {
            fseek($f, $from);
        }

        // Массив импортированных товаров
        $imported_items = array();

        // Проходимся по строкам, пока не конец файла
        // или пока не импортировано достаточно строк для одного запроса
        for ($k = 0; !feof($f) && $k < $this->products_count; $k++) {

            // Читаем строку
            $line = fgetcsv($f, 0, $this->column_delimiter);

            $product = null;

            if (is_array($line)) {

                // Проходимся по колонкам строки
                foreach ($this->columns as $num => $name) {

                    // Создаем массив item[название_колонки]=значение
                    if (isset($line[$num]) && !empty($line)) {
                        $product[$name] = $line[$num];
                    }
                }

                // Импортируем этот товар
                if ($imported_item = $this->import_item($product)) {
                    $imported_items[] = $imported_item;
                }
            }
        }

        // Запоминаем на каком месте закончили импорт
        $from = ftell($f);

        // И закончили ли полностью весь файл
        $result->end = feof($f);

        fclose($f);
        $size = filesize($import_file_path);

        // Создаем объект результата
        $result->from = $from;          	// На каком месте остановились
        $result->totalsize = $size;    		// Размер всего файла
        $result->items = $imported_items;   // Импортированные товары

        return $result;
    }



    /**
     * Импорт одного товара $item[column_name] = value;
     */
    private function import_item($item)
    {

        //print_r($item);

        // Удаляем пробелы из SKU и прайс
        $item['sku'] = str_replace(" ", "", $item['sku']);
        $item['price'] = str_replace(" ", "", $item['price']);
        $item['cost_price'] = str_replace(" ", "", $item['cost_price']);

        // Проверим обязательные параметры (должны быть все)
        if (
            empty($item['name'])
            || empty($item['sku'])
            || empty($item['price'])
            || empty($item['cost_price'])
        ) {
            return false;
        }


        // Убираем пробелы и меняем "," на "."
        $item['cost_price'] = str_replace(array(' ', ","), array('', "."), strval($item['cost_price']));
        $item['price'] = str_replace(array(' ', ","), array('', "."), strval($item['price']));
        $item['weight'] = str_replace(array(' ', ","), array('', "."), strval($item['weight']));

        // Выбираем вариант в Базе
        // Вариантов с одинаковым артикулом может быть несколько
        $this->Database->query('SELECT * FROM __products_variants WHERE sku=?', $item['sku']);
        if ($prev_variants = $this->Database->results()) {
            foreach ($prev_variants as $prev_variant) {

                //*** Подготовим вариант товара
                $variant = array();

                // Делаем импорт если цена или оптовая цена измемнилась
                if ($item['price'] != $prev_variant->price || $item['cost_price'] != $prev_variant->cost_price || $item['weight'] != $prev_variant->weight) {

                    // Вычисляем старую цену
                    if ($prev_variant->price > $item['price']) {
                        $variant['old_price'] = $prev_variant->price;
                    }


                    $variant['cost_price'] = $item['cost_price'];
                    $variant['price'] =  $item['price'];
                    $variant['weight'] =  $item['weight'];

                    // Определяем дату обновления.
                    // В базе 2014-11-30 21:05:08
                    $variant['date'] = date("Y-m-d H:i:s");

                    $this->ProductsVariants->update_variant($prev_variant->id, $variant);

                    // Нужно вернуть обновленный товар
                    $imported_item = new stdClass();
                    $imported_item->status = 'updated';
                    $imported_item->variant = $this->ProductsVariants->getVariant(intval($prev_variant->id));
                    $imported_item->variant->prev_price = $prev_variant->price;
                    $imported_item->product = $this->Products->get_product(intval($prev_variant->product_id));
                    $imported_item->currency = $this->currency;
                }
            }

            if (!empty($imported_item->variant)) {
                return $imported_item;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    // Фозвращает внутреннее название колонки по названию колонки в файле
    private function internal_column_name($name)
    {
        $name = trim($name);
        $name = str_replace('/', '', $name);
        $name = str_replace('\/', '', $name);
        foreach ($this->columns_names as $i => $names) {
            foreach ($names as $n) {
                if (!empty($name) && preg_match("/^" . preg_quote($name) . "$/ui", $n)) {
                    return $i;
                }
            }
        }
        return false;
    }
}


$import_ajax = new ImportAjax();

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($import_ajax->import());
