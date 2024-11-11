<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class CategoryAdmin extends Auth
{
    private $allowed_image_extentions = array('png', 'gif', 'jpg', 'jpeg', 'ico');

    public function fetch()
    {
        $images = array();
        $images_content = array();
        $seo_keywords = '';
        $seo_faqs = '';
        $category = new stdClass();
        $synonyms = new stdClass();

        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions = array(
            'products_categories' => array(
                'id' => 'integer',
                'parent_id' => 'integer',
                'name' => 'string',
                'url' => 'string',
                'meta_title' => 'string',
                'h1' => 'string',
                'meta_description' => 'string',
                'annotation' => 'string',
                'description' => 'string',
                'visible' => 'boolean',
                'main' => 'boolean'
            )
        );


        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $category = $this->postDataAcces($data_permissions);

            // Не допустить одинаковые URL разделов.
            if (isset($category->url) and $c = $this->ProductsCategories->get_category($category->url) and $c->id != $category->id) {
                $this->Design->assign('message_error', 'url_exists');
            } else {
                if (empty($category->id)) {
                    $category->id = $this->ProductsCategories->add_category($category);
                    $this->Design->assign('message_success', 'added');
                } else {
                    $this->ProductsCategories->update_category($category->id, $category);
                    $this->Design->assign('message_success', 'updated');
                }

                // SOE keywords
                $seo_keywords = $this->Request->post('seo_keywords');
                $seo_keywords_arr = explode("\n", $seo_keywords); // Формирум мосcив из строк
                $this->Seo->updateKeywords($seo_keywords_arr, $category->id, 'category');

                $seo_faqs = $this->Request->post('seo_faqs');
                $seo_faqs_arr = explode("\n", $seo_faqs); // Формирум мосив из строк
                $this->Seo->updateFAQs($seo_faqs_arr, $category->id, 'category');

                // Удаление основных изображений
                $images = (array)$this->Request->post('images');
                $current_images = $this->Images->getImages($category->id, 'category');
                foreach ($current_images as $image) {
                    if (!in_array($image->id, $images)) {
                        $this->Images->deleteImage($image->id);
                    }
                }

                // Удаление изображений контента
                $images_content = (array)$this->Request->post('images_content');
                $current_images_content = $this->Images->getImages($category->id, 'category_content');
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
                        if (!$this->Images->uploadAddImage($images['tmp_name'][$i], $images['name'][$i], $category->id, 'category')) {
                            $this->Design->assign('message_error', 'error uploading image');
                        }
                    }
                }

                // Загрузка осноных изображений из интернета и drag-n-drop файлов
                if ($images = $this->Request->post('images_urls')) {
                    foreach ($images as $url) {

                        // Если не пустой адрес и файл не локальный
                        if (!empty($url) && $url != 'http://' && strstr($url, '/') !== false) {
                            $this->Images->addImage($category->id, 'category', $url);
                        } elseif ($dropped_images = $this->Request->files('dropped_images')) {
                            $key = array_search($url, $dropped_images['name']);

                            // Ужимаем изображение до заданого размера
                            if ($key !== false && $image_name = $this->Images->uploadImage($dropped_images['tmp_name'][$key], $dropped_images['name'][$key])) {
                                $this->Images->addImage($category->id, 'category', $image_name);
                            }
                        }
                    }
                }

                // Загрузка изображений контента из интернета и drag-n-drop файлов
                if ($images_content = $this->Request->post('images_content_urls')) {
                    foreach ($images_content as $url) {

                        // Если не пустой адрес и файл не локальный
                        if (!empty($url) && $url != 'http://' && strstr($url, '/') !== false) {
                            $this->Images->addImage($category->id, 'category_content', $url);
                        } elseif ($dropped_images_content = $this->Request->files('dropped_images_content')) {
                            $key = array_search($url, $dropped_images_content['name']);

                            // Ужимаем изображение до заданого размера
                            if ($key !== false && $image_name_content = $this->Images->uploadImage($dropped_images_content['tmp_name'][$key], $dropped_images_content['name'][$key])) {
                                $this->Images->addImage($category->id, 'category_content', $image_name_content);
                            }
                        }
                    }
                }

                $images = $this->Images->getImages($category->id, 'category');
                $images_content = $this->Images->getImages($category->id, 'category_content');

                // Обновляем синонимы
                $synonyms = $this->Request->post('synonyms');
                $this->ProductsCategories->updateCategorySynonyms($category->id, $synonyms);
            }
        }


        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($category))) {


            $category = $this->ProductsCategories->get_category($id);

            if (empty($category->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }

            // Изображения товара
            $images = $this->Images->getImages($category->id, 'category');
            $images_content = $this->Images->getImages($category->id, 'category_content');

            $seo_keywords_arr = $this->Seo->getKeywords($category->id, 'category');
            $seo_keywords = join("\n", $seo_keywords_arr);

            $seo_faqs_arr = $this->Seo->getFAQs($category->id, 'category');
            $seo_faqs = join("\n", $seo_faqs_arr);

            // Выбираем синонимы категории
            $synonyms = $this->ProductsCategories->getSynonyms(array('category_id' => $category->id));
        }

        $categories = $this->ProductsCategories->getCategoriesTree();

        $this->Design->assign('seo_keywords', $seo_keywords);
        $this->Design->assign('seo_faqs', $seo_faqs);
        $this->Design->assign('category', $category);
        $this->Design->assign('categories', $categories);
        $this->Design->assign('images', $images);
        $this->Design->assign('images_content', $images_content);
        $this->Design->assign('synonyms', $synonyms);

        return  $this->Design->fetch('products/category.tpl');
    }
}
