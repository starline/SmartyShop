<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 * Корзина покупок
 * Этот класс использует шаблон cart.tpl
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class CartView extends View
{
    public function __construct()
    {
        parent::__construct();

        // Если передан id варианта, добавим его в корзину
        if ($variant_id = $this->Request->get('variant')) {
            $amount = $this->Request->get('amount', 'integer') ? $this->Request->get('amount', 'integer') : 1;
            $this->Cart->addCartProduct($variant_id, $amount);
            $this->Misc->makeRedirect($this->Config->root_url . '/cart', '301');
        }

        // Удаление товара из корзины
        if ($delete_variant_id = intval($this->Request->get('delete_variant'))) {
            $this->Cart->deleteItem($delete_variant_id);
            $this->Misc->makeRedirect($this->Config->root_url . '/cart', '301');
        }

        // Если нам запостили amounts, обновляем их
        if ($amounts = $this->Request->post('amounts', 'array')) {
            foreach ($amounts as $variant_id => $amount) {
                $this->Cart->updateItem($variant_id, $amount);
            }
            $this->Misc->makeRedirect($this->Config->root_url . '/cart', '301');
        }
    }


    public function fetch()
    {
        // Закрываем от индексации
        $this->Design->assign('noindex', true);

        // Выбираем товары корзины
        $cart = $this->Cart->getCart();
        $purchases = $this->Cart->getCartPurchases($cart->id);

        $subtotal = 0;
        $purchases_count = 0;

        $variants_ids = [];
        $products_ids = [];
        foreach ($purchases as $purchase) {
            $variants_ids[] = $purchase->variant_id;
            $products_ids[] = $purchase->product_id;
        }

        $products = $this->Products->get_products(array('id' => $products_ids, 'limit' => count($products_ids)));

        $images = $this->Images->getImages($products_ids, 'product');
        foreach ($images as $image) {
            $products[$image->entity_id]->images[] = $image;
        }

        $variants = $this->ProductsVariants->getVariants(array('id' => $variants_ids));
        foreach ($variants as $variant) {
            $products[$variant->product_id]->variants[] = $variant;
        }

        foreach ($purchases as &$purchase) {
            if (!empty($products[$purchase->product_id])) {
                $purchase->product = $products[$purchase->product_id];
            }

            if (!empty($variants[$purchase->variant_id])) {
                $purchase->variant = $variants[$purchase->variant_id];
            }

            // Общая стоимость товаров. Без учета скидок
            $subtotal += $purchase->price * $purchase->amount;
            $purchases_count += $purchase->amount;
        }

        $this->Design->assign('cart', $cart);
        $this->Design->assign('purchases', $purchases);
        $this->Design->assign('purchases_count', $purchases_count);
        $this->Design->assign('subtotal', $subtotal);

        // Выводим checkout
        if ($this->Request->get('step') == 'checkout') {

            $order = new stdClass();

            // Данные пользователя
            if (!empty($this->user)) {
                $last_order = $this->Orders->getOrders(array('user_id' => $this->user->id, 'limit' => 1));
                $last_order = reset($last_order);
                if ($last_order) {
                    $order->name =      $last_order->name;
                    $order->email =     $last_order->email;
                    $order->phone =     $last_order->phone;
                    $order->address =   $last_order->address;
                } else {
                    $order->name = $this->user->name;
                    $order->email = $this->user->email;
                }
            }

            // Делаем первичную проверку данных
            if ($this->Request->post()) {

                $order->payment_method_id   = $this->Request->post('payment_method_id', 'integer');
                $order->delivery_id         = $this->Request->post('delivery_id', 'integer');
                $order->name                = $this->Request->post('name', 'string');
                $order->phone               = $this->Request->post('phone', 'string');
                $order->email               = $this->Request->post('email');
                $order->address             = $this->Request->post('address', 'string');
                $order->comment             = $this->Request->post('comment', 'string');
                $order->ip                  = $_SERVER['REMOTE_ADDR'];

                // Купон
                if (!empty($coupon_code = trim($this->Request->post('coupon_code', 'string')))) {
                    $coupon = $this->UsersCoupons->getCoupon((string)$coupon_code);
                    if (empty($coupon) || !$coupon->valid) {
                        $this->Cart->applyCoupon('');
                        $this->Design->assign('coupon_error', 'invalid');
                    } else {
                        $this->Cart->applyCoupon($coupon_code);
                    }
                }

                $cart = $this->Cart->getCart();

                // Обновляем данные корзины
                $this->Design->assign('cart', $cart);

                $order->discount = $this->user->discount;
                if ($cart->coupon) {
                    $order->coupon_discount = $cart->coupon_discount;
                    $order->coupon_code = $cart->coupon->code;
                }

                // Сохраняем значения form на случай ошибки
                $this->Design->assign('order', $order);


                //////////////////////////////
                // Если нажали оформить заказ
                /////////////////////////////
                if ($this->Request->post('checkout')) {

                    // Если определен пользователь, закрепляем заказ за ним
                    if (!empty($this->user->id)) {
                        $order->user_id = $this->user->id;
                    }

                    // Убираем пробелы в номере телефона и добавляем +38
                    $order->phone = $this->Misc->clearPhoneNummber($order->phone);

                    if (empty($order->phone)) {
                        $this->Design->assign('error', 'empty_phone');
                    } else {

                        // Если есть телефон и нет авторизации
                        if (!empty($order->phone) and empty($this->user->id)) {

                            // проверяем пользователя по номеру телефона
                            if ($existing_user = $this->Users->getUser(['phone' => $order->phone])) {

                                $order->user_id = $existing_user->id;

                                // Заполняем имя из имени пользователя, если пусто
                                if (empty($order->name) and !empty($existing_user->name)) {
                                    $order->name = $existing_user->name;
                                }

                                // Заполняем email из имени пользователя, если пусто
                                if (empty($order->email) and !empty($existing_user->email)) {
                                    $order->email = $existing_user->email;
                                }

                                // Проверим по номеру email
                            } elseif (!empty($order->email) and $existing_user = $this->Users->getUser(['email' => $order->email])) {
                                $order->user_id = $existing_user->id;

                                // если такого пользователя нет, создаем его
                            } else {

                                $user = new \stdClass();
                                $user->name = $order->name;
                                $user->email = $order->email;
                                $user->phone = $order->phone;
                                $user->enabled = 1;

                                $order->user_id = $this->Users->addUser($user);
                            }
                        }

                        // Добавляем заказ в базу
                        $order_id = $this->Orders->addOrder($order);
                        $_SESSION['order_id'] = $order_id;

                        // Если использовали купон, увеличим количество его использований
                        if ($cart->coupon) {
                            $this->UsersCoupons->updateCoupon($cart->coupon->id, array('usages' => $cart->coupon->usages + 1));
                        }

                        // Добавляем товары к заказу
                        foreach ($cart->purchases as $purchase) {
                            $this->Orders->addPurchase(array('order_id' => $order_id, 'variant_id' => $purchase->variant_id, 'amount' => $purchase->amount));
                        }

                        $order = $this->Orders->getOrder($order_id);

                        // Определяем стоимость доставки
                        $delivery = $this->OrdersDelivery->getDeliveryMethod($order->delivery_id);
                        if (!empty($delivery) && ($delivery->free_from > $order->total_price || $delivery->free_from == 0)) {
                            $this->Orders->update_order($order->id, array('delivery_price' => $delivery->price, 'separate_delivery' => $delivery->separate_payment));
                        }

                        // Обновляем общую стоимость и прибыль, комиссию менеджера
                        $this->Orders->update_total_price($order->id);


                        // Send email to User
                        $this->UsersNotify->sendNotify('Email', 'newOrderToUser', [
                            'order_id' => $order->id,
                            'to_email' => $order->email
                        ]);


                        // Send Some Notification to Admin. Telegram|Email|SMS|...
                        $this->UsersNotify->sendNotifyToManager('newOrderToAdmin', [
                            'order_id' => $order->id
                        ]);

                        // Отправляем смс с реквизитами и суммой к оплате
                        // Отправляем увидомление в бот telegram

                        // Очищаем корзину (сессию)
                        $this->Cart->emptyCart();

                        // Перенаправляем на страницу заказа
                        $this->Misc->makeRedirect($this->Config->root_url . '/order/' . $order->url, '301');
                    }
                }
            }

            // Если существуют валидные купоны, нужно вывести инпут для купона
            if ($this->UsersCoupons->countCoupons(array('valid' => 1)) > 0) {
                $this->Design->assign('coupon_request', true);
            }

            // Способы доставки
            $deliveries = $this->OrdersDelivery->getDeliveryMethods(array('enabled' => 1));
            $this->Design->assign('deliveries', $deliveries);

            // Варианты оплаты
            $payment_methods = $this->OrdersPayment->getPaymentMethods(array('enabled' => 1, 'enabled_public' => 1));
            $this->Design->assign('payment_methods', $payment_methods);

            // Все валюты
            $this->Design->assign('all_currencies', $this->Money->getCurrencies());

            return $this->Design->fetch('cart_checkout.tpl');
        }

        // Выводим корзину
        else {

            // Определеям тип запрос
            $this->Design->assign('is_ajax', $this->Misc->isAjax());

            return $this->Design->fetch('cart.tpl');
        }
    }
}
