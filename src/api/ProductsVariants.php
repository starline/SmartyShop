<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 2.4
 *
 * Работа с вариантами товаров
 *
 */

namespace GoodGin;

class ProductsVariants extends GoodGin
{
    /**
     * Функция возвращает варианты товара
     * @param array $filter
     * @param array $join
     * @return array
     */
    public function getVariants(array $filter = array(), array $join = array("provider", "merchant"))
    {

        $where_product_id = '';
        if (isset($filter['product_id'])) {
            if (!empty($filter['product_id'])) {
                $where_product_id = $this->Database->placehold(' AND v.product_id in(?@)', (array)$filter['product_id']);
            } else {
                return array();
            }
        }

        $where_variant_id = '';
        if (isset($filter['id'])) {
            if (!empty($filter['id'])) {
                $where_variant_id = $this->Database->placehold(' AND v.id in(?@)', (array)$filter['id']);
            } else {
                return array();
            }
        }

        $where_in_stock = '';
        if (!empty($filter['in_stock']) && $filter['in_stock']) {
            $where_in_stock = $this->Database->placehold(' AND (v.stock>0 OR v.stock IS NULL)');
        }

        $where_low_price = '';
        if (!empty($filter['low_price'])) {
            $where_low_price = $this->Database->placehold(' AND v.price = (SELECT MIN(pv.price) FROM __products_variants pv WHERE pv.product_id = v.product_id)');
        }

        // JOIN provider
        $provider_select = "";
        $provider_join = "";
        if (in_array("provider", $join)) {
            $provider_select = $this->Database->placehold(", pr.name as provider_name ");
            $provider_join = $this->Database->placehold(" LEFT JOIN __products_providers as pr ON pr.id = v.provider_id ");
        }

        // JOIN merchant
        $merchant_select = "";
        $merchant_join = "";
        if (in_array("merchant", $join)) {
            $merchant_select = $this->Database->placehold(", merch.merchant_id as merchant_id ");
            $merchant_join = $this->Database->placehold(" LEFT JOIN __products_merchants_variants as merch ON merch.variant_id = v.id ");
        }

        $query = $this->Database->placehold(
            "SELECT
				v.id,
				v.name,
				v.product_id,
				v.provider_id,
				v.price,
				v.old_price,
				v.cost_price,
				v.sku,
				IFNULL(v.stock, ?) as stock,
				(v.stock IS NULL) as infinity,
				v.position,
				v.awaiting_date,
				v.awaiting,
				v.custom,
				v.weight
				$merchant_select
                $provider_select
			FROM 
				__products_variants AS v
				$provider_join
				$merchant_join
			WHERE 
				1
				$where_product_id          
				$where_variant_id
				$where_in_stock
				$where_low_price
			ORDER BY
				v.position",
            $this->Settings->max_order_amount
        );

        $this->Database->query($query);

        $variants = array();
        foreach ($this->Database->results() as $variant) {
            $variants[$variant->id] = $variant;
        }
        return $variants;
    }


