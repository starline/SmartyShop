<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 2.2
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class WarehouseMovementAdmin extends Auth
{
    public function fetch()
    {

        $movement = new stdClass();

        $total =  new stdClass();
        $total->purchases = 0;

        $payments = array();
        $purchases = array();

        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions =  array(
            "warehouse_edit" => array(
                "id" => "integer",
                "note" => "string",
                "note_logist" => "string",
                "awaiting_date" => null
                // status не передаем, он определяется отдельно
            ),
            "warehouse_add" => array(
                "id" => "integer",
                "note" => "string",
                "note_logist" => "string",
                "awaiting_date" => null
            )
        );

        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')  and ($this->access("warehouse_edit") or $this->access("warehouse_add"))) {

            $movement = $this->postDataAcces($data_permissions);

            //print_r($movement);

            // Преобразовываем дату datapiker для mysql
            if (!empty($movement->awaiting_date)) {
                $movement->awaiting_date = date('Y-m-d', strtotime($movement->awaiting_date));
            }

            // Создаем новую поставку/списание
            if (empty($movement->id)) {

                // Закрепляем за менеджером
                $movement->manager_id = $this->user->id;

                $movement->id = $this->Warehouse->add_movement($movement);
                $this->Design->assign('message_success', 'added');
            }


            // BUG: Если разрешено только добавлять поставки,
            // можно редактировать свои поставки со статусом 0 (новое)
            // или свои поставки в течении сессии

            // Обновляем поставку
            // id - проверяется через права
            if (!empty($movement->id)) {

                $this->Warehouse->update_movement($movement->id, $movement);
                $this->Design->assign('message_success', 'updated');

                // Закупки/списание товаров
                if ($this->Request->post('purchases')) {
                    foreach ($this->Request->post('purchases') as $n => $var) { // id, variant_id, amount
                        foreach ($var as $i => $v) {
                            if (empty($purchases[$i])) {
                                $purchases[$i] = new stdClass();
                            }
                            $purchases[$i]->$n = $v;
                        }
                    }
                }

                $posted_purchases_ids = array();
                foreach ($purchases as $purchase) {
                    $variant = $this->ProductsVariants->getVariant($purchase->variant_id);
                    $purchase_arr = array('amount' => $purchase->amount);

                    // Обновляем существующий вариант товара в заказе
                    if (!empty($purchase->id)) {
                        if (!empty($variant)) { # если исходный вариант существует

                            // Если параметр не задан, берется с исходного варианта товара
                            $purchase_arr = array_merge($purchase_arr, array('variant_id' => $purchase->variant_id, 'variant_name' => $variant->name, 'sku' => $variant->sku));
                            if ($this->access("products_price") and isset($purchase->cost_price)) {
                                $purchase_arr['cost_price'] = $purchase->cost_price;
                            }
                        } else { # Если исходный вариант удален, не существует
                            if ($this->access("products_price") and isset($purchase->cost_price)) {
                                $purchase_arr['cost_price'] = $purchase->cost_price;
                            }
                        }

                        $this->Warehouse->updatePurchase($purchase->id, $purchase_arr);
                    }

                    // Добавляем новый вариант товара
                    else {
                        $purchase_arr = array_merge($purchase_arr, array('movement_id' => $movement->id, 'variant_id' => $purchase->variant_id));
                        if ($this->access("products_price") and isset($purchase->cost_price)) {
                            $purchase_arr['cost_price'] = $purchase->cost_price;
                        }
                        $purchase->id = $this->Warehouse->addPurchase($purchase_arr);
                    }

                    $posted_purchases_ids[] = $purchase->id;
                }

                // Удалить непереданные товары
                foreach ($this->Warehouse->getPurchases(array('movement_id' => $movement->id)) as $p) {
                    if (!in_array($p->id, $posted_purchases_ids)) {
                        $this->Warehouse->delete_purchase($p->id);
                    }
                }

                // Отсортировать  варианты
                asort($posted_purchases_ids);
                $i = 0;
                foreach ($posted_purchases_ids as $purchases_id) {
                    $this->Warehouse->updatePurchase($posted_purchases_ids[$i], array('position' => $purchases_id));
                    $i++;
                }


                //////////////////////////////////////////////
                // Cтатус перемещения, обновление склада товаров
                //////////////////////////////////////////////
                $mov_status = $this->Request->post('status', 'string');

                // новый
                if ($mov_status == 0) {
                    if (!$this->Warehouse->open(intval($movement->id))) {
                        $this->Design->assign('message_error', 'error_open');
                    } else {
                        $this->Warehouse->update_movement($movement->id, array('status' => 0));
                    }

                    // ожидаем
                } elseif ($mov_status == 1) {

                    // UPDATE: обновляем даты поставки. Также нужно проверять дату поставки, когда купили последний товар.

                    if (!$this->Warehouse->open(intval($movement->id))) {
                        $this->Design->assign('message_error', 'error_open');
                    } else {
                        $this->Warehouse->update_movement($movement->id, array('status' => 1));
                    }

                    // выполнен/добавлен на склад
                } elseif ($mov_status == 2) {
                    if (!$this->Warehouse->close(intval($movement->id))) {
                        $this->Design->assign('message_error', 'error_closing');
                    } else {
                        $this->Warehouse->update_movement($movement->id, array('status' => 2));
                    }

                    // Списан
                } elseif ($mov_status == 3) {
                    if (!$this->Warehouse->close(intval($movement->id), true)) {
                        $this->Design->assign('message_error', 'error_closing');
                    } else {
                        $this->Warehouse->update_movement($movement->id, array('status' => 3));
                    }


                    // Отменен
                } elseif ($mov_status == 4) {
                    if (!$this->Warehouse->open(intval($movement->id))) {
                        $this->Design->assign('message_error', 'error_open');
                    } else {
                        $this->Warehouse->update_movement($movement->id, array('status' => 4));
                    }
                }
            }


            // Удаление изображений
            $images = (array)$this->Request->post('images');
            $current_images = $this->Images->getImages($movement->id, 'warehouse');
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
                    if (!$this->Images->uploadAddImage($images['tmp_name'][$i], $images['name'][$i], $movement->id, 'warehouse')) {
                        $this->Design->assign('message_error', 'error uploading image');
                    }
                }
            }

            // Загрузка изображений из интернета и drag-n-drop файлов
            if ($images = $this->Request->post('images_urls')) {
                foreach ($images as $url) {

                    // Если не пустой адрес и файл не локальный
                    if (!empty($url) && $url != 'http://' && strstr($url, '/') !== false) {
                        $this->Images->addImage($movement->id, 'warehouse', $url);
                    } elseif ($dropped_images = $this->Request->files('dropped_images')) {
                        $key = array_search($url, $dropped_images['name']);

                        // Ужимаем изображение до заданого размера
                        if ($key !== false && $image_name = $this->Images->uploadImage($dropped_images['tmp_name'][$key], $dropped_images['name'][$key], 1400, 1400)) {
                            $this->Images->addImage($movement->id, 'warehouse', $image_name);
                        }
                    }
                }
            }
        }


        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($movement))) {

            // Выбираем данные заказа
            $movement = $this->Warehouse->get_movement(intval($id));

            if (empty($movement->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }

            $total->wholesale = 0;
            $total->retail = 0;
            $total->weight = 0;
            $total->payments = 0;

            // Изображения
            $movement->images = $this->Images->getImages($movement->id, 'warehouse');

            // Выбираем товары заказа
            if ($purchases = $this->Warehouse->getPurchases(array('movement_id' => $movement->id))) {

                // Покупки
                $products_ids = array();
                $variants_ids = array();

                foreach ($purchases as $purchase) {
                    $products_ids[] = $purchase->product_id;
                    $variants_ids[] = $purchase->variant_id;
                }

                $products = $this->Products->get_products(array('id' => $products_ids));

                $images = $this->Images->getImages($products_ids, 'product');
                foreach ($images as $image) {
                    $products[$image->entity_id]->images[] = $image;
                }

                $variants = $this->ProductsVariants->getVariants(array('product_id' => $products_ids));
                foreach ($variants as $variant) {

                    // Выбираем поставки товаров
                    $variant->movements = $this->Warehouse->get_product_movements($variant->id);
                    $mov_amount = 0;
                    foreach ($variant->movements as $mov) {
                        $mov_amount += $mov->amount;
                    }
                    $variant->movements_amount = $mov_amount;

                    if (!empty($products[$variant->product_id])) {
                        $products[$variant->product_id]->variants[] = $variant;
                    }
                }

                foreach ($purchases as &$purchase) {
                    if (!empty($products[$purchase->product_id])) {
                        $purchase->product = $products[$purchase->product_id];
                    }

                    if (!empty($variants[$purchase->variant_id])) {
                        $purchase->variant = $variants[$purchase->variant_id];
                    }

                    // Подсчитываем общую стоимость
                    if (!empty($purchase->variant)) {
                        $total->wholesale += $purchase->cost_price * $purchase->amount; // Вычисляем по себестоимости поставки
                        $total->retail += $purchase->variant->price * $purchase->amount;

                        // Вычисляеми вес
                        $total->weight += $purchase->variant->weight * $purchase->amount;
                    }

                    $total->purchases += $purchase->amount;
                }
            }


            //  Выбираем платежи
            $rel_payments = $this->Finance->get_warehouse_payments($movement->id);
            foreach ($rel_payments as $pay) {
                $payment = $this->Finance->get_payment($pay->payment_id);

                // Если платеж не восновной валюте
                if ($payment->currency_amount > 0) {
                    $add_payment = $payment->currency_amount;
                } else {
                    $add_payment = $payment->amount;
                }

                // Учитываем расход или приход
                if ($payment->type == 0) {
                    $total->payments -= $add_payment;
                } else {
                    $total->payments += $add_payment;
                }

                $payments[] = $payment;
            }


            // Выбранный Менеджер
            if (!empty($movement->manager_id)) {
                $movement->manager = $this->Users->getUser($movement->manager_id);
            }
        }


        /////////////////
        //------- Create
        ////////////////
        else {

            // Статус
            if (!isset($movement->status)) {
                $movement->status = 0;
            }
        }

        //  Определяем возможность редактировать
        $can_edit = false;
        if ((in_array('warehouse_add', $this->user->permissions) and $movement->status == 0) or in_array('warehouse_edit', $this->user->permissions)) {
            $can_edit = true;
        }

        $this->Design->assign([
            'movement' => $movement,
            'purchases' => $purchases,
            'total' => $total,
            'payments' => $payments,
            'can_edit' => $can_edit
        ]);

        // Выводим на экран
        if ($this->Request->get('type') == 'print') {
            return $this->Design->fetch('warehouse/warehouse_movement_print.tpl');
        } else {
            return $this->Design->fetch('warehouse/warehouse_movement.tpl');
        }
    }
}
