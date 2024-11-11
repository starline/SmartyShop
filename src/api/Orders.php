<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 2.5
 *
 * Обновляем Оптовую цену в покупках
 * UPDATE s_orders_purchases SET cost_price = (SELECT cost_price FROM __products_variants WHERE id = s_orders_purchases.variant_id)
 *
 */

namespace GoodGin;

class Orders extends GoodGin
{
    /**
     * Выбрать определенный заказ
     * @param int|string $id
     */
    public function getOrder(int|string $id = null): object|null
    {
        if (empty($id)) {
            return false;
        }

        if (is_int($id)) {
            $where_id = $this->Database->placehold(' AND o.id=? ', intval($id));
        } else {
            $where_id = $this->Database->placehold(' AND o.url=? ', strval($id));
        }

        $query = $this->Database->placehold(
            "SELECT 
				o.* 
			FROM 
				__orders o 
            WHERE
                1 
			    $where_id 
			LIMIT 
                1"
        );

        $this->Database->query($query);
        $order = $this->Database->result();

        // Преобразуем json в object
        // Если преобразовывать пустую переменную, в обьект добавляется "scalar"
        if (!empty($order->settings)) {
            $order->settings = (object) unserialize($order->settings);
        }

        return $order;
    }


    /**
     * Выбрать список заказов
     * @param array $filter
     * @param $select = false|count|sum
     * @param array $join = array("payment_method", "delivery_method")
     */
    public function getOrders(array $filter = array(), $select = false, array $join = array())
    {

        // Pages view
        $sql_limit = "";
        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
            $page = 1;
            if (isset($filter['page'])) {
                $page = max(1, intval($filter['page']));
            }
            $sql_limit = $this->Database->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);
        }

        $where_payment_method = '';
        if (isset($filter['payment_method_id'])) {
            $where_payment_method = $this->Database->placehold(' AND o.payment_method_id = ?', intval($filter['payment_method_id']));
        }

        $where_delivery_method = '';
        if (isset($filter['delivery_id'])) {
            $where_delivery_method = $this->Database->placehold(' AND o.delivery_id = ?', intval($filter['delivery_id']));
        }

        $where_paid = '';
        if (isset($filter['paid'])) {
            $where_paid = $this->Database->placehold(' AND o.paid = ?', intval($filter['paid']));
        }

        $where_status = '';
        if (isset($filter['status'])) {
            $where_status = $this->Database->placehold(' AND o.status = ?', intval($filter['status']));
        }

        $where_id = "";
        if (isset($filter['id'])) {
            if (!empty($filter['id'])) {
                $where_id = $this->Database->placehold(' AND o.id in(?@)', (array)$filter['id']);
            } else {
                return array();
            }
        }

        $where_user = "";
        if (isset($filter['user_id'])) {
            if (!empty($filter['user_id'])) {
                $where_user = $this->Database->placehold(' AND o.user_id = ?', intval($filter['user_id']));
            } else {
                return array();
            }
        }

        $where_modified_since = '';
        if (isset($filter['modified_since'])) {
            $where_modified_since = $this->Database->placehold(' AND o.modified > ?', $filter['modified_since']);
        }

        $where_label = '';
        $join_label = '';
        if (!empty($filter['label'])) {
            $where_label = $this->Database->placehold(" AND ol.label_id = ?", $filter['label']);
            $join_label = $this->Database->placehold(" LEFT JOIN __orders_labels_related AS ol ON o.id=ol.order_id ");
        }

        $where_product = '';
        $join_product = '';
        if (isset($filter['product_id'])) {
            if (!empty($filter['product_id'])) {
                $where_product = $this->Database->placehold(' AND op.product_id = ?', intval($filter['product_id']));
                $join_product = $this->Database->placehold(" LEFT JOIN __orders_purchases AS op ON o.id=op.order_id ");
            } else {
                return array();
            }
        }

        // Ищем заказ по ID/phone/address/name
        $where_keyword = "";
        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $where_keyword .= $this->Database->placehold(' AND (o.id = "' . $this->Database->escape(trim($keyword)) . '" OR o.name LIKE "%' . $this->Database->escape(trim($keyword)) . '%" OR REPLACE(o.phone, "-", "")  LIKE "%' . $this->Database->escape(str_replace('-', '', trim($keyword))) . '%" OR o.address LIKE "%' . $this->Database->escape(trim($keyword)) . '%" )');
            }
        }


        // JOIN PAYMENT_METHOD
        $select_payment_method = '';
        $join_payment_method = '';
        if (in_array("payment_method", $join)) {
            $select_payment_method = $this->Database->placehold(", pm.name as payment_method_name");
            $join_payment_method =  $this->Database->placehold("LEFT JOIN __orders_payment_methods pm ON pm.id=o.payment_method_id");
        }


        // JOIN DELIVERY_METHOD
        $select_delivery_method = '';
        $join_delivery_method = '';
        if (in_array("delivery_method", $join)) {
            $select_delivery_method = $this->Database->placehold(", dm.name as delivery_method_name");
            $join_delivery_method =  $this->Database->placehold("LEFT JOIN __orders_delivery dm ON dm.id=o.delivery_id");
        }


        // Выбираем заказы
        if (!$select) {
            $query = $this->Database->placehold(
                "SELECT 
					o.id,
                    o.delivery_id,
                    o.delivery_price,
                    o.delivery_note,
                    o.delivery_info,
                    o.separate_delivery,
                    o.payment_method_id,
                    o.status,
                    o.paid,
                    o.closed,
                    o.user_id,
                    o.name,
                    o.address,
                    o.phone,
                    o.email,
                    o.comment,
                    o.note,
                    o.url,
                    o.ip,
                    o.total_price,
                    o.profit_price,
                    o.interest_price,
                    o.payment_price,
                    o.discount,
                    o.coupon_discount,
                    o.coupon_code,
                    o.date,
                    o.modified,
                    o.manager_id,
                    o.settings 
                    $select_payment_method
                    $select_delivery_method
				FROM 
					__orders AS o 
					$join_label
                    $join_product
                    $join_payment_method
                    $join_delivery_method
				WHERE 
					1
					$where_id 
					$where_status 
					$where_payment_method 
                    $where_delivery_method
					$where_user 
					$where_keyword 
					$where_label
                    $where_product 
					$where_modified_since 
					$where_paid
				ORDER BY
					id DESC 
					$sql_limit"
            );

            $this->Database->query($query);

            $orders = array();
            foreach ($this->Database->results() as $order) {

                // Преобразуем json в object
                // Если преобразовывать пустую переменную, в обьект добавляется "scalar"
                if (!empty($order->settings)) {
                    $order->settings = (object) unserialize($order->settings);
                } else {
                    $order->settings = new \stdClass();
                }

                $orders[$order->id] = $order;
            }

            return $orders;


            // Выбираем кол-во
        } elseif ($select == 'count') {
            $query = $this->Database->placehold(
                "SELECT 
					COUNT(DISTINCT o.id) as count
				FROM 
					__orders AS o 
					$join_label
                    $join_product
				WHERE
					1
					$where_id
					$where_status
					$where_payment_method 
                    $where_delivery_method 
					$where_user 
					$where_keyword
					$where_label
                    $where_product  
					$where_modified_since
					$where_paid"
            );

            $this->Database->query($query);
            return $this->Database->result('count');


            // Выбираем общую стоимость заказов
        } elseif ($select == 'sum') {
            $query = $this->Database->placehold(
                "SELECT 
                    SUM(o.total_price) as sum_total_price, 
                    SUM(o.profit_price) as sum_profit_price 
                FROM 
                    __orders AS o 
                    $join_label 
                    $join_product
                WHERE 
                    1 
                    $where_id 
                    $where_status 
                    $where_payment_method 
                    $where_delivery_method 
                    $where_user 
                    $where_keyword 
                    $where_label 
                    $where_product 
                    $where_modified_since
                    $where_paid"
            );

            $this->Database->query($query);
            return $this->Database->result();
        }
    }


    /**
     * Выбираем кол-во заказов
     * @param array $filter
     * @return int
     */
    public function getOrdersCount(array $filter = array())
    {
        return $this->getOrders($filter, 'count');
    }


    /**
     * Выбираем общую сумму заказов
     * @param array $filter
     * @return object ($sum_total_price, $sum_profit_price)
     */
    public function getOrdersPrice(array $filter = array())
    {
        return $this->getOrders($filter, 'sum');
    }


    /**
     * Обновление заказа
     * @param $id
     * @param $order
     * @param $modified
     * @return $id - ID заказа
     */
    public function update_order(int $id, $order, $modified = true)
    {
        $order = $this->Misc->cleanEntityId($order);

        // Убираем пробелы в номере телефона и добавляем +
        if (!empty($order->phone)) {
            $order->phone = $this->Misc->clearPhoneNummber($order->phone);
        }

        $set_modified = "";
        if ($modified) {
            $set_modified = $this->Database->placehold(", modified=now() ");
        }

        $query = $this->Database->placehold(
            "UPDATE 
				__orders 
			SET 
				?%
				$set_modified 
			WHERE 
				id=? 
			LIMIT 
				1",
            $order,
            intval($id)
        );

        if ($this->Database->query($query)) {
            return $id;
        } else {
            return false;
        }
    }


    /**
     * Удаляем заказ
     * @param int $id
     */
    public function delete_order(int $id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("DELETE FROM __orders_purchases WHERE order_id=?", $id);
        $this->Database->query($query);

        $query = $this->Database->placehold("DELETE FROM __orders_labels_related WHERE order_id=?", $id);
        $this->Database->query($query);

        $query = $this->Database->placehold("DELETE FROM __orders WHERE id=? LIMIT 1", $id);
        $this->Database->query($query);

        return $this->Finance->delete_payments_by_order($id);
    }


    /**
     * Добавляем заказ
     * @param $order
     */
    public function addOrder($order)
    {
        $order = $this->Misc->cleanEntityId($order);

        $order->url = md5(uniqid($this->Config->salt, true));
        $order->date = date("Y-m-d H:i:s");

        // Убираем пробелы в номере телефона
        if (isset($order->phone)) {
            $order->phone = $this->Misc->clearPhoneNummber($order->phone);
        }

        $query = $this->Database->placehold(
            "INSERT INTO 
                __orders 
            SET 
                ?%",
            $order
        );

        $this->Database->query($query);
        return $this->Database->getInsertId();
    }


    public function get_purchase($id)
    {
        $query = $this->Database->placehold("SELECT * FROM __orders_purchases WHERE id=? LIMIT 1", intval($id));
        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Выбираем товары в заказе
     * @param Array $filter
     * @param $join = array('image')
     */
    public function getPurchases($filter = array(), $join = array())
    {

        $order_id_filter = '';
        if (isset($filter['order_id'])) {
            if (!empty($filter['order_id'])) {
                $order_id_filter = $this->Database->placehold('AND op.order_id in(?@)', (array)$filter['order_id']);
            } else {
                return array();
            }
        }

        // JOIN IMAGE
        $select_image = '';
        $join_image = '';
        if (in_array("image", $join)) {
            $select_image = $this->Database->placehold(", i.filename as image_filename");
            $join_image =  $this->Database->placehold("LEFT JOIN __content_images i ON i.entity_id=op.product_id AND i.entity_name='product' AND i.position=(SELECT MIN(position) FROM __content_images WHERE entity_id=op.product_id and entity_name='product')");
        }

        $query = $this->Database->placehold(
            "SELECT 
                op.id,
                op.order_id,
                op.product_id,
                op.variant_id,
                op.product_name,
                op.variant_name,
                op.price,
                op.cost_price,
                op.amount,
                op.sku
                $select_image
            FROM 
                __orders_purchases op
                $join_image
            WHERE 
                1 
                $order_id_filter 
            ORDER BY 
                op.position"
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Обновляем покупки
     * @param $id - order_id
     * @param $purchase
     */
    public function updatePurchase($id, $purchase)
    {
        $purchase = $this->Misc->cleanEntityId($purchase);

        $old_purchase = $this->get_purchase($id);
        if (empty($old_purchase)) {
            return false;
        }

        $order = $this->getOrder(intval($old_purchase->order_id));
        if (empty($order)) {
            return false;
        }

        // Если заказ закрыт, нужно обновить склад при изменении покупки
        if ($order->closed and !empty($purchase->amount) and isset($old_purchase->variant_id) and isset($purchase->variant_id)) {
            if ($old_purchase->variant_id != $purchase->variant_id) {

                if (!empty($old_purchase->variant_id)) {
                    $query = $this->Database->placehold("UPDATE __products_variants SET stock=stock+? WHERE id=? AND stock IS NOT NULL LIMIT 1", $old_purchase->amount, $old_purchase->variant_id);
                    $this->Database->query($query);
                }

                if (!empty($purchase->variant_id)) {
                    $query = $this->Database->placehold("UPDATE __products_variants SET stock=stock-? WHERE id=? AND stock IS NOT NULL LIMIT 1", $purchase->amount, $purchase->variant_id);
                    $this->Database->query($query);
                }

            } elseif (!empty($purchase->variant_id)) {
                $query = $this->Database->placehold("UPDATE __products_variants SET stock=stock+? WHERE id=? AND stock IS NOT NULL LIMIT 1", $old_purchase->amount - $purchase->amount, $purchase->variant_id);
                $this->Database->query($query);
            }
        }

        // Обновляем товары заказа
        $query = $this->Database->placehold("UPDATE __orders_purchases SET ?% WHERE id=? LIMIT 1", $purchase, intval($id));
        $this->Database->query($query);

        return $id;
    }


    /**
     * Добавляем новый вариант товара в заказ
     * @return $purchase_id
     */
    public function addPurchase($purchase)
    {
        $purchase = $this->Misc->cleanEntityId($purchase);

        if (!empty($purchase->variant_id)) {

            // Выбираем данные исходного варианта товара
            $variant = $this->ProductsVariants->getVariant($purchase->variant_id);
            if (empty($variant)) {
                return false;
            }

            // Выбираем данные исходного товара
            $product = $this->Products->get_product(intval($variant->product_id));
            if (empty($product)) {
                return false;
            }
        }

        $order = $this->getOrder(intval($purchase->order_id));
        if (empty($order)) {
            return false;
        }

        if (empty($purchase->product_id) && !empty($variant->product_id)) {
            $purchase->product_id = $variant->product_id;
        }

        if (empty($purchase->product_name) && !empty($product->name)) {
            $purchase->product_name = $product->name;
        }

        if (empty($purchase->sku) && !empty($variant->sku)) {
            $purchase->sku = $variant->sku;
        }

        if (empty($purchase->variant_name) && !empty($variant->name)) {
            $purchase->variant_name = $variant->name;
        }

        if (!isset($purchase->price) && isset($variant->price)) {
            $purchase->price = $variant->price;
        }

        if (!isset($purchase->cost_price) && isset($variant->cost_price)) {
            $purchase->cost_price = $variant->cost_price;
        }

        if (!isset($purchase->amount)) {
            $purchase->amount = 1;
        }

        // Если заказ закрыт, нужно обновить склад при добавлении покупки
        if ($order->closed && !empty($purchase->amount) && !empty($variant->id)) {
            $stock_diff = $purchase->amount;
            $query = $this->Database->placehold("UPDATE __products_variants SET stock=stock-? WHERE id=? AND stock IS NOT NULL LIMIT 1", $stock_diff, $variant->id);
            $this->Database->query($query);
        }

        $query = $this->Database->placehold("INSERT INTO __orders_purchases SET ?%", $purchase);
        $this->Database->query($query);
        $purchase_id = $this->Database->getInsertId();

        return $purchase_id;
    }


    /**
     * Удаление товаров заказа
     * @param int $id
     */
    public function delete_purchase(int $id)
    {
        $purchase = $this->get_purchase($id);
        if (empty($purchase)) {
            return false;
        }

        $order = $this->getOrder(intval($purchase->order_id));
        if (!$order) {
            return false;
        }

        // Если заказ закрыт, нужно обновить склад при изменении покупки
        if ($order->closed && !empty($purchase->amount)) {
            $stock_diff = $purchase->amount;
            $query = $this->Database->placehold("UPDATE __products_variants SET stock=stock+? WHERE id=? AND stock IS NOT NULL LIMIT 1", $stock_diff, $purchase->variant_id);
            $this->Database->query($query);
        }

        $query = $this->Database->placehold("DELETE FROM __orders_purchases WHERE id=? LIMIT 1", intval($id));
        $this->Database->query($query);

        return true;
    }


    /**
     * Фиксируем заказ (принят, выполнен), забираем товары со склада
     * @return $order_id
     */
    public function close($order_id)
    {
        $order = $this->getOrder(intval($order_id));
        if (empty($order)) {
            return false;
        }

        // Если заказ Не был принят, снимаем товары со склада
        if (!$order->closed) {
            $purchases = $this->getPurchases(array('order_id' => $order->id));

            // Вычисляем общее кол-во покупки. Может быть несколько одинаковых вариантов
            $variants_amounts = array();
            foreach ($purchases as $purchase) {
                if (isset($variants_amounts[$purchase->variant_id])) {
                    $variants_amounts[$purchase->variant_id] += $purchase->amount;
                } else {
                    $variants_amounts[$purchase->variant_id] = $purchase->amount;
                }
            }

            // Определяем возможность заказа заданого кол-ва
            // Нельзя отнимать кол-во больше чем есть нна складе.
            foreach ($variants_amounts as $id => $amount) {
                $variant = $this->ProductsVariants->getVariant($id);
                if (empty($variant) || ($variant->stock < $amount)) {
                    return false;
                }
            }

            foreach ($purchases as $purchase) {
                $variant = $this->ProductsVariants->getVariant($purchase->variant_id);
                if (!empty($variant) and !$variant->infinity) {

                    // Кол-во нужно добавлять/вычетать в SQL запросе. Чтобы не произошла коллизия при одновременных запросах
                    $query = $this->Database->placehold("UPDATE __products_variants SET stock=stock-? WHERE id=? LIMIT 1", $purchase->amount, intval($variant->id));
                    if (!$this->Database->query($query)) {
                        return false;
                    }
                }
            }

            return $this->update_order($order->id, array('closed' => 1), true);
        }

        return true;
    }


    /**
     * Переводим заказ в открытый (новый или отменен)
     * @param $order_id
     * @return $order_id
     */
    public function open(int $order_id)
    {
        $order = $this->getOrder($order_id);
        if (empty($order)) {
            return false;
        }

        // Если заказ был принят, возвращаем товары на склад
        if ($order->closed) {
            $purchases = $this->getPurchases(array('order_id' => $order->id));
            foreach ($purchases as $purchase) {
                $variant = $this->ProductsVariants->getVariant($purchase->variant_id);
                if (!empty($variant) && !$variant->infinity) {

                    // Кол-во нужно добавлять/вычетать в SQL запросе. Чтобы не произошла коллизия при одновременных запросах
                    $query = $this->Database->placehold("UPDATE __products_variants SET stock=stock+? WHERE id=? LIMIT 1", $purchase->amount, intval($variant->id));
                    if (!$this->Database->query($query)) {
                        return false;
                    }
                }
            }

            return $this->update_order($order->id, array('closed' => 0), true);
        }

        return true;
    }


    /**
     * Set order payed
     * @param int $order_id
     * @return int
     */
    public function pay(int $order_id)
    {
        $order = $this->getOrder($order_id);
        if (empty($order)) {
            return false;
        }

        if (!$this->close($order->id)) {
            return false;
        }

        $query = $this->Database->placehold("UPDATE __orders SET payment_status=1, modified=NOW() WHERE id=? LIMIT 1", $order->id);
        $this->Database->query($query);
        return $order->id;
    }


    /**
     * Вычисляем и обновляем общую Стоимость и Прибыль
     * @param int $ordre_id
     * @param bool $modified - update edit date
     * @return $order_id
     */
    public function update_total_price(int $order_id, $modified = true)
    {

        // Get order informatiion
        if (empty($order = $this->getOrder(intval($order_id)))) {
            return false;
        }

        // Вычисляем комиссию способа доставки
        if (!empty($order->delivery_id)) {
            $delivery_settings = $this->OrdersDelivery->get_delivery_settings($order->delivery_id);
            // Дописать логику
        }

        // Выбираем все товары заказа
        $order_purchases = $this->getPurchases(array('order_id' => $order->id));

        // Выбираем общую стоимость товаров заказа (чистая сумма)
        $order_clean_price = 0;
        foreach ($order_purchases as $purchase) {
            $order_clean_price += $purchase->price * $purchase->amount;
        }

        // Выбираем общую себестоимость товаров заказов
        $order_cost_price = 0;
        foreach ($order_purchases as $purchase) {
            $order_cost_price += $purchase->cost_price * $purchase->amount;
        }


        // Вычисляем стоимость заказа со скидкой и купоном
        $order_discount_price = $order_clean_price * (100 - $order->discount) / 100 - $order->coupon_discount;
        $set_total_price = $this->Database->placehold("o.total_price = ? ", $order_discount_price);

        // Выбираем настройки способа оплаты
        if (!empty($order->payment_method_id)) {
            $payment_settings = $this->OrdersPayment->getPaymentMethodSettings($order->payment_method_id);
        }

        // Вычисляем внутренюю комиссию способа оплаты
        $order_payment_fee_inside_price = 0;
        if (!empty($payment_settings->fee_inside)) {
            $order_payment_fee_inside_price = $order_discount_price * $payment_settings->fee_inside / 100;
        }

        // Добавляем платеж за операцию
        if (!empty($payment_settings->fee_fix_inside)) {
            $order_payment_fee_inside_price += $payment_settings->fee_fix_inside;
        }


        // Вычисляем внутренюю сумму налога которую оплачивает продавец
        $order_payment_tax_inside_price = 0;
        if (!empty($payment_settings->tax_inside)) {
            $order_payment_tax_inside_price = $order_discount_price * $payment_settings->tax_inside / 100;
        }



        // Вычисляем общую сумму заказа к оплате
        $order_payment_price = $order_discount_price;

        // Добаляем стоимость доставки
        if (!empty($order->delivery_price) and !$order->separate_delivery) {
            $order_payment_price += $order->delivery_price;
        }

        // Если есть налоги способа оплаты, добавляем к цене
        // Формула: (100-tax%)*PriceWithTax = Price => PriceWithTax = Price/((100-tax%)/100)
        if (!empty($payment_settings->tax)) {
            $order_payment_price = $order_payment_price / ((100 - $payment_settings->tax) / 100);
        }

        // Добавляем комиссию сервиса
        if (!empty($payment_settings->fee)) {
            $order_payment_price = $order_payment_price / ((100 - $payment_settings->fee) / 100);
        }

        // BUG: Если основная валюта без копеек, округлим сумму

        $set_payment_price = $this->Database->placehold(", o.payment_price = ? ", $order_payment_price);


        // Вычисляем комиссию менеджера
        // Комиссия вычисляется от чистой стоимости заказа (с учетом скидки)
        // Комиссия менеджера уменьшается пропорционально сделаной им скидки
        // BUG Комиссия менеджера уменьшается на заказы от рекламы
        $manager_interest = 0;
        $set_interest  = "";
        if (!empty($order->manager_id)) { # если присвоен менеджер
            $manager = $this->Users->getUser($order->manager_id);
            if (!empty($manager->discount)) {
                $manager_discount = $manager->discount;

                // Вычисляем ROI заказа
                // Корректируем Комиссию менеджера
                // BUG: Костыль, требует доработки.
                if ($order_cost_price > 0) {
                    $roi =  ($order_discount_price - $order_cost_price) / $order_cost_price * 100;
                    if ($roi < 70) {

                        // Пропорциональное уменьшение % менеджера
                        $manager_discount = $manager->discount * $roi / 70;
                    }
                }

                $manager_interest = ($order_discount_price * $manager_discount / 100);
                if ($order_clean_price > 0) {

                    // Реальный % скидки на заказ c учетом купона и дисконта(%)
                    $real_order_discaunt = (1 - $order_discount_price / $order_clean_price) * 100;

                    // Вычет из комиссия менеджера = скидка на заказ % * 2
                    $manager_interest_discount =  $manager_interest * $real_order_discaunt * 2 / 100;
                    $manager_interest = $manager_interest - $manager_interest_discount;
                }

                // Округляем до 0.00
                $manager_interest = round($manager_interest, 2);
                $set_interest = $this->Database->placehold(", o.interest_price = ? ", $manager_interest);
            }
        }


        // Вычисляем конечную прибыль от заказа
        // Берем общую сумму заказа (после скидки и купона) и отнимает расходы
        // В расходы включены комиссия менеджера, комиссия платежной системы, комиссия способа доставки(?)
        $order_profit_price = ($order_discount_price - $order_cost_price) - $manager_interest - $order_payment_fee_inside_price - $order_payment_tax_inside_price;
        $set_profit_price = $this->Database->placehold(", o.profit_price = ? ", $order_profit_price);

        $set_modified = "";
        if ($modified) {
            $set_modified = $this->Database->placehold(", modified=now() ");
        }

        $query = $this->Database->placehold(
            "UPDATE 
				__orders o 
            SET 
                $set_total_price 
                $set_profit_price 
                $set_interest 
                $set_modified 
                $set_payment_price 
			WHERE 
				o.id = ? 
			LIMIT 
				1",
            intval($order->id)
        );

        $this->Database->query($query);
        return $order->id;
    }


    /**
     * Выбираем следующий заказ
     * @param $id
     * @param $status
     * @return $order
     */
    public function get_next_order($id, $status = null)
    {
        $where_status = '';
        if ($status !== null) {
            $where_status = $this->Database->placehold('AND status=?', $status);
        }
        $this->Database->query("SELECT MIN(id) as id FROM __orders WHERE id>? $where_status LIMIT 1", $id);
        $next_id = $this->Database->result('id');
        if ($next_id) {
            return $this->getOrder(intval($next_id));
        } else {
            return false;
        }
    }


    /**
     * Выбираем Предыдущий заказ
     * @param $id
     * @param $status
     * @return $order
     */
    public function get_prev_order($id, $status = null)
    {
        $where_status = '';
        if ($status !== null) {
            $where_status = $this->Database->placehold('AND status=?', $status);
        }
        $this->Database->query("SELECT MAX(id) as id FROM __orders WHERE id<? $where_status LIMIT 1", $id);
        $prev_id = $this->Database->result('id');
        if ($prev_id) {
            return $this->getOrder(intval($prev_id));
        } else {
            return false;
        }
    }
}
