<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 * @version 2.1
 *
 * Выбираем статистику
 *
 */

namespace GoodGin;

class Statistics extends GoodGin
{
    /**
     * Выводим статистику Заказов по Дням/Месяцам
     * @param $price_type - totalPrice/profitPrice/amount
     * @param  $date_type
     * @param $from_date
     * @param $to_date
     * @param $filters
     */
    public function ordersSum($price_type, $date_type = "byMonth", $from_date = null, $to_date = null, $filters = array())
    {

        $from_date_filter = '';
        $select_byDday = '';
        $group_byDay = '';
        $filter_orders = '';

        if (empty($price_type)) {
            return false;
        }

        // Выбираем выручку
        if ($price_type == "totalPrice") {
            $sum = " SUM(o.total_price) AS total_price";
        }

        // Выбираем прибыль
        elseif ($price_type == "profitPrice") {
            $sum = " SUM(o.profit_price) AS total_price";
        }

        // Выбираем кол-во заказов
        elseif ($price_type == "amount") {
            $sum = " COUNT(o.id) AS total_price";
        } else {
            return false;
        }

        // Определяем временной диапазон 2020-12-10 13:42:43
        if (!empty($from_date)) {
            $from_date = date('Y-m-d', strtotime($from_date));
            $from_date_filter = $this->Database->placehold(' AND o.date > ?', $from_date);
        }

        if ($date_type == "byDay") {
            $select_byDday =  $this->Database->placehold(' DAY(o.date) AS day, ');
            $group_byDay = $this->Database->placehold(' day, ');
        }

        foreach ($filters as $key => $filter) {
            $filter_orders = $this->Database->placehold(' AND o.' . $key . ' = ?', $filter);
        }


        $query = $this->Database->placehold(
            "SELECT 
				$sum, 
				$select_byDday
				MONTH(o.date) as month, 
				YEAR(o.date) as year 
			FROM 
				__orders o 
			WHERE 
				o.closed 
				$from_date_filter
				$filter_orders
			GROUP BY
				$group_byDay
				month,
				year
			ORDER BY
				o.date"
        );

        $this->Database->query($query);
        $data = $this->Database->results();
        $results = array();

        foreach ($data as $d) {

            if ($date_type == "byDay") {
                $result['day'] = $d->day;
            }

            $result['month'] = $d->month;
            $result['year'] = $d->year;
            $result['y'] = $d->total_price;
            $results[] = $result;
        }

        return $results;
    }


    /**
     * Выводим статистку перемещений на складе продукта по мемяцам
     * @param $product_id
     * @param $type
     */
    public function productWarehouseMovemetByMonth($product_id, $type)
    {

        $where_type = '';

        $sum = " SUM(whp.amount) AS amount";
        $where_product = $this->Database->placehold(" AND whp.product_id = ?", $product_id);

        if ($type == "add") {
            $where_type = $this->Database->placehold(" AND whm.status = ?", 2);
        } elseif ($type == "delete") {
            $where_type = $this->Database->placehold(" AND whm.status = ?", 3);
        }

        $query = $this->Database->placehold(
            "SELECT 
				$sum,
				MONTH(whm.awaiting_date) as month, 
				YEAR(whm.awaiting_date) as year 
			FROM 
				__wh_purchases as whp
				LEFT JOIN __wh_movements whm ON whp.movement_id = whm.id
			WHERE 
                whm.closed 
				$where_product
				$where_type 
			GROUP BY 
				month, year
			ORDER BY
				whm.awaiting_date"
        );


        $this->Database->query($query);
        $data = $this->Database->results();
        $results = array();

        foreach ($data as $d) {
            $result['month'] = $d->month;
            $result['year'] = $d->year;
            $result['y'] = $d->amount;
            $results[] = $result;
        }

        return $results;
    }


