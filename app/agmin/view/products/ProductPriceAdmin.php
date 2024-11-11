<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 *
 * ProductPriceAdmin
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class ProductPriceAdmin extends Auth
{
    public function fetch()
    {

        // Определяем обьекты
        $variants = array();
        $pricelists = array();
        $images = array();
        $related_products = array();
        $product = new stdClass();

        $orders = array();
        $orders_count = null;
        $orders_price = null;

        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions = array(
            'products_price' => array(
                'id' => 'integer',
                'visible' => 'boolean',
                'disable' => 'boolean',
                'featured' => 'boolean',
                'sale' => 'boolean'
            )
        );

        $variant_default = array(
            "weight" => 0,
            "old_price" => 0,
            "cost_price" => 0,
            "price" => 0,
            "provider_id" => null,
            "merchant_id" => ''
        );

        $checkboxes = array(
            "custom" => 0,
            "awaiting" => 0
        );


        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $product = $this->postDataAcces($data_permissions);

            //print_r($product);

            $this->Products->update_product($product->id, $product);
            $this->Design->assign('message_success', 'updated');

            $product = $this->Products->get_product($product->id);

            // Варианты товара
            if ($this->Request->post('variants')) {
                foreach ($this->Request->post('variants') as $parameter => $values) {
                    foreach ($values as $index => $value) {
                        if (empty($variants[$index])) {
                            $variants[$index] = new stdClass();
                        }

                        // Раcпределяем Checkbox по вариантам
                        if ($parameter == 'awaiting' or $parameter == 'custom') {
                            foreach ($variants as $index_v => $var) {
                                if ($value == $var->id) {
                                    $variants[$index_v]->$parameter = 1;
                                }
                            }
                        } else {
                            $variants[$index]->$parameter = $value;
                        }
                    }
                }
            }

            //print_r($variants);

            $variants_ids = array();
            foreach ($variants as &$variant) {

                // Преобразовываем дату datapiker для mysql
                if (!empty($variant->awaiting_date)) {
                    $variant->awaiting_date = date('Y-m-d', strtotime($variant->awaiting_date));
                }

                // Устанавливаем значения по-умолчанию для варианта товара
                foreach ($variant_default as $default_name => $default_value) {
                    if (isset($variant->$default_name)) {
                        $variant->$default_name = empty($variant->$default_name) ? $default_value : $variant->$default_name;
                    }
                }

                // Устанавливаем значения для checkbox
                foreach ($checkboxes as $checkbox_name => $checkbox_value) {
                    $variant->$checkbox_name = empty($variant->$checkbox_name) ? $checkbox_value : $variant->$checkbox_name;
                }

                if (!empty($variant->id)) {
                    $this->ProductsVariants->update_variant($variant->id, $variant);
                } else {
                    $variant->product_id = $product->id;
                    $variant->id = $this->ProductsVariants->add_variant($variant);
                }

                $variant = $this->ProductsVariants->getVariant(intval($variant->id));

                if (!empty($variant->id)) {
                    $variants_ids[] = $variant->id;
                }
            }

            // Удалить непереданные варианты
            $current_variants = $this->ProductsVariants->getVariants(array('product_id' => intval($product->id)));
            foreach ($current_variants as $current_variant) {
                if (!in_array(intval($current_variant->id), $variants_ids)) {
                    $this->ProductsVariants->delete_variant($current_variant->id);
                }
            }

            // Отсортировать  варианты
            asort($variants_ids);
            $i = 0;
            foreach ($variants_ids as $variant_id) {
                $this->ProductsVariants->update_variant($variants_ids[$i], array('position' => $variant_id));
                $i++;
            }

            // Связанные товары
            // Удаляем все связанные товары
            $this->Products->delete_all_related_products($product->id);

            if (is_array($this->Request->post('related_products'))) {
                $pos = 0;
                foreach ($this->Request->post('related_products') as $rel_id) {
                    $this->Products->add_related_product($product->id, $rel_id, $pos++);
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

            // Варианты товара
            $variants = $this->ProductsVariants->getVariants(array('product_id' => $product->id), array('merchant'));

            // Связанные товары
            $related_products = $this->Products->get_related_products($product->id);
            if (!empty($related_products)) {
                foreach ($this->Products->get_products(array('id' => array_keys($related_products)), array("image")) as $rel_product) {
                    $related_products[$rel_product->id] = $rel_product;
                }
            }

            // Выбираем заказы с этим товаром
            $orders_filter = array('product_id' => $product->id);
            $orders = $this->Orders->getOrders($orders_filter);

            // Товары заказа
            foreach ($this->Orders->getPurchases(array('order_id' => array_keys($orders)), array("image")) as $op) {
                $orders[$op->order_id]->purchases[] = $op;
            }

            // Кол-во заказов
            $orders_count = $this->Orders->getOrdersCount($orders_filter);

            // Выбираем общую сумму заказов
            $orders_price = $this->Orders->getOrdersPrice($orders_filter);
        }

        // Все поставщики
        $providers = $this->Providers->get_providers();
        $this->Design->assign('providers', $providers);

        // Все прайсы
        $pricelists = $this->ProductsMerchants->getPriceLists();
        $this->Design->assign('merchants', $pricelists);

        $this->Design->assign('product', $product);
        $this->Design->assign('product_variants', $variants);
        $this->Design->assign('related_products', $related_products);

        $this->Design->assign('orders', $orders);
        $this->Design->assign('orders_count', $orders_count);
        $this->Design->assign('orders_price', $orders_price);

        return $this->Design->fetch('products/product_price.tpl');
    }
}
