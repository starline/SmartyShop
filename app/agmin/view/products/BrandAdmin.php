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

class BrandAdmin extends Auth
{
    private $allowed_image_extentions = array('png', 'gif', 'jpg', 'jpeg', 'ico');
    private $entity_params = array(
        "id" => "integer",
        "name" => "string",
        "featured" => "boolean",
        "description" => "string",
        "url" => "string",
        "meta_title" => "string",
        "meta_description" => "string"
    );

    public function fetch()
    {

        $brand = new stdClass();
        $do_redirect = false;

        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions =  array(
            "products_brands" => $this->entity_params
        );



        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $brand = $this->postDataAcces($data_permissions);

            // Не допустить одинаковые URL разделов.
            if (!empty($check_brand = $this->ProductsBrands->get_brand($brand->url)) && $check_brand->id != $brand->id) {
                $this->Design->assign('message_error', 'url_exists');
            } else {
                if (empty($brand->id)) {
                    if (!empty($brand->id = $this->ProductsBrands->add_brand($brand))) {

                        // message_success - передаем через $_SESSION
                        $_SESSION['message_success'] = 'added';
                        $do_redirect = true;

                    } else {
                        $this->Design->assign('message_error', 'Что-то пошло не так');
                    }
                } else {
                    if ($this->ProductsBrands->update_brand($brand->id, $brand)) {
                        $_SESSION['message_success'] = 'updated';
                    } else {
                        $this->Design->assign('message_error', 'Что-то пошло не так');
                    }
                }

                // Удаление изображения
                if ($this->Request->post('delete_image')) {
                    $this->ProductsBrands->deleteImage($brand->id);
                }

                // Загрузка изображения
                $image = $this->Request->files('image');
                if (!empty($image['name']) && in_array(strtolower(pathinfo($image['name'], PATHINFO_EXTENSION)), $this->allowed_image_extentions)) {
                    $this->ProductsBrands->deleteImage($brand->id);
                    move_uploaded_file($image['tmp_name'], $this->Config->root_dir . $this->Config->images_brands_dir . $image['name']);
                    $this->ProductsBrands->update_brand($brand->id, array('image' => $image['name']));
                }


            }
        }

        // Делаем редирект на страницу с ID
        if ($do_redirect) {
            $this->Misc->makeRedirect($this->Request->url(array('id' => $brand->id)), '301');
        }

        $this->Design->assign('message_success', $this->Misc->getSessionMessage('message_success'));



        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($brand))) {
            $brand = $this->ProductsBrands->get_brand($id);

            if (empty($brand->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }
        }

        $this->Design->assign('brand', $brand);

        return  $this->Design->fetch('products/brand.tpl');
    }
}
