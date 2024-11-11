<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 * Этот класс использует шаблон products.tpl
 * Отображение списка товаров, каталог товаров
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class ProductsView extends View
{
    public function fetch()
    {

        // GET-Параметры
        // Если в параметре get(string) есть недопустимые знаки, они будут удалены
        $category_url 		= $this->Request->get('category', 'string');
        $brand_url    		= $this->Request->get('brand', 'string');

        $products_filter = array();
        $products_filter['visible'] = 1;
        $noindex = true; // Right away close indexation

        // Если задан бренд, выберем его из базы
        if (!empty($brand_url)) {
            $brand = $this->ProductsBrands->get_brand((string)$brand_url);
            $this->Design->assign('brand', $brand);
            $products_filter['brand_id'] = $brand->id;
        }

        // Выберем текущую категорию
        if (!empty($category_url)) {
            if ($category = $this->ProductsCategories->get_category((string)$category_url)) {

                // If selected only category. GET = view(1) and category_url(2)
                if (count($this->Request->get()) == 2) {
                    $this->Design->assign('canonical', '/' . $category->url); // Set hard canonical url
                    $this->Design->assign('show_description', true);
                    $noindex = false; // Open indexation
                }

                $this->Design->assign('category', $category);

                if (!$category->meta_title) {
                    $category->meta_title = $category->name;
                }

                $this->Design->assign('meta_title', $category->meta_title);

                // Если description пустой, берем title + product_meta_description
                $this->Design->assign('meta_description', ($category->meta_description) ? $category->meta_description : $category->meta_title . ' ' . $this->Settings->product_meta_description);

                $products_filter['category_id'] = $category->children;
            } else {
                return false;
            }
        }

        // Если задано ключевое слово
        $keyword = $this->Request->get('keyword');
        if (!empty($keyword)) {
            $noindex = false; // Open indexation
            $this->Design->assign('keyword', $keyword);
            $products_filter['keyword'] = $keyword;
        }

        // Сортировка
        if ($sort = $this->Request->get('sort', 'string')) {
            $products_filter['sort'] = $sort;
            $noindex = true; // Close indexation
        } else {
            $products_filter['sort'] = 'position';
        }

        $products_filter['sort_in_stock'] = true;
        $products_filter['sort_disable'] = true;
        $this->Design->assign('sort', $products_filter['sort']);

        // Характеристики
        if (!empty($category)) {
            $features = array();
            $selected_features = array();
            foreach ($this->ProductsFeatures->get_features(array('category_id' => $category->id, 'in_filter' => 1)) as $feature) {
                $features[$feature->id] = $feature;
                if (($val = strval($this->Request->get($feature->id))) != '') {
                    $selected_features[$feature->id] = $val;
                    $noindex = true; // Close indexation. Temporarily
                }
            }

            // Свойства характеристик
            $options_filter['visible'] = 1;
            $options_filter['category_id'] = $category->children;
            if (!empty($features)) {
                $options_filter['feature_id'] = array_keys($features);
            }

            if (!empty($selected_features)) {
                $this->Design->assign('canonical', $this->Request->url($selected_features, true)); // Set canonical, clear other params
                $options_filter['features'] = $selected_features;
                $products_filter['features'] = $selected_features;
            }

            if (!empty($brand->id)) {
                $options_filter['brand_id'] = $brand->id;
            }

            $options = $this->ProductsFeatures->getOptions($options_filter);

            foreach ($options as $option) {
                if (isset($features[$option->feature_id])) {
                    $features[$option->feature_id]->options[] = $option;
                }
            }

            // Delete fetures withot options
            foreach ($features as $i => &$feature) {
                if (empty($feature->options)) {
                    unset($features[$i]);
                }
            }

            $this->Design->assign('features', $features);
        }


        /////////////////////////////////////
        // Постраничная навигация
        ////////////////////////////////////
        $items_per_page = $this->Settings->products_num;
        $current_page = $this->Request->get('page', 'integer');     // Текущая страница в постраничном выводе
        $current_page = max(1, $current_page);                      // Если не задана, то равна 1
        $this->Design->assign('current_page_num', $current_page);
        $products_count = $this->Products->count_products($products_filter); // Вычисляем количество страниц

        $pages_num = ceil($products_count / $items_per_page);
        $this->Design->assign('total_pages_num', $pages_num);
        $this->Design->assign('total_products_num', $products_count);

        // Закрываем пагинатор от индексации
        if (!empty($this->Request->get('page'))) {
            $noindex = true; // Close indexation
        }

        $products_filter['page'] = $current_page;
        $products_filter['limit'] = $items_per_page;

        // Скидка для пользователя
        $discount = 0;
        if (isset($this->user->discount)) {
            $discount = $this->user->discount;
        }


        //////////////////////
        //  Выбираем товары
        /////////////////////
        $variants_sku = array();
        $products = $this->Products->get_products($products_filter);

        if (!empty($products)) {
            $products_ids = array_keys($products);

            foreach ($products as &$product) {
                $product->variants = array();
                $product->images = array();
                $product->properties = array();
            }

            $variants = $this->ProductsVariants->getVariants(array('product_id' => $products_ids));
            foreach ($variants as &$variant) {

                // Устанавливаем цены с учетом скидки
                //$variant->price *= (100-$discount)/100;

                $products[$variant->product_id]->variants[] = $variant;
                $variants_sku[] =  $variant->sku;
            }

            $images = $this->Images->getImages($products_ids, 'product');
            foreach ($images as $image) {
                $products[$image->entity_id]->images[] = $image;
            }

            // Устанавливаем вариант по-умолчанию
            foreach ($products as &$product) {
                if (isset($product->variants[0])) {
                    $product->variant = $product->variants[0];
                }

                if (isset($product->images[0])) {
                    $product->image = $product->images[0];
                }
            }

            /*
            $properties = $this->ProductsFeatures->getOptions(array('product_id'=>$products_ids));
            foreach($properties as $property)
                $products[$property->product_id]->options[] = $property;
            */

            $this->Design->assign('variants_sku', $variants_sku);
            $this->Design->assign('products', $products);
        }

        // Выбираем бренды, они нужны нам в шаблоне
        if (!empty($category)) {
            $brands = $this->ProductsBrands->get_brands(array('category_id' => $category->children, 'visible' => 1));
            $category->brands = $brands;
        }

        $this->Design->assign('noindex', $noindex);


        // Устанавливаем мета-теги в зависимости от запроса
        if (isset($brand)) {
            $this->Design->assign('meta_title', $brand->meta_title);
            $this->Design->assign('meta_description', $brand->meta_description);
        } elseif (isset($keyword)) {
            $this->Design->assign('meta_title', $keyword);
            $this->Design->assign('meta_description', $keyword . ' ' . $this->Settings->product_meta_description);
        }


        return $this->Design->fetch('products.tpl');
    }
}
