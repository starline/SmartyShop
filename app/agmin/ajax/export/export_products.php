<?php

session_start();
define('secure', 'true');

// Composer
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
require_once  dirname(__DIR__) . '/view/Auth.php';
$GoodGin = new Auth();

class ExportAjax extends Auth
{
    private $columns_names = array(
        'sku' =>              	'Арт.',
        'name' =>             	'Название товара (вариант)',
        'price' =>            	'Цена',
        'cost_price' =>		    'Оптовая цена',
        'stock' =>            	'Вналичии',
        'url' =>              	'Ссылка на сайт',
        //'brand' =>            	'Бранд',
        //'body' =>             	'Описание html',
        //'images' =>           	'Изображения',
        //'category'=>				'Категория',
    );

    private $column_delimiter = ";";
    private $subcategory_delimiter = '/';
    private $products_count = 10; // кол-во товаров обработаных за раз
    private $export_files_dir = 'files/exports/';
    private $filename = 'products.csv';

    public function fetch()
    {

        if (!$this->access('export')) {
            return false;
        }

        $export_file_path = $this->Config->root_dir . $this->export_files_dir . $this->filename;

        // Эксель кушает только 1251
        //setlocale(LC_ALL, 'ru_RU.1251');
        //$this->Database->query('SET NAMES cp1251');

        // Страница, которую экспортируем
        $page = $this->Request->get('page');
        if (empty($page) || $page == 1) {
            $page = 1;

            // Если начали сначала - удалим старый файл экспорта
            if (is_writable($export_file_path)) {
                unlink($export_file_path);
            }
        }

        // Открываем файл экспорта на добавление
        $f = fopen($export_file_path, 'ab');

        // Выбираем названия характеристик товаров
        // $features = $this->ProductsFeatures->get_features();
        // foreach ($features as $feature)
        // 	$this->columns_names[$feature->name] = $feature->name;

        // Если начали сначала - добавим в первую строку названия колонок
        if ($page == 1) {
            fputcsv($f, $this->columns_names, $this->column_delimiter);
        }

        // Все товары


        // Отфильтровать
        $filter = array();
        $filter['page'] = $page;
        $filter['limit'] = $this->products_count;
        $filter['category_id'] = intval($this->Request->get('category_id'));

        // Выбираем товаары с базы
        $products = $this->Products->get_products($filter);
        if (empty($products)) {
            return false;
        }


        // Добаавляем характеристики к товару
        // $options = $this->ProductsFeatures->get_product_options($p->id);
        // foreach ($options as $option) {
        // 	if (!isset($products[$option->product_id][$option->name]))
        // 		$products[$option->product_id][$option->name] = $option->value;
        // }


        // foreach ($products as $p_id => &$product) {
        // 	$categories = array();
        // 	$cats = $this->ProductsCategories->get_product_categories($p_id);

        // 	foreach ($cats as $category) {
        // 		$path = array();
        // 		$cat = $this->ProductsCategories->get_category((int)$category->category_id);
        // 		if (!empty($cat)) {

        // 			// Формируем дерево категории
        // 			foreach ($cat->path as $p)
        // 				$path[] = str_replace($this->subcategory_delimiter, '\\' . $this->subcategory_delimiter, $p->name);

        // 			$categories[] = join('/', $path);
        // 		}
        // 	}
        // 	$product['category'] = join(', ', $categories);
        // }


        // Изображения товаров
        $images = $this->Images->getImages(array_keys($products), 'product');

        foreach ($images as $image) {

            // Добавляем изображения к товару
            if (empty($products[$image->entity_id]->images)) {
                $products[$image->entity_id]->images = $image->filename;

                // остальные изображения через запятую
            } else {
                $products[$image->entity_id]->images = $products[$image->entity_id]->images . ', ' . $image->filename;
            }
        }

        // Выбирааем все варианты товаров
        $variants = $this->ProductsVariants->getVariants(array('product_id' => array_keys($products)));

        // Присваиваем варианты к соответсвующему товару
        foreach ($variants as $variant) {
            if (isset($products[$variant->product_id])) {
                if ($variant->infinity) {
                    $variant->stock = '';
                }
                $products[$variant->product_id]->variants[] = $variant;
            }
        }

        foreach ($products as &$product) {
            $variants = $product->variants;
            unset($product->variants);

            if (!empty($variants)) {
                foreach ($variants as $variant) {

                    $result = $product;
                    foreach ($variant as $name => $value) {
                        if (empty($result->$name)) {
                            $result->$name = $value;
                        }
                    }

                    foreach ($this->columns_names as $column_var => $column_name) {
                        if (!empty($result->$column_var)) {
                            $res[$column_var] = $result->$column_var;

                            // Если есть название варианта, добавляем к названию товара
                            //if ($column_var == 'name' and !empty($result->variant)) {
                            //   $res[$column_var] .= ' (' . $result->variant . ')';
                            //}

                            // сформируем сссылку на товар
                            if ($column_var == 'url') {
                                $res[$column_var] = $this->Config->root_url . '/tovar-' . $res[$column_var];
                            }
                        } else {
                            $res[$column_var] = '';
                        }
                    }
                    fputcsv($f, $res, $this->column_delimiter);
                }
            }
        }

        $total_products = $this->Products->count_products($filter);

        if ($this->products_count * $page < $total_products) {
            return array('end' => false, 'page' => $page, 'totalpages' => ceil($total_products / $this->products_count));
        } else {
            return array('end' => true, 'page' => $page, 'totalpages' => ceil($total_products / $this->products_count));
        }

        fclose($f);
    }
}

$export_ajax = new ExportAjax();

header("Content-type: application/json; charset=utf-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");


print json_encode($export_ajax->fetch());
