<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 *
 */

session_start();
define('secure', 'true');

// Composer
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
require_once  dirname(__DIR__) . '/view/Auth.php';

class ImportAjax extends Auth
{
    // Соответствие полей в базе и имён колонок в файле
    private $columns_names = array(
        'name' =>             array('product', 'name', 'товар', 'название', 'наименование'),
        'url' =>              array('url', 'адрес'),
        'visible' =>          array('visible', 'published', 'видим'),
        'featured' =>         array('featured', 'hit', 'хит', 'рекомендуемый'),
        'category' =>         array('category', 'категория'),
        'brand' =>            array('brand', 'бренд'),
        'variant' =>          array('variant', 'вариант'),
        'price' =>            array('price', 'цена'),
        'cost_price' =>       array('compare price', 'старая цена'),
        'sku' =>              array('sku', 'артикул'),
        'stock' =>            array('stock', 'склад', 'на складе'),
        'meta_title' =>       array('meta title', 'заголовок страницы'),
        'meta_description' => array('meta description', 'описание страницы'),
        'annotation' =>       array('annotation', 'аннотация', 'краткое описание'),
        'description' =>      array('description', 'описание'),
        'images' =>           array('images', 'изображения')
    );

    // Соответствие имени колонки и поля в базе
    private $internal_columns_names = array();

    private $import_files_dir      = 'files/imports/'; // Временная папка
    private $import_file           = 'import.csv';           // Временный файл
    private $category_delimiter    = ',';                       // Разделитель каегорий в файле
    private $subcategory_delimiter = '/';                    // Разделитель подкаегорий в файле
    private $column_delimiter      = ';';
    private $products_count        = 10;
    private $columns               = array();

    public function import()
    {
        if (!$this->access('products_import')) {
            return false;
        }

        // Для корректной работы установим локаль UTF-8
        setlocale(LC_ALL, 'ru_RU.UTF-8');

        $result = new stdClass();
        $import_file_path = $this->Config->root_dir . $this->import_files_dir . $this->import_file;

        // Определяем колонки из первой строки файла
        $f = fopen($import_file_path, 'r');
        $this->columns = fgetcsv($f, null, $this->column_delimiter);

        // Заменяем имена колонок из файла на внутренние имена колонок
        foreach ($this->columns as &$column) {
            if ($internal_name = $this->internal_column_name($column)) {
                $this->internal_columns_names[$column] = $internal_name;
                $column = $internal_name;
            }
        }


        // Если нет названия товара - не будем импортировать
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
                foreach ($this->columns as $i => $col) {
                    // Создаем массив item[название_колонки]=значение
                    if (isset($line[$i]) && !empty($line) && !empty($col)) {
                        $product[$col] = $line[$i];
                    }
                }
            }

