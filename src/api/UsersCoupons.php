<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 1.5
 *
 */

namespace GoodGin;

class UsersCoupons extends GoodGin
{
    /**
     * Функция возвращает купон по его id или url
     * (в зависимости от типа аргумента, int - id, string - code)
     * @param $id - id или code купона
     */
    public function getCoupon($id)
    {
        if (empty($id)) {
            return false;
        }

        if (gettype($id) == 'string') {
            $where = $this->Database->placehold(' AND c.code=? ', $id);
        } else {
            $where = $this->Database->placehold(' AND c.id=? ', intval($id));
        }

        $query = $this->Database->placehold(
            "SELECT
                c.id, 
                c.code, 
                c.value, 
                c.type, 
                c.expire, 
                c.min_order_price, 
                c.single, 
                c.usages,
				((DATE(NOW()) <= DATE(c.expire) OR c.expire IS NULL) AND (c.usages=0 OR NOT c.single)) AS valid
		    FROM 
                __users_coupons c
            WHERE
                1 
                $where
            LIMIT 
                1"
        );

        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Функция возвращает массив купонов, удовлетворяющих фильтру
     * @param array $filter
     */
    public function getCoupons(array $filter = array())
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

        $coupon_id_filter = '';
        if (!empty($filter['id'])) {
            $coupon_id_filter = $this->Database->placehold('AND c.id in(?@)', (array)$filter['id']);
        }

        $valid_filter = '';
        if (isset($filter['valid'])) {
            if ($filter['valid']) {
                $valid_filter = $this->Database->placehold('AND ((DATE(NOW()) <= DATE(c.expire) OR c.expire IS NULL) AND (c.usages=0 OR NOT c.single))');
            } else {
                $valid_filter = $this->Database->placehold('AND NOT ((DATE(NOW()) <= DATE(c.expire) OR c.expire IS NULL) AND (c.usages=0 OR NOT c.single))');
            }
        }

        $keyword_filter = '';
        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= $this->Database->placehold('AND (b.name LIKE "%' . $this->Database->escape(trim($keyword)) . '%") ');
            }
        }

        $query = $this->Database->placehold(
            "SELECT
                c.id, 
                c.code, 
                c.value, 
                c.type, 
                c.expire, 
                c.min_order_price, 
                c.single, 
                c.usages,
				((DATE(NOW()) <= DATE(c.expire) OR c.expire IS NULL) AND (c.usages=0 OR NOT c.single)) AS valid
		    FROM 
                __users_coupons c 
            WHERE 
                1 
                $coupon_id_filter 
                $valid_filter 
                $keyword_filter
		    ORDER BY 
                valid DESC, 
                id DESC 
            $sql_limit",
            $this->Settings->date_format
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Функция вычисляет количество, удовлетворяющих фильтру
     * @param array $filter
     */
    public function countCoupons(array $filter = array())
    {
        $coupon_id_filter = '';
        if (!empty($filter['id'])) {
            $coupon_id_filter = $this->Database->placehold('AND c.id in(?@)', (array)$filter['id']);
        }

        $valid_filter = '';
        if (isset($filter['valid'])) {
            $valid_filter = $this->Database->placehold('AND ((DATE(NOW()) <= DATE(c.expire) OR c.expire IS NULL) AND (c.usages=0 OR NOT c.single))');
        }

        $keyword_filter = '';
        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= $this->Database->placehold('AND (c.name LIKE "%' . $this->Database->escape(trim($keyword)) . '%" ) ');
            }
        }

        $query = $this->Database->placehold(
            "SELECT 
                COUNT(distinct c.id) as count
		    FROM 
                __users_coupons c 
            WHERE 
                1 
                $coupon_id_filter 
                $valid_filter 
                $keyword_filter"
        );

        $this->Database->query($query);
        return $this->Database->result('count');
    }


    /**
     * Создание купона
     * @param $coupon
     */
    public function addCoupon($coupon)
    {
        $coupon = $this->Misc->cleanEntityId($coupon);

        if (empty($coupon->single)) {
            $coupon->single = 0;
        }

        $query = $this->Database->placehold("INSERT INTO __users_coupons SET ?%", $coupon);

        $this->Database->query($query);
        return $this->Database->getInsertId();
    }


    /**
     * Обновить купон(ы)
     * @param $id
     * @param $coupon
     */
    public function updateCoupon(int|array $id, $coupon)
    {
        $query = $this->Database->placehold("UPDATE __users_coupons SET ?% WHERE id in(?@) LIMIT ?", $coupon, (array)$id, count((array)$id));
        return $this->Database->query($query);
    }


    /**
     * Удалить купон
     * @param int $id
     */
    public function deleteCoupon(int $id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("DELETE FROM __users_coupons WHERE id=? LIMIT 1", intval($id));
        return $this->Database->query($query);
    }
}
