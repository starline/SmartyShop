<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 * Домашняя страница сайта
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class MainView extends View
{
    public function __construct()
    {
        parent::__construct();
    }

    public function fetch()
    {

        // Выбираем популярные товары с основных категорий
        $categories_products = array();
        foreach ($this->categories_tree as $cat) {
            if ($cat->visible) {

                $category_params['visible'] = 1;
                $category_params['featured'] = 1;
                $category_params['category_id'] = $cat->children;
                $category_params['limit'] = 8;

                $category_products = $this->Products->get_products($category_params);

                if (!empty($category_products)) {

                    // id выбраных товаров
                    $category_pids = array_keys($category_products);

                    // Выбираем варианты товаров
                    $category_variants = $this->ProductsVariants->getVariants(array('product_id' => $category_pids));

                    // Для каждого варианта, добавляем вариант в соответствующий товар
                    foreach ($category_variants as &$variant) {
                        $category_products[$variant->product_id]->variants[] = $variant;
                    }

                    // Выбираем изображения товаров
                    $category_images = $this->Images->getImages($category_pids, 'product');
                    foreach ($category_images as $image) {
                        $category_products[$image->entity_id]->images[] = $image;
                    }

                    foreach ($category_products as &$product) {
                        if (isset($product->variants[0])) {
                            $product->variant = $product->variants[0];
                        }
                        if (isset($product->images[0])) {
                            $product->image = $product->images[0];
                        }
                    }

                    $current_category = new stdClass();
                    $current_category->category = $cat;
                    $current_category->products = $category_products;
                    $categories_products[] = $current_category;
                }
            }
        }
        $this->Design->assign('categories_products', $categories_products);


        // Устанавливаем meta-теги
        $this->Design->assign('meta_title', $this->Settings->company_name . ' - ' . $this->Settings->company_description);
        $this->Design->assign('meta_description', $this->Settings->company_name . ' - ' . $this->Settings->company_description);
        $this->Design->assign('canonical', '');


        // OpenGraph
        $openGraph = array(
            array('property' => 'og:type', 'content' => 'website'),
            array('property' => 'og:title', 'content' => $this->Settings->company_name),
            array('property' => 'og:description', 'content' => $this->Settings->company_description),
            array('property' => 'og:url', 'content' => $this->Config->root_url),
            array('property' => 'og:site_name', 'content' => $this->Settings->company_name),
            array('property' => 'og:image', 'content' => $this->Config->root_url . "/templates/" . $this->Settings->theme . "/images/favicon.png"),
            array('property' => 'og:image:alt', 'content' => $this->Settings->company_name),
            array('property' => 'og:image:type', 'content' => "image/png"),
        );
        $this->Design->assign('openGraph', $openGraph);


        return $this->Design->fetch('main.tpl');
    }
}
