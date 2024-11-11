<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 1.4
 *
 * Работаем со складом, закупками, поставками, списанием
 *
 */

namespace GoodGin;

class Warehouse extends GoodGin
{
    /**
     * Выбрать определенную поставку
     * @param int $id
     */
    public function get_movement(int $id)
    {
        $where = $this->Database->placehold(' AND m.id=? ', intval($id));
        $query = $this->Database->placehold(
            "SELECT  
				m.*
			FROM 
				__wh_movements m 
			WHERE
                1
                $where
			LIMIT 1"
        );

        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Выбрать список поставок
     * @param array $filter
     * @param $count
     */
    public function get_movements(array $filter = array(), $count = false)
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

        $where_status = '';
        if (isset($filter['status'])) {
            $where_status = $this->Database->placehold(' AND whm.status=?', intval($filter['status']));
        } else {
            $where_status = $this->Database->placehold(' AND whm.status NOT IN (3, 4)');
        }

        $where_id = '';
        if (isset($filter['id'])) {
            $where_id = $this->Database->placehold(' AND whm.id in(?@)', (array)$filter['id']);
        }

        $where_modified = '';
        if (isset($filter['modified_since'])) {
            $where_modified = $this->Database->placehold(' AND whm.modified>?', $filter['modified_since']);
        }

        $where_keyword = '';
        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $where_keyword .= $this->Database->placehold(' AND (whm.note LIKE "%' . $this->Database->escape(trim($keyword)) . '%" OR whm.id = "' . $this->Database->escape(trim($keyword)) . '") ');
            }
        }

        // Выбираем заказы
        if ($count === false) {
            $query = $this->Database->placehold(
                "SELECT 
				    whm.*
                FROM 
                    __wh_movements AS whm 
                WHERE 
                    1
                    $where_id 
                    $where_status 
                    $where_keyword 
                    $where_modified 
                ORDER BY
                    whm.id DESC 
                $sql_limit"
            );

            $this->Database->query($query);

            $movements = array();
            foreach ($this->Database->results() as $movement) {
                $movements[$movement->id] = $movement;
            }
            return $movements;


            // Выбираем кол-во
        } else {

            // Выбираем заказы
            $query = $this->Database->placehold(
                "SELECT 
                    COUNT(DISTINCT whm.id) as count
                FROM 
                    __wh_movements AS whm 
                WHERE
                    1
                    $where_id 
                    $where_status 
                    $where_keyword 
                    $where_modified"
            );

            $this->Database->query($query);
            return $this->Database->result('count');
        }
    }


    /**
     * Выбираем кол-во поставок
     * @param array $filter
     */
    public function count_movements(array $filter = array())
    {
        return $this->get_movements($filter, true);
    }


    /**
     * Выбираем ожидаемые поставки определенного товара
     * $status = 1
     * @param int|array $variand_id
     */
    public function get_product_movements(int|array $variand_id)
    {

        $where_variant_id = "";
        if (!empty($variand_id)) {
            $where_variant_id = $this->Database->placehold(' AND whp.variant_id in(?@)', (array)$variand_id);
        }

        $query = $this->Database->placehold(
            "SELECT 
                whp.id,
                whp.movement_id,
                whp.product_id,
                whp.variant_id, 
                whp.product_name,
                whp.variant_name,
                whp.amount,
                whp.sku,
                whm.awaiting_date,
                whm.status
            FROM 
                __wh_purchases as whp
                LEFT JOIN __wh_movements whm ON whp.movement_id = whm.id
            WHERE 
                1 
                $where_variant_id 
                AND whm.status = 1
            ORDER BY 
                whm.awaiting_date ASC"
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Обновление поставки
     * @param int $id
     */
    public function update_movement(int $id, $movement)
    {
        $movement = (object)$movement;

        $query = $this->Database->placehold(
            "UPDATE 
				__wh_movements 
			SET 
				?%,
				modified=now() 
			WHERE 
				id=? 
			LIMIT 
				1",
            $movement,
            intval($id)
        );

        return $this->Database->query($query);
    }


    /**
     * Удаляем поставку
     * @param int $id
     */
    public function delete_movement(int $id)
    {
        if (empty($id)) {
            return false;
        }

        // Удаляем товары поставки
        $query = $this->Database->placehold("DELETE FROM __wh_purchases WHERE movement_id=?", intval($id));
        if ($this->Database->query($query)) {

            // Удаляем поставку
            $query = $this->Database->placehold("DELETE FROM __wh_movements WHERE id=? LIMIT 1", intval($id));
            return $this->Database->query($query);

            // BUG Удалить связанные оплату
        } else {
            return false;
        }
    }


    /**
     * Добавляем поставку
     * @param $movement
     */
    public function add_movement($movement)
    {
        $movement = $this->Misc->cleanEntityId($movement);

        $set_curr_date = "";
        if (empty($movement->date)) {
            $set_curr_date = ', date=now()';
        }

        $query = $this->Database->placehold("INSERT INTO __wh_movements SET ?% $set_curr_date", $movement);

        if ($this->Database->query($query)) {
            return $this->Database->getInsertId();
        } else {
            return false;
        }
    }


    /**
     * Выбираем товар в поставке
     * @param int $id
     */
    public function get_purchase(int $id)
    {
        $query = $this->Database->placehold("SELECT * FROM __wh_purchases WHERE id=? LIMIT 1", intval($id));
        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Выбираем товары в поставке
     * @param array $filter
     * @param $join = array('image')
     */
    public function getPurchases(array $filter = array(), $join = array())
    {
        $where_movement_id = "";
        if (isset($filter['movement_id'])) {
            if (!empty($filter['movement_id'])) {
                $where_movement_id = $this->Database->placehold('AND movement_id in(?@)', (array)$filter['movement_id']);
            } else {
                return array();
            }
        }

        // JOIN IMAGE
        $select_image = '';
        $join_image = '';
        if (in_array("image", $join)) {
            $select_image = $this->Database->placehold(", i.filename as image_filename");
            $join_image =  $this->Database->placehold("LEFT JOIN __content_images i ON i.entity_id=wmp.product_id AND i.entity_name='product' AND i.position=(SELECT MIN(position) FROM __content_images WHERE entity_id=wmp.product_id and entity_name='product')");
        }

        $query = $this->Database->placehold(
            "SELECT 
                wmp.id,
                wmp.movement_id,
                wmp.product_id,
                wmp.variant_id, 
                wmp.sku, 
                wmp.product_name,
                wmp.variant_name,
                wmp.price, 
                wmp.cost_price, 
                wmp.amount,
                wmp.position 
                $select_image
            FROM 
                __wh_purchases wmp
                $join_image
            WHERE 
                1 
                $where_movement_id 
            ORDER BY 
                wmp.position"
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Обновляем товары в поставке
     * @param int $id
     */
    public function updatePurchase(int $id, $purchase)
    {
        $purchase = (object)$purchase;
        $old_purchase = $this->get_purchase($id);

        if (empty($old_purchase)) {
            return false;
        }

        $movement = $this->get_movement($old_purchase->movement_id);

        if (!$movement) {
            return false;
        }

        // Если поставка закрыта, нужно обновить склад при изменении закупки
        if ($movement->closed && !empty($purchase->amount)) {

            $s = ($movement->status == 3 || $movement->status == 4) ? -1 : 1;

            // если сменили вариант товара
            if (!empty($old_purchase->variant_id) and !empty($purchase->variant_id) and $old_purchase->variant_id != $purchase->variant_id) {

                // забираем со старого варианта
                if (!empty($old_purchase->variant_id)) {
                    $query = $this->Database->placehold("UPDATE __products_variants SET stock=stock-(?) WHERE id=? AND stock IS NOT NULL LIMIT 1", $s * $old_purchase->amount, $old_purchase->variant_id);
                    $this->Database->query($query);
                }

                // добавляем в новый вариант
                if (!empty($purchase->variant_id)) {
                    $query = $this->Database->placehold("UPDATE __products_variants SET stock=stock+(?) WHERE id=? AND stock IS NOT NULL LIMIT 1", $s * $purchase->amount, $purchase->variant_id);
                    $this->Database->query($query);
                }

                // обновляем склад с новым значением поставки
            } elseif (!empty($purchase->variant_id)) {
                $query = $this->Database->placehold("UPDATE __products_variants SET stock=stock-(?) WHERE id=? AND stock IS NOT NULL LIMIT 1", $s * ($old_purchase->amount - $purchase->amount), $purchase->variant_id);
                $this->Database->query($query);
            }
        }

        // Обновляем товары поставки
        $query = $this->Database->placehold("UPDATE __wh_purchases SET ?% WHERE id=? LIMIT 1", $purchase, intval($id));
        $this->Database->query($query);

        return $id;
    }


    /**
     * Добавляем товар в поставку
     * @param $purchase
     */
    public function addPurchase($purchase)
    {
        $purchase = $this->Misc->cleanEntityId($purchase);

        if (!empty($purchase->variant_id)) {
            $variant = $this->ProductsVariants->getVariant($purchase->variant_id);
            if (empty($variant)) {
                return false;
            }

            $product = $this->Products->get_product(intval($variant->product_id));
            if (empty($product)) {
                return false;
            }
        }

        $movement = $this->get_movement(intval($purchase->movement_id));
        if (empty($movement)) {
            return false;
        }

        if (!isset($purchase->product_id) && isset($variant)) {
            $purchase->product_id = $variant->product_id;
        }

        if (empty($purchase->product_name)  && !empty($product->name)) {
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
        if ($movement->closed && !empty($purchase->amount) && !empty($variant->id)) {

            $s = ($movement->status == 3 || $movement->status == 4) ? -1 : 1;

            $stock_diff = $purchase->amount;
            $query = $this->Database->placehold("UPDATE __products_variants SET stock=stock+? WHERE id=? AND stock IS NOT NULL LIMIT 1", $s * $stock_diff, $variant->id);
            $this->Database->query($query);
        }

        $query = $this->Database->placehold("INSERT INTO __wh_purchases SET ?%", $purchase);
        $this->Database->query($query);
        return $this->Database->getInsertId();
    }


    /**
     * Удаляем товар из поставки
     * @param int $id
     */
    public function delete_purchase(int $id)
    {
        $purchase = $this->get_purchase($id);
        if (!$purchase) {
            return false;
        }

        $movement = $this->get_movement(intval($purchase->movement_id));
        if (!$movement) {
            return false;
        }

        // Если заказ закрыт, нужно обновить склад при изменении покупки
        if ($movement->closed && !empty($purchase->amount)) {
            $stock_diff = $purchase->amount;

            // Если списание, прибавляем на складе
            // Если поставка, отнимаем со склада
            $s = ($movement->status == 3 || $movement->status == 4) ? -1 : 1;

            $query = $this->Database->placehold("UPDATE __products_variants SET stock=stock-? WHERE id=? AND stock IS NOT NULL LIMIT 1", $s * $stock_diff, $purchase->variant_id);
            $this->Database->query($query);
        }

        $query = $this->Database->placehold("DELETE FROM __wh_purchases WHERE id=? LIMIT 1", intval($id));
        $this->Database->query($query);

        return true;
    }


    /**
     * Фиксируем поставку/списание (выполнен)
     * $subtract (вычесть)  = true - при списании
     * @param int $movement_id
     * @param $subtract
     */
    public function close(int $movement_id, $subtract = false)
    {
        $movement = $this->get_movement($movement_id);
        if (empty($movement)) {
            return false;
        }

        // Если списание/поставка товаров
        $s = ($subtract) ? -1 : 1;

        // Если поставка еще не была закрыта, добавляем товары
        if (!$movement->closed) {
            $purchases = $this->getPurchases(array('movement_id' => $movement->id));

            foreach ($purchases as $purchase) {
                $variant = $this->ProductsVariants->getVariant($purchase->variant_id);
                if ($variant and !$variant->infinity) {
                    $new_stock = $variant->stock + $s * $purchase->amount;
                    $this->ProductsVariants->update_variant($variant->id, array('stock' => $new_stock));
                }
            }
            $query = $this->Database->placehold("UPDATE __wh_movements SET closed=1, modified=NOW() WHERE id=? LIMIT 1", intval($movement->id));
            $this->Database->query($query);
        }

        return $movement->id;
    }


    /**
     * Переводим поставку в открытый (новый, ожидаем)
     * @param int $movement_id
     */
    public function open(int $movement_id)
    {
        $movement = $this->get_movement($movement_id);
        if (empty($movement)) {
            return false;
        }

        // Если заказ был списан(3), меняем знак
        $s = ($movement->status == 3 || $movement->status == 4) ? 1 : -1;

        // Если заказ был как "выполнен/closed", отнимаем||добавляем товар на склада
        if ($movement->closed) {
            $purchases = $this->getPurchases(array('movement_id' => $movement->id));
            foreach ($purchases as $purchase) {
                $variant = $this->ProductsVariants->getVariant($purchase->variant_id);
                if (!empty($variant) and !$variant->infinity) {
                    $new_stock = $variant->stock + $s * $purchase->amount;
                    $this->ProductsVariants->update_variant($variant->id, array('stock' => $new_stock));
                }
            }
            $query = $this->Database->placehold("UPDATE __wh_movements SET closed=0, modified=NOW() WHERE id=? LIMIT 1", intval($movement->id));
            $this->Database->query($query);
        }

        return $movement->id;
    }
}
