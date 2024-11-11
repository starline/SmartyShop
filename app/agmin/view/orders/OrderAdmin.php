<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class OrderAdmin extends Auth
{
    private $entity_params = array(
        'id' => 'integer',
        'name' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'address' => 'string',
        'comment' => 'string',
        'note' => 'string',
        'discount' => 'float',
        'coupon_discount' => 'float',
        'delivery_id' => 'integer',
        'delivery_note' => 'string',
        'delivery_price' => 'float',
        'payment_method_id' => 'integer',
        'user_id' => 'integer',
        'manager_id' => 'integer',
        'separate_delivery' => 'boolean',
        'paid' => 'boolean'
        // $status - не передаем, он определяется отдельно
        // $order_settings - настройки не передаем, их нужно завернуть в json
    );

    // подключаем плагин Smarty
    public function __construct()
    {
        parent::__construct();
        $this->Design->smarty->registerPlugin("function", "get_payment_module_html", array($this, 'payment_module_plugin'));
        $this->Design->smarty->registerPlugin("function", "get_delivery_module_html", array($this, 'delivery_module_plugin'));
    }

    public function fetch()
    {

        $order = new stdClass();
        $prev_order = new stdClass();

        $do_redirect = false;

        $total =  new stdClass();
        $total->weight = 0;
        $total->purchases = 0;
        $total->purchases_price = 0;

        $payment_method = new stdClass();
        $order_labels = array();
        $payments = array();
        $order_settings = array();

        $purchases = array();



        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions = array(
            'orders' => $this->entity_params
        );



        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $order = $this->postDataAcces($data_permissions);
            $order = $this->Misc->trimEntityProps($order, array('name', 'email', 'phone', 'address', 'delivery_note', 'coupon_discount', 'comment'));

            //print_r($order);

            // Если выбрали "заказ оплачен", но не выбрали способ оплаты - отменим оплату
            if (!empty($order->paid) and !$order->payment_method_id) {
                $this->Design->assign('message_error', 'error_paid');
                $order->paid = 0;
            }

            // Преобразуем настройки модулей в json
            if ($order_settings = $this->Request->post('order_settings')) {
                foreach ($order_settings as $param_name => $param_value) {
                    $order_settings[$param_name] = str_replace('"', "''", $param_value); // Двойные ковычки не сохраняются в json
                }
                $order->settings = serialize($order_settings);
            }

            // Определяем покупателя по телефону/email, если нет, добавляем нового
            if ((!empty($order->phone) or !empty($order->email)) and empty($order->user_id)) {

                // Выбираем пользователя по номеру телефона
                if ($user = $this->Users->getUser(['phone' => $order->phone])) {
                    $order->user_id = $user->id;
                }

                // Выбираем пользователя по email
                elseif ($user = $this->Users->getUser(['email' => $order->email])) {
                    $order->user_id = $user->id;
                }

                // если не найден, создаем нового
                elseif (!empty($order->name)) {
                    $user = new stdClass();
                    $user->name = $order->name;
                    $user->email = $order->email;
                    $user->phone = $order->phone;
                    $user->enabled = 1;
                    $order->user_id = $this->Users->addUser($user);
                }
            }

            // Создаем новый заказ
            if (empty($order->id)) {

                // Новый, созданый заказ закрепляем за менеджером
                $order->manager_id = $this->user->id;

                // Определяем оплату доставки
                if (!empty($order->delivery_id)) {
                    $delivery_method = $this->OrdersDelivery->getDeliveryMethod($order->delivery_id);
                    if (empty($order->separate_delivery) and !empty($delivery_method->separate_payment)) {
                        $order->separate_delivery = $delivery_method->separate_payment;
                    }
                    if (empty($order->delivery_price)) {
                        $order->delivery_price = $delivery_method->price;
                    }
                }

                if (!empty($order->id = $this->Orders->addOrder($order))) {
                    $_SESSION['message_success'] = 'added';
                    $do_redirect = true;
                } else {
                    $this->Design->assign('message_error', 'Что-то пошло не так');
                }
            }

            // Обновляем заказ
            else {
                if (empty($order->manager_id)) {
                    $order->manager_id = $this->user->id; // Пользователь текущей сессии
                }

                $this->Orders->update_order($order->id, $order);
                $_SESSION['message_success'] = 'updated';
            }

            // Обновляем метки заказа
            $order_labels = !empty($this->Request->post('order_labels')) ? $this->Request->post('order_labels') : array();
            $this->OrdersLabels->update_order_labels($order->id, $order_labels);


            /////////////////////
            // Сохраняем Покупки
            ////////////////////
            if ($this->Request->post('purchases')) {
                foreach ($this->Request->post('purchases') as $var_name => $var_arr) {
                    foreach ($var_arr as $index => $value) {
                        if (empty($purchases[$index])) {
                            $purchases[$index] = new stdClass();
                        }
                        $purchases[$index]->$var_name = $value;
                    }
                }
            }

            $posted_purchases_ids = array();
            foreach ($purchases as $purchase) {
                $variant = $this->ProductsVariants->getVariant($purchase->variant_id);

                // Не добавляем товар с нулевым кол-вом
                if (!empty($purchase->amount)) {
                    $purchase_arr = array('amount' => $purchase->amount);

                    // Обновляем существующий вариант товара в заказе
                    if (!empty($purchase->id)) {
                        if (!empty($variant)) { // если исходный вариант существует

                            // Если параметр не задан, берется с исходного варианта товара
                            $purchase_arr = array_merge($purchase_arr, array('variant_id' => $variant->id, 'variant_name' => $variant->name, 'sku' => $variant->sku));
                            if ($this->access("products_price") and isset($purchase->price)) {
                                $purchase_arr['price'] = $purchase->price;
                            }
                        } else { // Если исходный вариант удален, не существует
                            if ($this->access("products_price") and isset($purchase->price)) {
                                $purchase_arr['price'] = $purchase->price;
                            }
                        }

                        $this->Orders->updatePurchase($purchase->id, $purchase_arr);
                    }

                    // Добавляем новый вариант
                    else {
                        $purchase_arr = array_merge($purchase_arr, array('order_id' => $order->id, 'variant_id' => $purchase->variant_id));
                        if ($this->access("products_price") and isset($purchase->price)) {
                            $purchase_arr['price'] = $purchase->price;
                        }
                        $purchase->id = $this->Orders->addPurchase($purchase_arr);
                    }

                    $posted_purchases_ids[] = $purchase->id;
                }
            }

            // Удалить непереданные товары
            foreach ($this->Orders->getPurchases(array('order_id' => $order->id)) as $p) {
                if (!in_array($p->id, $posted_purchases_ids)) {
                    $this->Orders->delete_purchase($p->id);
                }
            }

            // Отсортировать варианты
            asort($posted_purchases_ids);
            $i = 0;
            foreach ($posted_purchases_ids as $purchases_id) {
                $this->Orders->updatePurchase($posted_purchases_ids[$i], array('position' => $purchases_id));
                $i++;
            }

            // Обновляем общую стоимость и прибыль, комиссию менеджера
            $this->Orders->update_total_price($order->id, false);


            ////////////////////////////////////////////
            // Статус заказа, обновление склада товаров
            ///////////////////////////////////////////
            $order_status = $this->Request->post('status', 'string');

            // Новый
            if ($order_status == 0) {
                if (!$this->Orders->open(intval($order->id))) {
                    $this->Design->assign('message_error', 'error_open');
                } else {
                    $this->Orders->update_order($order->id, array('status' => 0));
                }
            }
            // Принят
            elseif ($order_status == 1) {
                if (!$this->Orders->close(intval($order->id))) {
                    $this->Design->assign('message_error', 'error_closing');
                } else {
                    $this->Orders->update_order($order->id, array('status' => 1));
                }
            }
            // Отгружен
            elseif ($order_status == 4) {
                if (!$this->Orders->close(intval($order->id))) {
                    $this->Design->assign('message_error', 'error_closing');
                } else {
                    $this->Orders->update_order($order->id, array('status' => 4));
                }
            }
            // Выполнен
            elseif ($order_status == 2) {
                if (!$this->Orders->close(intval($order->id))) {
                    $this->Design->assign('message_error', 'error_closing');
                } else {
                    $this->Orders->update_order($order->id, array('status' => 2));
                }
            }
            // Отмена
            elseif ($order_status == 3) {
                if (!$this->Orders->open(intval($order->id))) {
                    $this->Design->assign('message_error', 'error_open');
                } else {
                    $this->Orders->update_order($order->id, array('status' => 3));
                }
            }

            // Выбираем даные по заказу.
            $order = $this->Orders->getOrder(intval($order->id));



            //////////////////////////////
            // Создаем платежи в Финансах автоматически
            // Для заказов после даты обновления алгоритма
            /////////////////////////////
            if (strtotime($order->date) > strtotime('2024-03-01')) {

                // Платеж по заказу (Выручка). Если заказ оплачен и выбран способ оплаты и сумма оплаты > 0
                $order_payment_income = $this->Finance->get_payment_by_order($order->id, 'income');
                if (!empty($order->paid) and !empty($order->payment_method_id) and $order->payment_price > 0) {

                    $payment_method = $this->OrdersPayment->getPaymentMethod($order->payment_method_id);

                    // В настйках способа оплаты должжен быть указан колешел
                    if (!empty($payment_method->finance_purse_id)) {

                        $payment_income = new stdClass();
                        $payment_income->finance_category_id = $this->Settings->income_finance_category_id;     // Категория платежжа. Выручка
                        $payment_income->type = 1;	                                                            // Тип платежа. Приход
                        $payment_income->manager_id = $this->user->id;	                                        // Пользователь из сессии
                        $payment_income->purse_id = $payment_method->finance_purse_id;                          // Выбрать соответсвующий кошелек

                        // Пересчитываем финансовый платеж. Если настройка задана в способе оплаты
                        $payment_income_calculate = $order->payment_price;
                        if (!empty($payment_method->settings->calculate_finance_payment)) {
                            if (!empty($payment_method->settings->fee_inside)) {
                                $payment_income_calculate = $payment_income_calculate - $payment_income_calculate * ($payment_method->settings->fee_inside / 100);
                            }

                            // Сначало вычисляем проценты, затем отнимаем платежи
                            if (!empty($payment_method->settings->fee_fix_inside)) {
                                $payment_income_calculate = $payment_income_calculate - $payment_method->settings->fee_fix_inside;
                            }
                        }

                        // Переводим в валюту кошелька
                        $payment_method_purse = $this->Finance->get_purse($payment_method->finance_purse_id);
                        $payment_income->amount = $this->Money->priceConvert($payment_income_calculate, intval($payment_method_purse->currency_id), false);

                        // Если платеж уже внесен в финансы
                        if (!empty($order_payment_income->id)) {

                            // Обновляем. Если платеж не сверен бухгалтером
                            if (empty($order_payment_income->verified)) {
                                $payment_income->id = $order_payment_income->id;
                                $this->Finance->update_payment($payment_income->id, $payment_income);
                            }
                        } else {
                            $payment_income->id = $this->Finance->add_payment($payment_income);

                            // Добавляем контрагента "заказ"
                            $contractor = new stdClass();
                            $contractor->payment_id = $payment_income->id;
                            $contractor->entity_id = $order->id;
                            $contractor->entity_name = 'order';
                            $this->Finance->add_contractor($contractor);
                        }
                    }
                }

                // Удалим платеж (приход)
                // Eсли заказа не оплачен и не сверен или не выбран способ оплаты или не выбран кошелек оплаты
                if (!empty($order_payment_income->id) and empty($order_payment_income->verified) and (empty($order->paid) || empty($order->payment_method_id) || empty($payment_method->finance_purse_id))) {
                    $this->Finance->deletePayment($order_payment_income->id);
                }


                // Расход на доставку. Если в стоимость заказа включена доставка
                $order_payment_expense = $this->Finance->get_payment_by_order($order->id, 'expense');
                if (empty($order->separate_delivery) and !empty($order->delivery_id) and $order->delivery_price > 0) {

                    $delivery_method = $this->OrdersDelivery->getDeliveryMethod($order->delivery_id);

                    if (!empty($delivery_method->finance_purse_id)) {

                        $payment_expense = new stdClass();
                        $payment_expense->finance_category_id = $this->Settings->expense_finance_category_id;   // Категория платежа. Расход на доставку
                        $payment_expense->type = 0;	                                                            // Тип платежа. Расход
                        $payment_expense->manager_id = $this->user->id;	                                        // Пользователь из сессии
                        $payment_expense->purse_id = $delivery_method->finance_purse_id;                        // Выбрать соответсвующий кошелек

                        // Переводим в валюту кошелька
                        $delivery_method_purse = $this->Finance->get_purse($delivery_method->finance_purse_id);
                        $payment_expense->amount = $this->Money->priceConvert($order->delivery_price, intval($delivery_method_purse->currency_id), false);

                        // Если платеж уже внемен в финансы
                        if (!empty($order_payment_expense->id)) {

                            // Обновляем. Если платеж не сверен бухгалтером
                            if (empty($order_payment_expense->verified)) {
                                $payment_expense->id = $order_payment_expense->id;
                                $this->Finance->update_payment($payment_expense->id, $payment_expense);
                            }
                        } else {
                            $payment_expense->id = $this->Finance->add_payment($payment_expense);

                            // Добавляем контрагента "заказ"
                            $contractor = new stdClass();
                            $contractor->payment_id = $payment_expense->id;
                            $contractor->entity_id = $order->id;
                            $contractor->entity_name = 'order';
                            $this->Finance->add_contractor($contractor);
                        }
                    }
                }

                // Удалим платеж (расход)
                if (!empty($order_payment_expense->id)  and empty($order_payment_expense->verified) and (!empty($order->separate_delivery) || empty($order->delivery_id) || empty($delivery_method->finance_purse_id) || $order->delivery_price == 0)) {
                    $this->Finance->deletePayment($order_payment_expense->id);
                }
            }


            // Отправляем письмо пользователю
            if ($this->Request->post('notify_user') and !empty($order->email)) {

                // Send email to User
                $this->UsersNotify->sendNotify('Email', 'newOrderToUser', [
                  'order_id' => $order->id,
                  'to_email' => $order->email,
                  'from_name' => $this->Settings->company_name
                ]);
            }
        }

        // Делаем редирект на страницу с ID
        if ($do_redirect) {
            $this->Misc->makeRedirect($this->Request->url(array('id' => $order->id)), '301');
        }

        $this->Design->assign('message_success', $this->Misc->getSessionMessage('message_success'));



        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($order))) {

            $order = $this->Orders->getOrder($id);

            if (empty($order->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }

            $total->payments = 0;

            // Выбираем активные Метки заказа
            foreach ($this->OrdersLabels->get_order_labels($order->id) as $ol) {
                $order_labels[] = $ol->id;
            }

            // Выбираем товары заказа
            if (!empty($purchases = $this->Orders->getPurchases(array('order_id' => $order->id)))) {

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

                        // Общий вес
                        $total->weight += $purchase->variant->weight * $purchase->amount;
                    }

                    // Общая стоимость товаров. Без учета скидок
                    $total->purchases_price += $purchase->price * $purchase->amount;
                    $total->purchases += $purchase->amount;
                }
            } else {
                $purchases = array();
            }

            $this->Design->assign('purchases', $purchases);


            // Выбранный cпособ доставки
            $delivery = $this->OrdersDelivery->getDeliveryMethod($order->delivery_id);
            $this->Design->assign('delivery', $delivery);

            // Выбранный Способ оплаты
            $payment_method = $this->OrdersPayment->getPaymentMethod($order->payment_method_id);
            if (!empty($payment_method)) {

                // Валюта оплаты
                $payment_currency = $this->Money->getCurrency(intval($payment_method->currency_id));
                $this->Design->assign('payment_currency', $payment_currency);

                // Выбираем настройки способа оплаты
                $payment_settings = $this->OrdersPayment->getPaymentMethodSettings($payment_method->id);
                $payment_method->settings = $payment_settings;
            }

            // Выбранный Пользователь
            if ($order->user_id) {
                $this->Design->assign('order_user', $this->Users->getUser($order->user_id));
            }

            // Выбранный Менеджер
            if (!empty($order->manager_id)) {
                $order_manager = $this->Users->getUser($order->manager_id);
                $order_manager->interest_price = $order->interest_price;
                if (intval($order_manager->discount) > 0 and intval($order->total_price) > 0) {
                    $real_manager_discount = ($order_manager->interest_price / $order->total_price) * 100;
                    $order_manager->interest_discount =  $real_manager_discount;
                }

                $this->Design->assign('order_manager', $order_manager);
            }

            //  Выбираем платежи
            $rel_payments = $this->Finance->get_payments_by_order($order->id);
            foreach ($rel_payments as $rel_payment) {
                $payment = $this->Finance->get_payment($rel_payment->payment_id);

                // Если платеж не в основной валюте BUG
                if ($payment->currency_rate > 1) {
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

                // Выбираем контрагента
                $contractor = $this->Finance->get_contractor(intval($payment->id));
                if (isset($contractor->entity_name)) {
                    $contractor->view_name = $this->Misc->getViewAdmin($contractor->entity_name);
                }
                $payments[$payment->id] = $payment;
                $payments[$payment->id]->contractor = $contractor;
            }

            // Выбираем предыдущий заказ
            $prev_order = $this->Orders->get_prev_order($order->id, $order->status);
        }


        // Статус
        if (!isset($order->status)) {
            $order->status = 0;
        }

        //  Определяем возможность редактировать
        $can_edit = false;
        if (($order->status != 2 and $order->status != 3) || $this->Users->checkUserAccess($this->user, "orders_edit")) {
            $can_edit = true;
        }

        // Все способы доставки
        // Потом в шаблоне .tpl выберем какой отображать
        $deliveries = $this->OrdersDelivery->getDeliveryMethods();

        // Все способы оплаты
        // Потом в шаблоне .tpl выберем какой отображать
        $payment_methods = $this->OrdersPayment->getPaymentMethods();

        // Все Метки заказов
        $labels = $this->OrdersLabels->get_labels();

        $this->Design->assign('prev_order', $prev_order);
        $this->Design->assign('order', $order);
        $this->Design->assign('status', $order->status);
        $this->Design->assign('total', $total);
        $this->Design->assign('labels', $labels);
        $this->Design->assign('order_labels', $order_labels);
        $this->Design->assign('payment_method', $payment_method);
        $this->Design->assign('payment_methods', $payment_methods);
        $this->Design->assign('payments', $payments);
        $this->Design->assign('deliveries', $deliveries);
        $this->Design->assign('can_edit', $can_edit);

        // Показываем распечатку
        if ($this->Request->get('type') == 'print') {
            return $this->Design->fetch('orders/order_print.tpl');
        } else {
            return $this->Design->fetch('orders/order.tpl');
        }
    }


    /**
     * Форма оплаты
     * Smarty Plugin
     */
    public function payment_module_plugin($params)
    {
        return $this->OrdersPayment->getPaymentModuleHtml($params);
    }


    /**
     * Выводим модуль доставки
     * Smarty Plugin
     */
    public function delivery_module_plugin($params)
    {
        return $this->OrdersDelivery->getDeliveryModuleHtml($params);
    }
}