    /**
     * Выбираем все варианты продуктов
     * @param $filter
     * @param Array $join array('merchant', 'image', 'category')
     */
    public function get_all_products_variants($filter = array(), $join = array())
    {

        // Фильтр
        $where_filter = "";

        if (!empty($filter)) {
            foreach ($filter as $key => $item) {
                if (!is_array($item)) {
                    $where_filter .= $this->Database->placehold(" AND $key = ?", $item);
                    if ($key == "merchant_id") {
                        $join[] = 'merchant';
                    }
                }
            }
        }

        // JOIN images
        $image_select = "";
        $image_join = "";
        if (in_array("image", $join)) {
            $image_select = $this->Database->placehold(", i.filename as image ");
            $image_join = $this->Database->placehold(" LEFT JOIN __content_images i ON i.entity_id=p.id AND i.entity_name='product' AND i.position=(SELECT MIN(position) FROM __content_images WHERE entity_id=p.id AND entity_name='product') ");
        }

        // JOIN merchant
        $merchant_select = "";
        $merchant_join = "";
        if (in_array("merchant", $join)) {
            $merchant_select = $this->Database->placehold(", merch.merchant_id as merchant_id ");
            $merchant_join = $this->Database->placehold(" LEFT JOIN __products_merchants_variants as merch ON merch.variant_id=v.id ");
        }

        // JOIN category
        $category_select = "";
        $category_join = "";
        if (in_array("category", $join)) {
            $category_select = $this->Database->placehold(", pc.name as category_name ");
            $category_join = $this->Database->placehold(" LEFT JOIN __products_categories pc ON pc.id = p.category_id ");
        }

        $this->Database->query("SET SQL_BIG_SELECTS=1");
        $query = $this->Database->placehold(
            "SELECT 
				v.price, 
				v.old_price, 
				v.id as variant_id, 
				v.name as variant_name, 
				v.position as variant_position, 
                v.stock, 
                v.sku,
				v.weight, 
				p.id as product_id, 
                p.name as product_name, 
				p.url, 
				p.annotation, 
				p.body, 
				p.category_id, 
				p.brand_id 
                $category_select 
				$image_select
				$merchant_select 
			FROM 
				__products_variants v 
				LEFT JOIN __products p ON v.product_id=p.id	
                $category_join
				$image_join 
				$merchant_join
			WHERE 
				p.visible 
				$where_filter
            ORDER BY
             	p.id,
             	v.position"
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Выбираем информацию о Варианте товара
     * @param $id - может быть как и цифрой(id) так и строкой(sku)
     * @return $variant
     */
    public function getVariant($id, $join = array())
    {
        if (empty($id)) {
            return false;
        }

        $where_id = "";
        $where_sku = "";

        if (is_numeric($id)) {
            $where_id = $this->Database->placehold(" AND v.id=? ", intval($id));
        } else {
            $where_sku = $this->Database->placehold(" AND v.sku=? ", $id);
        }

        // JOIN merchant
        $merchant_select = "";
        $merchant_join = "";
        if (in_array("merchant", $join)) {
            $merchant_select = $this->Database->placehold(", merch.merchant_id as merchant_id ");
            $merchant_join = $this->Database->placehold(" LEFT JOIN __products_merchants_variants as merch ON merch.variant_id=v.id  ");
        }

        $query = $this->Database->placehold(
            "SELECT 
				v.id,
				v.product_id,
				v.price,
				v.old_price,
				v.cost_price,
				v.sku,
				IFNULL(v.stock, ?) as stock,
				(v.stock IS NULL) as infinity,
				v.name,
				v.provider_id,
				v.awaiting_date,
				v.awaiting,
				v.custom,
				v.weight
                $merchant_select
			FROM 
				__products_variants v 
				$merchant_join
			WHERE 
                1
				$where_id
                $where_sku
			LIMIT 
				1",
            $this->Settings->max_order_amount
        );

        if ($this->Database->query($query)) {
            return $this->Database->result();
        } else {
            return false;
        }
    }


    /**
     * Update protuct variant data
     * @param $id
     * @param $variant (object)
     * @return $id - variant
     */
    public function update_variant($id, $variant)
    {

        // Если указан прайслист, сохраним его
        if (isset($variant->merchant_id)) {
            if (!empty($variant->merchant_id)) {
                $this->set_variant_in_pricelist($id, $variant->merchant_id);
            } else {
                $this->delete_variant_in_pricelists($id);
            }

            // Очищаем merchant_id так как его нет в таблице __products_variants
            unset($variant->merchant_id);
        }

        // Clear price from symbols
        $check_arr = array("price", "old_price", "cost_price", "weight");
        foreach ($check_arr as $key) {
            if (isset($variant->$key)) {
                $variant->$key = $this->Misc->clearPrice($variant->$key);
            }
        }

        // Преобраазуем stock = null|number
        if (isset($variant->stock)) {
            if ($variant->stock == '' or $variant->stock == '∞') {
                $variant->stock = null;
            }
        }

        $query = $this->Database->placehold("UPDATE __products_variants SET ?% WHERE id=? LIMIT 1", $variant, intval($id));
        if ($this->Database->query($query)) {
            return $id;
        } else {
            return false;
        }
    }


    /**
     * Добавить вариант
     * @param $variant
     */
    public function add_variant($variant)
    {
        $variant = $this->Misc->cleanEntityId($variant);

        // Если указан прайслист, сохраним его
        if (isset($variant->merchant_id)) {
            if (!empty($variant->merchant_id)) {
                $merchant_id = $variant->merchant_id;
            }

            // Очищаем merchant_id так как его нет в таблице __products_variants
            unset($variant->merchant_id);
        }

        // Clear price from symbols
        $check_arr = array("price", "old_price", "cost_price", "weight");
        foreach ($check_arr as $key) {
            if (isset($variant->$key)) {
                $variant->$key = $this->Misc->clearPrice($variant->$key);
            }
        }

        // Преобраазуем stock = null|number
        if (isset($variant->stock)) {
            if ($variant->stock == '' or $variant->stock == '∞') {
                $variant->stock = null;
            }
        }

        $query = $this->Database->placehold("INSERT INTO __products_variants SET ?%", $variant);
        $this->Database->query($query);
        $variant_id = $this->Database->getInsertId();

        if (!empty($merchant_id)) {
            $this->set_variant_in_pricelist($variant_id, $merchant_id);
        }

        return $variant_id;
    }


    /**
     * Удаление варианта
     * @param $id
     */
    public function delete_variant($id)
    {
        if (empty($id)) {
            return false;
        }

        if ($this->Database->query("DELETE FROM __products_variants WHERE id=? LIMIT 1", intval($id))) {
            if ($this->Database->query("UPDATE __orders_purchases SET variant_id=NULL WHERE variant_id=?", intval($id))) {
                if ($this->Database->query("UPDATE __wh_purchases SET variant_id=NULL WHERE variant_id=?", intval($id))) {
                    return $this->delete_variant_in_pricelists($id);
                }
            }
        }

        return false;
    }


    /**
     * Выбираем прайсы связанные с вариантом
     * @param $variant_id
     */
    public function get_variant_related_pricelist($variant_id)
    {
        $query = $this->Database->placehold(
            "SELECT
				*
			FROM
				__products_merchants_variants v
			WHERE
				variant_id=?
			LIMIT
				1",
            $variant_id
        );

        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Устанавливаем варинат в прайслист
     * @param $variant_id
     * @param $merchant_id
     */
    public function set_variant_in_pricelist($variant_id, $merchant_id = null)
    {
        if (empty($merchant_id)) {
            return $this->delete_variant_in_pricelists($variant_id);
        }

        // Если уже есть связь с прайсом, обновляем
        if (!empty($relation = $this->get_variant_related_pricelist($variant_id))) {

            // Если выбран новый прйс, обновляем связь с прайсом
            if (intval($relation->merchant_id) != intval($merchant_id)) {
                $query = $this->Database->placehold(
                    "UPDATE 
                            __products_merchants_variants 
                        SET 
                            ?% 
                        WHERE 
                            variant_id=? 
                        LIMIT 
                            1",
                    ['merchant_id' => $merchant_id],
                    $relation->variant_id
                );
                return $this->Database->query($query);
            }

            // Добавляем связь с прайсом
        } else {
            $data = ['variant_id' => $variant_id, 'merchant_id' => $merchant_id];
            return $this->Database->query("INSERT INTO __products_merchants_variants SET ?%", $data);
        }
    }


    /**
     * Удаляем Вариант из прайслиста
     * @param $variant_id
     */
    public function delete_variant_in_pricelists($variant_id = null)
    {
        return $this->Database->query("DELETE FROM __products_merchants_variants WHERE variant_id = ? LIMIT 1", intval($variant_id));
    }


    /**
     * Обнуление наличия
     * @param $variant
     * @param $filter
     */
    public function restore_stock($variant, $filter = array())
    {
        $where_provider_id = "";
        if (!empty($filter['provider_ids'])) {
            $where_provider_id = $this->Database->placehold(' AND provider_id in(?@)', (array)$filter['provider_ids']);
        }

        $query = $this->Database->placehold(
            "UPDATE 
				__products_variants
			SET 
				?% 
			WHERE 
				1
				$where_provider_id",
            $variant
        );

        return $this->Database->query($query);
    }
}