    /**
     * Выводим статистику продаж категории продуктов по мемяцам
     * @param $category_id
     * @param $type
     */
    public function productsCategoryByMonth($category_id, $type)
    {
        if (empty($category_id)) {
            return false;
        }

        // Выбрать все товары категории
        $category = $this->ProductsCategories->get_category(intval($category_id));
        $filter['category_id'] = $category->children; // id  всех дочерних категорий
        $filter['limit'] = 'all';

        $products = $this->Products->get_products($filter);

        return $this->productByMonth(array_keys($products), $type);
    }


    /**
     * Выводим статистку продаж продукта по мемяцам
     * @param $product_id - ID or Array(id, id, ...) or srting(id, id, ...) of prodict
     * @param $type
     */
    public function productByMonth($product_id, $type)
    {

        //  если переданы id нескольких продуктов
        if (!is_int($product_id)) {
            if (!is_array($product_id)) {
                $product_id = explode(",", $product_id);
            }
            $where_product = $this->Database->placehold(" AND op.product_id in(?@)", $product_id);
        } else {
            $where_product = $this->Database->placehold(" AND op.product_id = ?", $product_id);
        }

        // Вычитаем скидку в % . Так как это единый процент для всех товаров заказа
        if (!empty($type) and $type == "totalPrice") {
            $sum = " SUM((op.price - op.price * o.discount / 100) * op.amount) AS total_price";
        } elseif (!empty($type) and $type == "profitPrice") {
            $sum = " SUM((op.price - op.price * o.discount / 100 - op.cost_price) * op.amount) AS total_price";
        } elseif (!empty($type) and $type == "amount") {
            $sum = " SUM(op.amount) AS total_price";
        } else {
            return false;
        }

        $query = $this->Database->placehold(
            "SELECT 
				$sum,
				MONTH(o.date) as month, 
				YEAR(o.date) as year 
			FROM 
				__orders_purchases op 
				LEFT JOIN __orders o ON op.order_id = o.id
			WHERE 
                o.closed
				$where_product 
			GROUP BY 
				month, year
			ORDER BY
				o.date"
        );

        $this->Database->query($query);
        $data = $this->Database->results();
        $results = array();

        foreach ($data as $d) {
            $result['month'] = $d->month;
            $result['year'] = $d->year;
            $result['y'] = $d->total_price;
            $results[] = $result;
        }

        return $results;
    }


    /**
     * Выводим статистку продаж продукта за период
     * @param $product_id
     * @param $from_date
     * @param $to_date
     */
    public function productByDate($product_id, $from_date = null, $to_date = null)
    {

        $where_date = "";

        $sum_totalPrice = " SUM((op.price * op.amount) - op.price * op.amount * o.discount / 100) AS totalPrice";
        $sum_profitPrice = " SUM((op.price * op.amount - op.cost_price * op.amount) - op.price * op.amount * o.discount / 100) AS profitPrice";
        $sum_amount = " SUM(op.amount) AS amount";

        // Определяем временной диапазон 2020-12-10 13:42:43
        if (!empty($from_date)) {
            $from_date = date('Y-m-d', strtotime($from_date));
            $where_date = $this->Database->placehold(' AND o.date > ?', $from_date);
        }

        $query = $this->Database->placehold(
            "SELECT 
				$sum_totalPrice, 
				$sum_profitPrice, 
				$sum_amount, 
				MONTH(o.date) as month, 
				YEAR(o.date) as year 
			FROM 
				__orders_purchases op 
				LEFT JOIN __orders o ON op.order_id = o.id
			WHERE 
				op.product_id = $product_id 
                AND o.closed 
				$where_date"
        );

        $this->Database->query($query);
        $results = $this->Database->results();

        return $results;
    }


    /**
     * Выводим статистку обработаных заказов Менеджера по месяцам
     */
    public function managerOrdersByMonth($manager_id, $type)
    {

        if (!empty($type) and $type == "totalPrice") {
            $sum = " SUM(o.interest_price) AS total_price";
        } elseif (!empty($type) and $type == "amount") {
            $sum = " COUNT(*) AS total_price";
        } else {
            return false;
        }

        $query = $this->Database->placehold(
            "SELECT 
				$sum,
				MONTH(o.date) as month, 
				YEAR(o.date) as year 
			FROM 
				__orders o 
			WHERE 
				o.manager_id = $manager_id AND 
				o.closed 
			GROUP BY 
				month, year
			ORDER BY
				o.date"
        );

        $this->Database->query($query);
        $data = $this->Database->results();
        $results = array();

        foreach ($data as $d) {
            $result['month'] = $d->month;
            $result['year'] = $d->year;
            $result['y'] = $d->total_price;
            $results[] = $result;
        }

        return $results;
    }


