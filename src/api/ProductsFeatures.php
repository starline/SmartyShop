<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 * @version 1.4
 *
 * Тут работаем с свойствами товара
 *
 */

namespace GoodGin;

class ProductsFeatures extends GoodGin
{
    /**
     * Выбираем названия характеристик
     * @param $filter
     */
    public function get_features($filter = array())
    {

        $where_category_id = '';
        if (isset($filter['category_id'])) {
            $where_category_id = $this->Database->placehold(' AND f.id in(SELECT feature_id FROM __products_categories_features AS cf WHERE cf.category_id in(?@))', (array)$filter['category_id']);
        }

        $where_in_filter = '';
        if (isset($filter['in_filter'])) {
            $where_in_filter = $this->Database->placehold(' AND f.in_filter=?', intval($filter['in_filter']));
        }

        $where_id = '';
        if (!empty($filter['id'])) {
            $where_id = $this->Database->placehold(' AND f.id in(?@)', (array)$filter['id']);
        }

        // Выбираем свойства
        $query = $this->Database->placehold(
            "SELECT
				f.id,
				f.name,
				f.position,
				f.in_filter
			FROM 
				__products_features AS f
			WHERE 
				1
				$where_category_id
				$where_in_filter
				$where_id
			ORDER BY 
				f.position"
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Выбираем название характеристики
     * @param $id
     */
    public function getFeature($id)
    {
        if (is_numeric($id)) {
            $where = $this->Database->placehold('id = ?', $id);
        } else {
            $where = $this->Database->placehold('name = ?', trim($id));
        }

        $query = $this->Database->placehold(
            "SELECT
                id,
                name, 
                position, 
                in_filter 
            FROM 
                __products_features 
            WHERE 
                $where 
            LIMIT 
                1",
            $id
        );

        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Выбиарем категории в которых есть характеристика
     * @param $id
     */
    public function getFeatureCategories($id)
    {
        $query = $this->Database->placehold(
            "SELECT 
                cf.category_id as category_id 
            FROM 
                __products_categories_features cf
			WHERE 
                cf.feature_id = ?",
            $id
        );

        $this->Database->query($query);
        return $this->Database->results('category_id');
    }


    /**
     * Добавить характеристики товара
     * @param $feature
     */
    public function addFeature($feature)
    {
        $feature = $this->Misc->cleanEntityId($feature);
        $this->Database->query("INSERT INTO __products_features SET ?%", $feature);

        if (!empty($id = $this->Database->getInsertId())) {
            $this->Database->query("UPDATE __products_features SET position=id WHERE id=? LIMIT 1", $id);
            return $id;
        }

        return false;
    }


    /**
     * Update Featture
     */
    public function updateFeature($id, $feature)
    {
        $query = $this->Database->placehold("UPDATE __products_features SET ?% WHERE id in(?@) LIMIT ?", (array)$feature, (array)$id, count((array)$id));
        $this->Database->query($query);
        return $id;
    }


    public function deleteFeature(int $id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("DELETE FROM __products_features WHERE id=? LIMIT 1", intval($id));
        $this->Database->query($query);

        $query = $this->Database->placehold("DELETE FROM __products_options WHERE feature_id=?", intval($id));
        $this->Database->query($query);

        $query = $this->Database->placehold("DELETE FROM __products_categories_features WHERE feature_id=?", intval($id));
        $this->Database->query($query);

        $query = $this->Database->placehold("DELETE FROM __products_features_variants WHERE feature_id=?", intval($id));
        $this->Database->query($query);
    }


    /**
     * Feature variants
     */
    public function getFeatureVariants(int $feature_id)
    {
        $query = $this->Database->placehold(
            "SELECT 
				fv.name
			FROM 
				__products_features_variants fv 
			WHERE
				fv.feature_id = ? 
			ORDER BY  
				position ASC ",
            $feature_id
        );

        $this->Database->query($query);
        return $this->Database->results('name');
    }


    /**
     * Обновление вариантов характеристик
     * @param $feature_id
     * @param $variants
     */
    public function updateFeatureVariants($feature_id, $variants)
    {
        if (empty($feature_id)) {
            return false;
        }

        $feature_id = intval($feature_id);
        $this->Database->query("DELETE pfv FROM __products_features_variants pfv WHERE pfv.feature_id=?", $feature_id);

        if (!empty($variants) and is_array($variants)) {
            $values = array();
            foreach ($variants as $index => $variant) {

                // Исключавем пустые значения варината
                if (!empty($variant)) {
                    $values[] = "($feature_id, '$variant', $index)";
                }
            }
            return $this->Database->query("INSERT INTO __products_features_variants (feature_id, name, position) VALUES " . join(', ', $values));
        }
        return true;
    }


    public function addFeatureCategory($id, $category_id)
    {
        return $this->Database->query("INSERT IGNORE INTO __products_categories_features SET feature_id=?, category_id=?", $id, $category_id);
    }


    /**
     * Update feature categories
     * @param Integer $feature_id
     * @param Array $categories
     */
    public function updateFeatureCategories($feature_id, $categories)
    {

        if (empty($feature_id)) {
            return false;
        }

        $feature_id = intval($feature_id);
        $this->Database->query("DELETE pcf FROM __products_categories_features pcf WHERE pcf.feature_id=?", $feature_id);

        if (!empty($categories) and is_array($categories)) {
            $values = array();
            foreach ($categories as $category) {
                $values[] = "($feature_id , " . intval($category) . ")";
            }
            $this->Database->query("INSERT INTO __products_categories_features (feature_id, category_id) VALUES " . join(', ', $values));

            // Удалим значения из options
            $query = $this->Database->placehold(
                "DELETE 
					o 
				FROM 
					__products_options o
			        LEFT JOIN __products p ON p.id=o.product_id
			    WHERE 
					o.feature_id = ? 
					AND p.category_id not in(?@)",
                $feature_id,
                $categories
            );

            return $this->Database->query($query);
        } else {

            // Удалим значения из options
            return $this->Database->query("DELETE po FROM __products_options po WHERE po.feature_id=?", $feature_id);
        }
    }


    public function deleteOption($product_id, $feature_id)
    {
        return $this->Database->query("DELETE FROM __products_options WHERE product_id=? AND feature_id=? LIMIT 1", intval($product_id), intval($feature_id));
    }


    public function update_option($product_id, $feature_id, $value)
    {
        if (!empty($value)) {
            $query = $this->Database->placehold("REPLACE INTO __products_options SET value=?, product_id=?, feature_id=?", $value, intval($product_id), intval($feature_id));
        } else {
            $query = $this->Database->placehold("DELETE FROM __products_options WHERE feature_id=? AND product_id=?", intval($feature_id), intval($product_id));
        }
        return $this->Database->query($query);
    }


    /**
     * Выбираем варианты характеристик
     * @param  $filter
     */
    public function getOptions($filter = array())
    {

        if (!$this->Misc->check_filter_params($filter, array('feature_id', 'product_id', 'category_id', 'brand_id'))) {
            return array();
        }

        $where_feature_id = '';
        if (isset($filter['feature_id'])) {
            $where_feature_id = $this->Database->placehold('AND po.feature_id in(?@)', (array)$filter['feature_id']);
        }

        $where_product_id = '';
        if (isset($filter['product_id'])) {
            $where_product_id = $this->Database->placehold('AND po.product_id in(?@)', (array)$filter['product_id']);
        }

        $where_brand_id = '';
        if (isset($filter['brand_id'])) {
            $where_brand_id = $this->Database->placehold('AND po.product_id in(SELECT id FROM __products WHERE brand_id in(?@))', (array)$filter['brand_id']);
        }

        $where_keyword = '';
        if (isset($filter['keyword'])) {
            $kw = $filter['keyword'];
            $where_keyword = $this->Database->placehold("AND po.value LIKE '%$kw%'");
        }

        $from_visible = '';
        $where_category_id = '';
        if (isset($filter['visible'])) {
            $from_visible = $this->Database->placehold('INNER JOIN __products p ON p.id=po.product_id AND p.visible=?', intval($filter['visible']));

            if (isset($filter['category_id'])) {
                $where_category_id = $this->Database->placehold('AND p.category_id in(?@)', (array)$filter['category_id']);
            }
        }

        $where_features = '';
        if (isset($filter['features'])) {
            foreach ($filter['features'] as $feature => $value) {
                $where_features .= $this->Database->placehold('AND (po.feature_id=? OR po.product_id in (SELECT product_id FROM __products_options WHERE feature_id=? AND value=? )) ', $feature, $feature, $value);
            }
        }

        $limit = "";
        if (isset($filter['limit'])) {
            $limit = $this->Database->placehold(" LIMIT ?", $filter['limit']);
        }

        $query = $this->Database->placehold(
            "SELECT 
                DISTINCT po.value,
				po.feature_id
		    FROM 
				__products_options po
				$from_visible
			WHERE 
				1
				$where_category_id
				$where_feature_id
				$where_product_id
				$where_brand_id
				$where_features
                $where_keyword
			ORDER BY 
				po.value = 0,
				-po.value DESC,
				po.value
            $limit"
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Select pruduct features with option value
     * @param $product_id
     */
    public function get_product_options($product_id)
    {
        $query = $this->Database->placehold(
            "SELECT 
				f.id as feature_id,
				f.name,
				po.value,
				po.product_id 
			FROM 
				__products_options po 
				LEFT JOIN __products_features f ON f.id=po.feature_id
			WHERE 
				po.product_id in(?@)
			ORDER BY 
				f.position",
            (array)$product_id
        );

        $this->Database->query($query);
        return $this->Database->results();
    }
}
