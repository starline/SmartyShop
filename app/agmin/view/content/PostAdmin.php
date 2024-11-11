<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 *
 * PostAdmin
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class PostAdmin extends Auth
{
    public function fetch()
    {

        // Определяем обьекты
        $seo_keywords = array();
        $post = new stdClass();

        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions = array(
            'blog' => array(
                'id' => 'integer',
                'name' => 'string',
                'visible' => 'boolean',
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

            $post = $this->postDataAcces($data_permissions);
            $post->date = date('Y-m-d', strtotime($this->Request->post('date')));

            // Не допустить одинаковые URL разделов.
            if (isset($post->url) and ($a = $this->Blog->getPost($post->url)) and $a->id != $post->id) {
                $this->Design->assign('message_error', 'url_exists');
            } else {
                if (empty($post->id)) {
                    $post->id = $this->Blog->addPost($post);
                    $this->Design->assign('message_success', 'added');
                } else {
                    $this->Blog->updatePost($post->id, $post);
                    $this->Design->assign('message_success', 'updated');
                }

                // SEO keywords
                $seo_keywords = explode("\n", $this->Request->post('seo_keywords')); // Формирум мосив из строк
                $this->Seo->updateKeywords($seo_keywords, $post->id, 'post');

                // Удаление изображений
                $images = (array)$this->Request->post('images');
                $current_images = $this->Images->getImages($post->id, 'post');
                foreach ($current_images as $image) {
                    if (!in_array($image->id, $images)) {
                        $this->Images->deleteImage($image->id);
                    }
                }

                // Порядок изображений
                if ($images = $this->Request->post('images')) {
                    $i = 0;
                    foreach ($images as $id) {
                        $this->Images->updateImage($id, array('position' => $i));
                        $i++;
                    }
                }

                // Загрузка изображений
                if ($images = $this->Request->files('images')) {
                    for ($i = 0; $i < count($images['name']); $i++) {
                        if (!$this->Images->uploadAddImage($images['tmp_name'][$i], $images['name'][$i], $post->id, 'post')) {
                            $this->Design->assign('message_error', 'error uploading image');
                        }
                    }
                }

                // Загрузка изображений из интернета и drag-n-drop файлов
                if ($images = $this->Request->post('images_urls')) {
                    foreach ($images as $url) {

                        // Если не пустой адрес и файл не локальный
                        if (!empty($url) && $url != 'http://' && strstr($url, '/') !== false) {
                            $this->Images->addImage($post->id, 'post', $url);
                        } elseif ($dropped_images = $this->Request->files('dropped_images')) {
                            $i = array_search($url, $dropped_images['name']);
                            if (!$this->Images->uploadAddImage($dropped_images['tmp_name'][$i], $dropped_images['name'][$i], $post->id, 'post')) {
                                $this->Design->assign('message_error', 'error uploading image');
                            }
                        }
                    }
                }
            }
        }


        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($post))) {

            $post = $this->Blog->getPost(intval($id));

            if (empty($post->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }

            // Изображения
            $post->images = $this->Images->getImages($post->id, 'post');

            // SEO keywords
            $seo_keywords = $this->Seo->getKeywords($post->id, 'post');
        }
        

        // Меню
        $menus = $this->Pages->get_menus();
        $this->Design->assign('menus', $menus);

        $this->Design->assign('seo_keywords', $seo_keywords);
        $this->Design->assign('post', $post);

        return $this->Design->fetch('content/post.tpl');
    }
}
