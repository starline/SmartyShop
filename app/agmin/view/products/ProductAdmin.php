<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 *
 * ProductAdmin
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class ProductAdmin extends Auth
{
    public function fetch()
    {

        // Определяем обьекты
        $seo_keywords = array();
        $options = array();
        $images = array();
        $images_content = array();
        $product = new stdClass();

        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions = array(
            'products_content' => array(
                'id' => 'integer',
                'name' => 'string',
                'brand_id' => 'integer',
                'category_id' => 'integer',
                'url' => 'string',
                'meta_title' => 'string',
                'meta_description' => 'string',
                'annotation' => 'string',
                'body' => 'string'
            )
        );


        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $product = $this->postDataAcces($data_permissions);
            $product = $this->Misc->trimEntityProps($product, array('name', 'url', 'meta_title', 'meta_description', 'annotation'));

            //print_r($product);

            // Не допустить пустое название товара.
            if (empty($product->name)) {
                $this->Design->assign('message_error', 'empty_name');

                // Не допустить одинаковые URL товаров.
                // Пропускаем. Проверяется в api/Products.php.

            } else {
                if (empty($product->id)) {
                    $product->id = $this->Products->add_product($product);
                    $this->Design->assign('message_success', 'added');
                } else {
                    $this->Products->update_product($product->id, $product);
                    $this->Design->assign('message_success', 'updated');
                }

                $product = $this->Products->get_product($product->id);

                // SEO keywords
                $seo_keywords = explode("\n", $this->Request->post('seo_keywords')); // Формирум мaссив из строк
                $this->Seo->updateKeywords($seo_keywords, $product->id, 'product');

                // Удаление основных изображений
                $images = (array)$this->Request->post('images');
                $current_images = $this->Images->getImages($product->id, 'product');
                foreach ($current_images as $image) {
                    if (!in_array($image->id, $images)) {
                        $this->Images->deleteImage($image->id);
                    }
                }

                // Удаление изображения контента
                $images_content = (array)$this->Request->post('images_content');
                $current_images_content = $this->Images->getImages($product->id, 'product_content');
                foreach ($current_images_content as $image) {
                    if (!in_array($image->id, $images_content)) {
                        $this->Images->deleteImage($image->id);
                    }
                }

                // Порядок основных изображений
                if ($images = $this->Request->post('images')) {
                    $i = 0;
                    foreach ($images as $id) {
                        $this->Images->updateImage($id, array('position' => $i));
                        $i++;
                    }
                }

                // Порядок изображений контента
                if ($images_content = $this->Request->post('images_content')) {
                    $i = 0;
                    foreach ($images_content as $id) {
                        $this->Images->updateImage($id, array('position' => $i));
                        $i++;
                    }
                }

                // Загрузка основных изображений
                if ($images = $this->Request->files('images')) {
                    for ($i = 0; $i < count($images['name']); $i++) {
                        if (!$this->Images->uploadAddImage($images['tmp_name'][$i], $images['name'][$i], $product->id, 'product')) {
                            $this->Design->assign('message_error', 'error uploading image');
                        }
                    }
                }

                // Загрузка основных изображений из интернета и drag-n-drop файлов
                if ($images = $this->Request->post('images_urls')) {
                    foreach ($images as $url) {

                        // Если не пустой адрес и файл не локальный
                        if (!empty($url) && $url != 'http://' && strstr($url, '/') !== false) {
                            $this->Images->addImage($product->id, 'product', $url);
                        } elseif ($dropped_images = $this->Request->files('dropped_images')) {
                            $key = array_search($url, $dropped_images['name']);

                            // Ужимаем изображение до заданого размера
                            if ($key !== false && $image_name = $this->Images->uploadImage($dropped_images['tmp_name'][$key], $dropped_images['name'][$key])) {
                                $this->Images->addImage($product->id, 'product', $image_name);
                            }
                        }
                    }
                }

                // Загрузка изображений контента из интернета и drag-n-drop файлов
                if ($images_content = $this->Request->post('images_content_urls')) {
                    foreach ($images_content as $url) {

                        // Если не пустой адрес и файл не локальный
                        if (!empty($url) && $url != 'http://' && strstr($url, '/') !== false) {
                            $this->Images->addImage($product->id, 'product_content', $url);
                        } elseif ($dropped_images_content = $this->Request->files('dropped_images_content')) {
                            $key = array_search($url, $dropped_images_content['name']);

                            // Ужимаем изображение до заданого размера
                            if ($key !== false && $image_name_content = $this->Images->uploadImage($dropped_images_content['tmp_name'][$key], $dropped_images_content['name'][$key])) {
                                $this->Images->addImage($product->id, 'product_content', $image_name_content);
                            }
                        }
                    }
                }
                $images = $this->Images->getImages($product->id, 'product');
                $images_content = $this->Images->getImages($product->id, 'product_content');


                // Характеристики товара
                // Удалим все из товара
                foreach ($this->ProductsFeatures->get_product_options($product->id) as $po) {
                    $this->ProductsFeatures->deleteOption($product->id, $po->feature_id);
                }

                // Характеристики текущей категории
                $category_features = array();
                foreach ($this->ProductsFeatures->get_features(array('category_id' => $product->category_id)) as $f) {
                    $category_features[] = $f->id;
                }

                // Устанавливаем харакетристики товара
                if (is_array($options = $this->Request->post('options'))) {
                    foreach ($options as $f_id => $val) {
                        $option = new stdClass();
                        $option->feature_id = $f_id;
                        $option->value = $val;

                        if (in_array($option->feature_id, $category_features)) {
                            $this->ProductsFeatures->update_option($product->id, $option->feature_id, $option->value);
                        }
                    }
                }

                // Новые характеристики
                $new_features_names = $this->Request->post('new_features_names');
                $new_features_values = $this->Request->post('new_features_values');
                if (is_array($new_features_names) && is_array($new_features_values)) {
                    foreach ($new_features_names as $i => $name) {
                        $value = trim($new_features_values[$i]);

                        if (!empty($name) && !empty($value)) {

                            $feature = $this->ProductsFeatures->getFeature($name);
                            if (empty($feature)) {
                                $feature = new stdClass();
                                $feature->id = $this->ProductsFeatures->addFeature(array('name' => trim($name)));
                            }

                            $this->ProductsFeatures->addFeatureCategory($feature->id, $product->category_id);
                            $this->ProductsFeatures->update_option($product->id, $feature->id, $value);
                        }
                    }
                }
            }
        }


        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($product))) {

            $product = $this->Products->get_product(intval($id));

            if (empty($product->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }

            // Изображения товара
            $images = $this->Images->getImages($product->id, 'product');
            $images_content = $this->Images->getImages($product->id, 'product_content');

            // Свойства товара
            $options = $this->ProductsFeatures->get_product_options($product->id);
            if (is_array($options)) {
                $temp_options = array();
                foreach ($options as $option) {
                    $temp_options[$option->feature_id] = $option;
                }
                $options = $temp_options;
            }

            // SEO keywords
            $seo_keywords = $this->Seo->getKeywords($product->id, 'product');
        }


        /////////////////////
        //------- Creat View
        ////////////////////
        else {

            if ($category_id = $this->Request->get('category_id')) {
                $product->category_id = $category_id;
            }

            if ($brand_id = $this->Request->get('brand_id')) {
                $product->brand_id = $brand_id;
            }
        }


        // Все бренды
        $brands = $this->ProductsBrands->get_brands();
        $this->Design->assign('brands', $brands);

        // Все категории
        $categories = $this->ProductsCategories->getCategoriesTree();
        $this->Design->assign('categories', $categories);

        // Все свойства товара
        if (!empty($product->category_id)) {
            $features = $this->ProductsFeatures->get_features(array('category_id' => $product->category_id));
            $this->Design->assign('features', $features);

            if (!empty($features)) {
                foreach ($features as &$feature) {
                    $feature->variants = $this->ProductsFeatures->getFeatureVariants($feature->id);
                }
            }
        }

        $this->Design->assign('product', $product);
        $this->Design->assign('seo_keywords', $seo_keywords);
        $this->Design->assign('product_images', $images);
        $this->Design->assign('images_content', $images_content);
        $this->Design->assign('options', $options);

        return $this->Design->fetch('products/product.tpl');
    }
}
