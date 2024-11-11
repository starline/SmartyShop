<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @author Artem Sabelnikov
 *
 * При переводе выбираем два связаных платежа
 */

if (!defined('secure')) {
    exit('Access denied');
}

class FinancePaymentAdmin extends Auth
{
    public function fetch()
    {

        $payment = new stdClass();
        $rel_payment = new stdClass();
        $contractor = new stdClass();
        $current_currency = null;
        $cur_type = $this->Request->get('cur_type', 'string');


        // Типы контрагента
        $contractor_types = array(
            ['name' => 'Заказ', 				'entity_name' => 'order', 					'search' => 'search_orders'],
            ['name' => 'Пользователь', 			'entity_name' => 'user', 					'search' => 'search_users'],
            ['name' => 'Складское перемещение', 'entity_name' => 'wh_movement', 			'search' => 'search_movements']
        );

        $entity_options = array(
            "id" => "integer",
            "amount" => "float",
            "finance_category_id" => "integer",
            "type" => "integer",
            "purse_id" => "integer",
            "purse_to_id" => "integer",
            "comment" => "string",
            "currency_amount" => "float",
            "currency_rate" => "float",
            "verified" => "boolean"
        );

        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions =  array(
            "finance" => $entity_options
        );


        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $payment = $this->postDataAcces($data_permissions);

            // Если платеж верефицирован, определяем пользователя
            if (!empty($payment->verified)) {
                $payment->verified_user_id  = $this->user->id;
            }

            // Если перевод, создаем второй платеж
            if (!empty($payment->purse_to_id)) {
                $rel_payment = clone $payment;
                $rel_payment->purse_id = $payment->purse_to_id;
                unset($rel_payment->purse_to_id);
            }

            // Очищаем purse_to_id - этого параметра нет в базе
            unset($payment->purse_to_id);


            //////////////////
            // Создаем платеж
            /////////////////
            if (empty($payment->id)) {

                // Менеджер создавший
                $payment->manager_id  = $this->user->id;

                // Тип платежа по-умолчанию "Расход", при создании перевода
                $payment->type = isset($payment->type) ? $payment->type : 0;
                if (!empty($payment->id = $this->Finance->add_payment($payment))) {

                    // Если перевод, создаем второй платеж
                    if (!empty($rel_payment->purse_id)) {

                        $rel_payment->related_payment_id = $payment->id;

                        // Пересчитываем по курсу
                        $rel_payment->amount = $payment->currency_amount;
                        $rel_payment->currency_amount = $payment->amount;
                        $rel_payment->currency_rate = $rel_payment->currency_amount / $rel_payment->amount;
                        $rel_payment->type = ($payment->type == 1) ? 0 : 1;
                        $rel_payment->manager_id = $payment->manager_id;

                        //

                        $rel_payment->id = $this->Finance->add_payment($rel_payment);

                        if (!empty($rel_payment->id)) {
                            $this->Finance->update_payment($payment->id, array("related_payment_id" => $rel_payment->id));
                        }
                    }

                    $this->Design->assign('message_success', 'added');
                } else {
                    $this->Design->assign('message_error', 'error add');
                }


                ////////////////////
                // Обновляем платеж
                ///////////////////
            } else {
                if (!empty($this->Finance->update_payment($payment->id, $payment))) {

                    $payment = $this->Finance->get_payment(intval($payment->id));

                    // Если перевод
                    if (!empty($rel_payment->purse_id)) {
                        $rel_payment->related_payment_id = $payment->id;
                        $rel_payment->id = $payment->related_payment_id;

                        // пересчитываем по курсу
                        $rel_payment->amount = $payment->currency_amount;
                        $rel_payment->currency_amount = $payment->amount;

                        // На ноль делить нельзя
                        if ($rel_payment->amount > 0) {
                            $rel_payment->currency_rate = $rel_payment->currency_amount / $rel_payment->amount;
                        }

                        if ($payment->type == 1) {
                            $rel_payment->type = 0; // расход
                        } else {
                            $rel_payment->type = 1; // приход
                        }

                        $this->Finance->update_payment($rel_payment->id, $rel_payment);
                    }

                    $this->Design->assign('message_success', 'updated');
                } else {
                    $this->Design->assign('message_error', 'error update');
                }
            }

            // Обработка связи с сущностью
            if (!is_null($this->Request->post('entity_name', 'string')) and !is_null($this->Request->post('entity_id', 'integer'))) {
                $contractor->payment_id = $payment->id;
                $contractor->entity_id = $this->Request->post('entity_id');
                $contractor->entity_name = $this->Request->post('entity_name');
                $this->Finance->add_contractor($contractor);
            } else {
                $this->Finance->delete_contractor($payment->id);
            }


            // Удаление изображений
            $images = (array)$this->Request->post('images');
            $current_images = $this->Images->getImages($payment->id, 'payment');
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
                    if (!$this->Images->uploadAddImage($images['tmp_name'][$i], $images['name'][$i], $payment->id, 'payment')) {
                        $this->Design->assign('message_error', 'error uploading image');
                    }
                }
            }

            // Загрузка изображений из интернета и drag-n-drop файлов
            if ($images = $this->Request->post('images_urls')) {
                foreach ($images as $url) {

                    // Если не пустой адрес и файл не локальный
                    if (!empty($url) && $url != 'http://' && strstr($url, '/') !== false) {
                        $this->Images->addImage($payment->id, 'payment', $url);
                    } elseif ($dropped_images = $this->Request->files('dropped_images')) {
                        $key = array_search($url, $dropped_images['name']);

                        // Ужимаем изображение до заданого размера
                        if ($key !== false && $image_name = $this->Images->uploadImage($dropped_images['tmp_name'][$key], $dropped_images['name'][$key], 1400, 1400)) {
                            $this->Images->addImage($payment->id, 'payment', $image_name);
                        }
                    }
                }
            }
        }


        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($payment))) {

            $payment = $this->Finance->get_payment(intval($id));

            if (empty($payment->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }

            // Изображения
            $payment->images = $this->Images->getImages($payment->id, 'payment');

            if (!empty($payment->related_payment_id)) {
                $rel_payment = $this->Finance->get_payment(intval($payment->related_payment_id));
                $cur_type = 2;
            }

            $contractor = $this->Finance->get_contractor(intval($payment->id));
        }


        //////////////////////
        //------- View Create
        /////////////////////
        else {

            // Определяем предопределенного контрагента
            if (!is_null($this->Request->get('contractor_entity_name', 'string')) and !is_null($this->Request->get('contractor_entity_id', 'integer'))) {
                $contractor->entity_name = $this->Request->get('contractor_entity_name', 'string');
                $contractor->entity_id = $this->Request->get('contractor_entity_id', 'integer');
                $contractor = $this->Finance->set_contractor_name($contractor);
            }
        }


        // Устанавливаем module
        if (isset($contractor->entity_name)) {
            $contractor->view_name = $this->Misc->getViewAdmin($contractor->entity_name);
        }

        if (!empty($payment->manager_id)) {
            $payment->manager = $this->Users->getUser($payment->manager_id);
        } else {
            $payment->manager = $this->user;
        }

        if (!empty($payment->verified_user_id)) {
            $payment->verified_user = $this->Users->getUser($payment->verified_user_id);
        }

        $purses = $this->Finance->getPurses();             // Выбрать кошелек
        $categories = $this->Finance->get_categories();     // Выбрать категорию
        $currencies = $this->Money->getCurrencies();       // Выбираем валюты

        $to_currency = $this->Money->getMainCurrency();
        if (isset($payment->id)) {
            if (isset($cur_type) and $cur_type == 2 and isset($rel_payment)) {
                $current_purse = $this->Finance->get_purse($payment->purse_id);
                $current_currency = $this->Money->getCurrency((int)$current_purse->currency_id);
                $to_purse = $this->Finance->get_purse($rel_payment->purse_id);
                $to_currency = $this->Money->getCurrency((int)$to_purse->currency_id);
            } else {
                $current_purse = $this->Finance->get_purse($payment->purse_id);
                $current_currency = $this->Money->getCurrency((int)$current_purse->currency_id);
            }
        } else {
            $current_currency = $this->Money->getCurrency((int)$purses[0]->currency_id);
        }

        $this->Design->assign('payment', $payment);
        $this->Design->assign('rel_payment', $rel_payment);
        $this->Design->assign('purses', $purses);

        $this->Design->assign('current_currency', $current_currency);
        $this->Design->assign('to_currency', $to_currency);

        $this->Design->assign('categories', $categories);
        $this->Design->assign('currencies', $currencies);
        $this->Design->assign('contractor', $contractor);
        $this->Design->assign('contractor_types', $contractor_types);
        $this->Design->assign('cur_type', $cur_type);

        // Отображение
        return $this->Design->fetch('finance/payment.tpl');
    }
}