    /**
     * Выводим общий график финансовых платежей
     * @param $filter
     */
    public function financeByMonth($filter = array())
    {

        // Определяем тип платежа
        $where_type = "";
        if (isset($filter['type'])) {
            if ($filter['type'] == "plus" || $filter['type'] == 1) {
                $filter['type'] = 1;
            } elseif ($filter['type'] == "minus" || $filter['type'] == 0) {
                $filter['type'] = 0;
            }

            $where_type = $this->Database->placehold(' AND fp.type=?', $filter['type']);
        }

        $where_payments_ids = "";
        if (isset($filter['payments_ids'])) {
            $where_payments_ids = $this->Database->placehold(' AND fp.id in(?@)', (array)$filter['payments_ids']);
        }

        // Исключаем Все переводы $related_payment_id = null
        $where_related_payment_id = "";
        if (isset($filter['related_payment_id']) and $filter['related_payment_id'] == "NULL") {
            $where_related_payment_id = $this->Database->placehold(' AND fp.related_payment_id is NULL');

            // Если отличное от NULL - это перевод с кошелка на кошелек
        } elseif (!empty($filter['related_payment_id'])) {
            $where_related_payment_id = $this->Database->placehold(' AND fp.related_payment_id=?', $filter['related_payment_id']);
        }

        $where_purse_id = "";
        if (isset($filter['purse_id'])) {
            $where_purse_id =  $this->Database->placehold(' AND fp.purse_id in(?@)', (array)$filter['purse_id']);
        }

        $where_category_id = "";
        $category_join = "";
        if (isset($filter['category_id']) && !empty($filter['category_id'])) {
            $where_category_id =  $this->Database->placehold(' AND fc.id = ? ', (int)$filter['category_id']);
            $category_join  = $this->Database->placehold(' LEFT JOIN __finance_categories fc ON fp.finance_category_id = fc.id');
        }

        $query = $this->Database->placehold(
            "SELECT 
				fp.amount,
				fp.currency_amount,
                fp.currency_rate,
				MONTH(fp.date) as month,
				YEAR(fp.date) as year,
				purse.currency_id as currency_id,
				cur.position as pos
			FROM 
				__finance_payments fp
				LEFT JOIN __finance_purses as purse ON purse.id = fp.purse_id
				LEFT JOIN __finance_currencies as cur ON cur.id = currency_id
				$category_join 
			WHERE
				1
				$where_type 
				$where_purse_id
				$where_related_payment_id
				$where_category_id
				$where_payments_ids
			ORDER BY
				fp.date"
        );
        $this->Database->query($query);
        $data = $this->Database->results();

        $finances = array();
        foreach ($data as $item) {
            $item = (array) $item;

            // Пропускаем первую валюты - это базовая
            if ($item['pos'] != 1) {
                if (!empty($item['currency_amount']) and intval($item['currency_rate']) !== 1) {
                    $item['amount'] = $item['currency_amount'];
                } else {
                    $item['amount'] = $this->Money->priceConvert((int)$item['amount'], (int)$this->Money->getMainCurrency()->id, false, (int)$item['currency_id']);
                }
            }
            $finances[$item['year']][$item['month']][] = $item;
        }

        $results = array();
        foreach ($finances as $key_y => $year) {
            foreach ($year as $key_m => $month) {
                $info = array();
                $info['month'] = $key_m;
                $info['year'] = $key_y;
                $info['y'] = array_sum(array_column($month, 'amount'));
                $results[] = $info;
            }
        }

        return $results;
    }
}