            // Импортируем этот товар
            if ($imported_item = $this->import_item($product)) {
                $imported_items[] = $imported_item;
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


    // Импорт одного товара $item[column_name] = value;
    private function import_item($item)
    {
        $imported_item = new stdClass();

        // Проверим не пустое ли название и артинкул (должно быть хоть что-то из них)
        if (empty($item['name']) && empty($item['sku'])) {
            return false;
        }

        // Подготовим товар для добавления в базу
        $product = array();

        if (isset($item['name'])) {
            $product['name'] = trim($item['name']);
        }

        if (isset($item['meta_title'])) {
            $product['meta_title'] = trim($item['meta_title']);
        }

        if (isset($item['meta_description'])) {
            $product['meta_description'] = trim($item['meta_description']);
        }

        if (isset($item['annotation'])) {
            $product['annotation'] = trim($item['annotation']);
        }

        if (isset($item['description'])) {
            $product['body'] = trim($item['description']);
        }

        if (isset($item['visible'])) {
            $product['visible'] = intval($item['visible']);
        }

        if (isset($item['featured'])) {
            $product['featured'] = intval($item['featured']);
        }

        if (isset($item['url'])) {
            $product['url'] = trim($item['url']);
        } elseif (isset($item['name'])) {
            $product['url'] = $this->Misc->transliteration_ru_en($item['name']);
        }

        // Если задан бренд
        if (!empty($item['brand'])) {
            $item['brand'] = trim($item['brand']);

            // Найдем его по имени
            $this->Database->query('SELECT id FROM __products_brands WHERE name=?', $item['brand']);

            if (!$product['brand_id'] = $this->Database->result('id')) {
                // Создадим, если не найден
                $product['brand_id'] = $this->ProductsBrands->add_brand(array('name' => $item['brand'], 'meta_title' => $item['brand'], 'meta_description' => $item['brand']));
            }
        }

        // Если задана категория
        $category_id = null;
        $categories_ids = array();
        if (isset($item['category'])) {
            foreach (explode($this->category_delimiter, $item['category']) as $c) {
                $categories_ids[] = $this->import_category($c);
            }
            $category_id = reset($categories_ids);
        }

        // Подготовим вариант товара
        $variant = array();

        if (isset($item['variant'])) {
            $variant['name'] = trim($item['variant']);
        }

        if (isset($item['price'])) {
            $variant['price'] = str_replace(',', '.', trim($item['price']));
        }

        if (isset($item['cost_price'])) {
            $variant['cost_price'] = trim($item['cost_price']);
        }

        if (isset($item['stock'])) {
            if ($item['stock'] == '') {
                $variant['stock'] = null;
            } else {
                $variant['stock'] = trim($item['stock']);
            }
        }

        if (isset($item['sku'])) {
            $variant['sku'] = trim($item['sku']);
        }

        // Если задан артикул варианта, найдем этот вариант и соответствующий товар
        if (!empty($variant['sku'])) {
            $this->Database->query('SELECT id as variant_id, product_id FROM __products_variants, __products WHERE sku=? AND __products_variants.product_id = __products.id LIMIT 1', $variant['sku']);
            $result = $this->Database->result();
            if ($result) {
                // и обновим товар
                if (!empty($product)) {
                    $this->Products->update_product($result->product_id, $product);
                }

                // и вариант
                if (!empty($variant)) {
                    $this->ProductsVariants->update_variant($result->variant_id, $variant);
                }

                $product_id = $result->product_id;
                $variant_id = $result->variant_id;

                // Обновлен
                $imported_item->status = 'updated';
            }
        }

        // Если на прошлом шаге товар не нашелся, и задано хотя бы название товара
        if ((empty($product_id) || empty($variant_id)) && isset($item['name'])) {
            if (!empty($variant['sku']) && empty($variant['name'])) {
                $this->Database->query('SELECT v.id as variant_id, p.id as product_id FROM __products p LEFT JOIN __products_variants v ON v.product_id=p.id WHERE v.sku=? LIMIT 1', $variant['sku']);
            } elseif (isset($item['variant'])) {
                $this->Database->query('SELECT v.id as variant_id, p.id as product_id FROM __products p LEFT JOIN __products_variants v ON v.product_id=p.id AND v.name=? WHERE p.name=? LIMIT 1', $item['variant'], $item['name']);
            } else {
                $this->Database->query('SELECT v.id as variant_id, p.id as product_id FROM __products p LEFT JOIN __products_variants v ON v.product_id=p.id WHERE p.name=? AND p.brand_id=? LIMIT 1', $item['name'], $product['brand_id']);
            }

            $r =  $this->Database->result();
            if ($r) {
                $product_id = $r->product_id;
                $variant_id = $r->variant_id;
            }

            // Если вариант найден - обновляем,
            if (!empty($variant_id)) {
                $this->ProductsVariants->update_variant($variant_id, $variant);
                $this->Products->update_product($product_id, $product);
                $imported_item->status = 'updated';
            }

            // Иначе - добавляем
            elseif (empty($variant_id)) {
                if (empty($product_id)) {
                    $product_id = $this->Products->add_product($product);
                }

                $this->Database->query('SELECT max(v.position) as pos FROM __products_variants v WHERE v.product_id=? LIMIT 1', $product_id);
                $pos = $this->Database->result('pos');

                $variant['position'] = $pos + 1;
                $variant['product_id'] = $product_id;
                $variant_id = $this->ProductsVariants->add_variant($variant);
                $imported_item->status = 'added';
            }
        }

        if (!empty($variant_id) && !empty($product_id)) {

            // Нужно вернуть обновленный товар
            $imported_item->variant = $this->ProductsVariants->getVariant(intval($variant_id));
            $imported_item->product = $this->Products->get_product(intval($product_id));

            // Добавляем категории к товару
            if (!empty($categories_ids)) {
                foreach ($categories_ids as $c_id) {
                    $this->ProductsCategories->add_product_category($product_id, $c_id);
                }
            }

            // Изображения товаров
            if (isset($item['images'])) {
                // Изображений может быть несколько, через запятую
                $images = explode(',', $item['images']);
                foreach ($images as $image) {
                    $image = trim($image);
                    if (!empty($image)) {
                        // Имя файла
                        $image_filename = pathinfo($image, PATHINFO_BASENAME);

                        // Добавляем изображение только если такого еще нет в этом товаре
                        $this->Database->query('SELECT filename FROM __content_images WHERE product_id=? AND (filename=? OR filename=?) LIMIT 1', $product_id, $image_filename, $image);
                        if (!$this->Database->result('filename')) {
                            $this->Images->addImage($product_id, 'product', $image);
                        }
                    }
                }
            }


            // Характеристики товаров
            foreach ($item as $feature_name => $feature_value) {

                // Если нет такого названия колонки, значит это название свойства
                if (!in_array($feature_name, $this->internal_columns_names)) {

                    // Свойство добавляем только если для товара указана категория
                    if ($category_id) {
                        $this->Database->query('SELECT f.id FROM __products_features f WHERE f.name=? LIMIT 1', $feature_name);
                        if (!$feature_id = $this->Database->result('id')) {
                            $feature_id = $this->ProductsFeatures->addFeature(array('name' => $feature_name));
                        }

                        $this->ProductsFeatures->addFeatureCategory($feature_id, $category_id);
                        $this->ProductsFeatures->update_option($product_id, $feature_id, $feature_value);
                    }
                }
            }
            return $imported_item;
        }
    }


    // Отдельная функция для импорта категории
    private function import_category($category)
    {
        // Поле "категория" может состоять из нескольких имен, разделенных subcategory_delimiter-ом
        // Только неэкранированный subcategory_delimiter может разделять категории
        $delimiter = $this->subcategory_delimiter;
        $regex = "/\\DELIMITER((?:[^\\\\\DELIMITER]|\\\\.)*)/";
        $regex = str_replace('DELIMITER', $delimiter, $regex);
        $names = preg_split($regex, $category, 0, PREG_SPLIT_DELIM_CAPTURE);
        $id = null;
        $parent = 0;

        // Для каждой категории
        foreach ($names as $name) {
            // Заменяем \/ на /
            $name = trim(str_replace("\\$delimiter", $delimiter, $name));
            if (!empty($name)) {
                // Найдем категорию по имени
                $this->Database->query('SELECT id FROM __products_categories WHERE name=? AND parent_id=?', $name, $parent);
                $id = $this->Database->result('id');

                // Если не найдена - добавим ее
                if (empty($id)) {
                    $id = $this->ProductsCategories->add_category(array('name' => $name, 'parent_id' => $parent, 'meta_title' => $name, 'meta_description' => $name, 'url' => $this->Misc->transliteration_ru_en($name)));
                }

                $parent = $id;
            }
        }
        return $id;
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
