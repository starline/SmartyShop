<?php

/**
 * GoodGin CMS - The Best of gins
 * Скрипты
 *
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class ScriptsAdmin extends Auth
{
    public function fetch()
    {

        // Обработка действий
        if ($this->Request->method('post')) {
            switch ($this->Request->post('action')) {


                // Telegram Bot
                case 'script': {
                    $result[] = 'No result';


                    // Make token for all users
                    if (0) {
                        $users = $this->Users->getUsers();
                        foreach ($users as $user) {
                            $token = $this->Misc->getToken($user->id);
                            $this->Users->updateUser($user->id, ['token' => $token]);
                        }
                    }



                    // Send message
                    if (0) {

                        $result = $this->UsersNotify->sendNotifyToManager('newOrderToAdmin', [
                            'order_id' => 5172
                        ]);

                        /*$result = $this->UsersNotify->sendNotify('Turbosms', 'deliveryTrackNumber', [
                            'order_id' => 5172,
                            'chat_id' => 500510211
                        ]);*/

                        /*$result = $this->UsersNotify->sendNotify('Email', 'newOrderToAdmin', [
                            'order_id' => 5172,
                            'to_email' => 'aa.guzhva@gmail.com',
                            'from_name' => $this->Settings->company_name
                        ]);*/
                    }

                    if (0) {
                        $result = trigger_error('my error', E_USER_WARNING);
                        function myErrorHandler($errno, $errstr, $errfile, $errline)
                        {
                            print "[$errno] $errstr";
                        }

                        // set to the user defined error handler
                        set_error_handler("myErrorHandler");
                    }


                    if (0) {

                        // PHP_EOL - перевод строки
                        $obj = new stdClass();
                        if (empty($obj)) {
                            $result = 'Object empty';
                        }
                        $result[] = $obj;

                        $message_params = array();
                        $message_params['var1'] = 'var1 - old value';
                        $message_params['var1'] = isset($message_params['parse_mode']) ? $message_params['parse_mode'] : 'var1 - new value';
                        $result[] = $message_params['var1'];

                        $message_params['var2'] = 'var2 - old value';
                        $message_params['var2'] = $message_params['var2'] ?: 'var2 - new value';
                        $result[] = $message_params['var2'];

                        /*$settings = null;
                        if (isset($settings) and is_null($settings)) {
                            $result[] = 'get NULL';
                        }*/
                    }


                    // Threw function
                    if (0) {
                        $arr = ['name1' => 1, '$name2' => 2 ];
                        function bridge(&$params)
                        {
                            $params['name1'] = 3;
                        }
                        bridge($arr);
                        $result[] = $arr['name1'];
                    }


                    // Trim String
                    if (0) {

                        // if $var != '' || NULL || false return $var
                        // if $var undefined return EROOR
                        // trim('   ') return empty
                        $var = '  f ';
                        if (isset($var) and !empty(trim($var))) {
                            $result[] = trim($var) ?: 'no';
                        } else {
                            $result[] = 'empty';
                        }
                    }


                    // SET webhook
                    if (0) {
                        $bot_api_key  = $this->Config->telegram_bot_api_key;
                        $bot_username = $this->Config->telegram_bot_name;
                        $hook_url     = $this->Config->root_url . '/exchange/telegram/webhook.php';

                        try {
                            // Create Telegram API object
                            $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

                            // Set webhook
                            $vk_result = $telegram->setWebhook($hook_url);
                            if ($vk_result->isOk()) {
                                $result[] = vk_result->getDescription();
                            }
                        } catch (Longman\TelegramBot\Exception\TelegramException $e) {
                            // log telegram errors
                            // echo $e->getMessage();
                        }
                    }


                    $result  = print_r($result, true);
                    $this->Design->assign('result', $result);
                    break;
                }


                    // Подбираем сопутсвующие товары
                case 'related_products': {

                    // Выбираем все товары, активные
                    $products = $this->Products->get_products(array("visible" => true));
                    //$products = $this->Products->get_products(array("visible" => true, "id" => 434));

                    foreach ($products as $product) {

                        // Выбираем все текущие связанные товары
                        //$cur_rel_products = $this->Products->get_related_products($product->id);
                        $cur_rel_products = array();

                        // Выбираем Все товары в выполненных заказах
                        $purchases_ids = array();
                        $orders_done = $this->Orders->getOrders(array('product_id' => $product->id));
                        if (!empty($orders_done)) {
                            $purchases = $this->Orders->getPurchases(array("order_id" => array_keys($orders_done)));
                            foreach ($purchases as $pur) {
                                $purchases_ids[] = $pur->product_id;
                            }
                        }

                        // Соединяем выбранные товары. убираем дубликаты
                        $rel_products_ids = array_unique(array_merge(array_keys($cur_rel_products), $purchases_ids));

                        // Выбираем все товары в категории
                        // Если товаров меньше чем в настройках, выбираем все товары родительской категории
                        $category_products = array();
                        $parent_category_products = array();
                        if (count($rel_products_ids) < $this->Settings->rel_products_num) {
                            $category_products = $this->Products->get_products(array("visible" => true, "in_stock" => true, "category_id" => $product->category_id));
                            $rel_products_ids = array_unique(array_merge($rel_products_ids, array_keys($category_products)));

                            if (count($rel_products_ids) < $this->Settings->rel_products_num and !empty($product->category_id)) {
                                $category = $this->ProductsCategories->get_category(intval($product->category_id));
                                $parent_category = $this->ProductsCategories->get_category(intval($category->parent_id));
                                if (!empty($parent_category->children)) {
                                    $parent_category_products = $this->Products->get_products(array("visible" => true, "in_stock" => true, "category_id" => $parent_category->children));
                                    $rel_products_ids = array_unique(array_merge($rel_products_ids, array_keys($parent_category_products)));
                                }
                            }
                        }

                        // Проверяем что товары в наличии, активны, продажи. Сортировка по рентабельности
                        $active_products = $this->Products->get_products(array("id" => $rel_products_ids, "visible" => true, "in_stock" => true, "top" => true, "date_from" => date('Y-m-d', strtotime('-180 days'))));
                        $rel_products_ids = array_keys($active_products);

                        // Если товаров мало, добавляем
                        if (count($rel_products_ids) < $this->Settings->rel_products_num) {
                            $rel_products_ids = array_unique(array_merge($rel_products_ids, array_keys($cur_rel_products)));
                            if (count($rel_products_ids) < $this->Settings->rel_products_num) {
                                $rel_products_ids = array_unique(array_merge($rel_products_ids, $purchases_ids));
                                if (count($rel_products_ids) < $this->Settings->rel_products_num) {
                                    $rel_products_ids = array_unique(array_merge($rel_products_ids, array_keys($category_products)));
                                    if (count($rel_products_ids) < $this->Settings->rel_products_num) {
                                        $rel_products_ids = array_unique(array_merge($rel_products_ids, array_keys($parent_category_products)));
                                    }
                                }
                            }
                        }

                        // Проверяем что товары в наличии, активны
                        $new_active_products = $this->Products->get_products(array("id" => $rel_products_ids, "visible" => true, "in_stock" => true));
                        $rel_products_ids = array_unique(array_merge(array_keys($active_products), array_keys($new_active_products)));

                        // Удаляем текущий товар из выбрки
                        foreach ($rel_products_ids as $index => $value) {
                            if ($value == $product->id) {
                                unset($rel_products_ids[$index]);
                            }
                        }

                        // Обрезаем выборку до максимального кол-ва для показа
                        $rel_products_ids = array_slice($rel_products_ids, 0, $this->Settings->rel_products_num + 2);
                        //print_r($rel_products_ids);

                        // Удаляем все связанные товары
                        $this->Products->delete_all_related_products($product->id);

                        // Записываем новые связаные товары
                        $pos = 0;
                        foreach ($rel_products_ids as $rel_id) {
                            $this->Products->add_related_product($product->id, $rel_id, $pos++);
                        }
                    }
                    break;
                }


                    // Обнуляем весь склад поставщиков
                    // У которых стоит отметка "разрешить обнуление склада"
                case 'restore_providers_stock': {

                    // Выбрать всех поставщиков
                    $providers = $this->Providers->get_providers();

                    foreach ($providers as $key => $value) {

                        // Кроме aliexpress
                        if (!$value->no_restore_price) {
                            $provider_ids[] = $value->id;
                        }
                    }

                    $this->ProductsVariants->restore_stock(array('stock' => 0), array("provider_ids" => $provider_ids));
                    break;
                }


                    // Обнуляем весь склад
                case 'restore_stock': {

                    $this->ProductsVariants->restore_stock(array('stock' => 0));
                    break;
                }


                    // Исправляем базу заказов
                case 'restore_orders': {
                    $orders = $this->Orders->getOrders();
                    foreach ($orders as $order) {

                        // Просчитываем payment_price
                        if (0) {
                            $this->Orders->update_total_price($order->id, false);
                        }

                        // Переносим поле sms_payment_info в settings
                        if (0 and isset($order->sms_payment_info) and $order->sms_payment_info == 1) {
                            $order->settings->payment_sms = $order->sms_payment_info;
                            $order_settings = serialize((array)$order->settings);
                            $this->Orders->update_order($order->id, array('settings' => $order_settings), false);
                        }

                        // Переносим поле sms_delivery_note в settings
                        if (0 and isset($order->sms_delivery_note) and $order->sms_delivery_note == 1) {
                            $order->settings->delivery_sms = $order->sms_delivery_note;
                            $order_settings = serialize((array)$order->settings);
                            $this->Orders->update_order($order->id, array('settings' => $order_settings), false);
                        }

                        // Переносим поле delivery_info в settings
                        if (0 and !empty($order->delivery_info)) {
                            $order->settings->delivery_info = $order->delivery_info;
                            $order_settings = serialize((array)$order->settings);
                            $this->Orders->update_order($order->id, array('settings' => $order_settings), false);
                        }
                    }
                    break;
                }


                    // Создаем пользователей по данным из заказов
                case 'create_user_by_order': {

                    $new_users = array();

                    // Выбрать все заказы
                    $orders = $this->Orders->getOrders();

                    // Перебираем заказы
                    foreach ($orders as $order) {

                        // Ищем пользователя по телефону заказа, если не установлен покупатель
                        if (!empty($order->phone) and empty($order->user_id)) {
                            $user = $this->Users->getUser(['phone' => $order->phone]);

                            // Если нет пользователя, создаем его
                            if (empty($user)) {

                                $user = new stdClass();

                                if (!empty($order->name)) {
                                    $user->name = $order->name;
                                } else {
                                    $user->name = $order->phone;
                                }

                                $user->email = $order->email;
                                $user->phone = $order->phone;
                                $user->enabled = 1;

                                $user->id = $this->Users->addUser($user);

                                $new_users[] = $user;
                            }

                            $update_order = new stdClass();
                            $update_order->user_id = $user->id;

                            // Сохраняем владельца заказа
                            $this->Orders->update_order($order->id, $update_order);
                        }
                    }

                    $this->Design->assign('new_users', $new_users);
                    break;
                }


                    // Check php settings
                case 'php_check': {
                    $php_check = new stdClass();

                    $php_check->version = phpversion();

                    if (extension_loaded('apc') && ini_get('apc.enabled')) {
                        $php_check->apc = ini_get('apc.shm_size');
                    }

                    $php_check->default_charset = ini_get('default_charset');
                    $php_check->short_open_tag = ini_get('short_open_tag');
                    $php_check->display_errors = ini_get('display_errors');

                    // func_overload
                    $php_check->func_overload = ini_get('mbstring.func_overload');

                    // 1 - если работает func_overload(2)
                    // 2 - если func_overload(0)
                    $length = strlen("\xd0\xa2");
                    if ($length != 1 and $php_check->func_overload == 2) {
                        $php_check->func_overload = "str function doesn't overload to mbstring";
                    }

                    $this->Design->assign('php_check', $php_check);
                    break;
                }
            }
        }


        return $this->Design->fetch('settings/scripts.tpl');
    }

}
