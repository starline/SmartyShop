<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 2.5
 *
 * Работа с товарами
 *
 */

namespace GoodGin;

class Products extends GoodGin
{
    /**
     * Функция возвращает товары
     * @param Array $filter
     * Возможные значения фильтра:
     * id - id товара или их массив
     * category_id - id категории или их массив
     * brand_id - id бренда или их массив
     * page - текущая страница, integer
     * limit - количество товаров на странице, integer
     * sort - порядок товаров, возможные значения: position(по умолчанию), name, price
     * keyword - ключевое слово для поиска
     * features - фильтр по свойствам товара, массив (id свойства => значение свойства)
     * @param Array $join = array('brand', 'category', 'image', 'variant')
     * @param Boolean $count
     */
    public function get_products($filter = array(), $join = array(), $count = false)
    {

        if (!$this->Misc->check_filter_params($filter, array('id', 'brand_id', 'category_id'))) {
            return array();
        }

        $order = "p.position DESC"; // сортировка по умолчанию

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


        $where_product_id = "";
        if (isset($filter['id'])) {
            $where_product_id = $this->Database->placehold(' AND p.id in(?@)', (array)$filter['id']);
        }


        $where_category_id = "";
        if (isset($filter['category_id'])) {
            $where_category_id = $this->Database->placehold(' AND p.category_id in(?@)', (array)$filter['category_id']);
        }


        $where_brand_id = "";
        if (isset($filter['brand_id'])) {
            $where_brand_id = $this->Database->placehold(' AND p.brand_id in(?@)', (array)$filter['brand_id']);
        }


        $where_is_featured = "";
        if (isset($filter['featured'])) {
            $where_is_featured = $this->Database->placehold(' AND p.featured=?', intval($filter['featured']));
        }


        $where_features = "";
        if (!empty($filter['features'])) {
            foreach ($filter['features'] as $feature => $value) {
                $where_features .= $this->Database->placehold(" AND p.id in (SELECT product_id FROM __products_options WHERE feature_id=? AND value in(?@)) ", $feature, (array)$value);
            }
        }


        $where_discounted = "";
        if (isset($filter['discounted'])) {
            $join[] = 'variant';
            $where_discounted = $this->Database->placehold(" AND IF (variant.old_price>variant.price, 1, 0)=? ", intval($filter['discounted']));
        }


        $where_sale = "";
        if (isset($filter['sale'])) {
            $where_sale = $this->Database->placehold(' AND p.sale=?', intval($filter['sale']));
        }


        $where_in_stock = "";
        if (isset($filter['in_stock'])) {
            $join[] = 'variant';
            $where_in_stock = $this->Database->placehold(" AND IF (variant.stock>0 OR variant.stock IS NULL, 1, 0)=? ", intval($filter['in_stock']));
        }


        $where_visible = "";
        if (isset($filter['visible'])) {
            $where_visible = $this->Database->placehold('AND p.visible=?', intval($filter['visible']));
        }


        // Товары застоявшиеся на складе
        $select_stagnation = "";
        $where_stagnation = "";
        $join_stagnation = "";
        if (isset($filter['stagnation'])) {
            $join[] = 'variant';
            $select_stagnation = $this->Database->placehold(", orders.id as order_id, orders.date as order_date, IF (orders.date IS NULL, 1, 0) as never_ordered");
            $join_stagnation = $this->Database->placehold(" LEFT JOIN s_orders orders on orders.id=(SELECT order_id FROM s_orders_purchases LEFT JOIN s_orders o on o.id=order_id AND o.paid=1 WHERE product_id=p.id ORDER BY o.date DESC LIMIT 1)");
            $where_stagnation = $this->Database->placehold(" AND IF (variant.stock>0 OR variant.stock IS NULL, 1, 0) = 1 ");
            $order = $this->Database->placehold("orders.date ASC");
        }


        // Самые прибыльные товары
        $select_top = "";
        $where_top = "";
        $join_top = "";
        if (isset($filter['top'])) {
            if (empty($filter['date_from'])) {
                $filter['date_from'] = date('Y-m-d', strtotime('-30 days'));
            }
            $select_top = $this->Database->placehold(", ordpur.profit as profit, ordpur.sold as sold");
            $join_top = $this->Database->placehold(" LEFT JOIN (SELECT SUM((op.price-op.cost_price)*op.amount) as profit, product_id, SUM(op.amount) as sold FROM s_orders_purchases op LEFT JOIN s_orders ord on ord.id = op.order_id WHERE ord.date>? AND ord.paid=1 AND ord.closed=1 GROUP BY product_id) ordpur on ordpur.product_id=p.id ", $filter['date_from']);
            $where_top = $this->Database->placehold(" AND ordpur.profit IS NOT NULL ");
            $order = $this->Database->placehold("ordpur.profit DESC");
        }


        // Предложение по закупке
        $select_purchase = "";
        $where_purchase = "";
        $join_purchase = "";
        $group_purchase = "";
        if (isset($filter['purchase'])) {

            // По-умолчанию берем продажи за 60 дней
            if (empty($filter['date_from'])) {
                $filter['date_from'] = date('Y-m-d', strtotime('-60 days'));
            }

            $select_purchase = $this->Database->placehold(", ordpur.sold as sold, IF (whpur.waiting IS NULL,0,whpur.waiting) as waiting, varnt.stock as stock, MAX(-(varnt.stock - ordpur.sold*2 + IF (whpur.waiting IS NULL,0,whpur.waiting))) as need, varnt.id as variant_id ");
            $join_purchase = $this->Database->placehold(" 
                LEFT JOIN (SELECT product_id, id, stock FROM __products_variants) varnt on varnt.product_id=p.id 
                LEFT JOIN (SELECT variant_id, SUM(op.amount) as sold FROM s_orders_purchases op LEFT JOIN s_orders ord on ord.id = op.order_id WHERE ord.date>? AND ord.paid=1 AND ord.closed=1 GROUP BY variant_id) ordpur on ordpur.variant_id=varnt.id 
                LEFT JOIN (SELECT variant_id, SUM(whp.amount) as waiting FROM s_wh_purchases whp LEFT JOIN s_wh_movements whm on whm.id=whp.movement_id WHERE whm.status=1 GROUP BY variant_id) whpur on whpur.variant_id=varnt.id 
                ", $filter['date_from']);
            $where_purchase = $this->Database->placehold(" AND ordpur.sold is not null AND -(varnt.stock - ordpur.sold*2 + IF (whpur.waiting IS NULL,0,whpur.waiting)) > 0 ");
            $group_purchase = $this->Database->placehold("GROUP BY p.id");
            $order = $this->Database->placehold("need DESC");
        }


        if (!empty($filter['sort'])) {
            switch ($filter['sort']) {
                case 'position':
                    $order = 'p.position DESC';
                    break;
                case 'name':
                    $order = 'p.name';
                    break;
                case 'created':
                    $order = 'p.created DESC';
                    break;
                case 'price':
                    $order = '(
						SELECT 
							pv.price
						FROM 
							__products_variants pv 
						WHERE 
							p.id = pv.product_id  
						ORDER BY 
							pv.price ASC
						LIMIT 1
					) ASC';
                    break;
            }
        }

        $order_by_disable = "";
        if (isset($filter['sort_disable'])) {
            $order_by_disable = $this->Database->placehold('p.disable ASC, ');
        }

        // Сортировка по наличию
        $order_by_in_stock = "";
        if (isset($filter['sort_in_stock'])) {
            $join[] = 'variant';
            $order_by_in_stock = $this->Database->placehold(" variant_in_stock DESC, ");
        }

        // поиск по слову
        $where_keyword = "";
        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);

            // Создаем два варианта с точкой и запятой
            foreach ($keywords as $keyword) {
                $kw = $this->Database->escape(trim($keyword));

                $kwr_sql_c = "";
                if (stripos($kw, ",")) {
                    $kwr = str_replace(",", ".", $keyword);
                    $kwr_sql_c  = $this->Database->placehold("OR p.name LIKE '%$kwr%'");
                }

                $kwr_sql_d = "";
                if (stripos($kw, ".")) {
                    $kwr = str_replace(".", ",", $keyword);
                    $kwr_sql_d  = $this->Database->placehold("OR p.name LIKE '%$kwr%'");
                }

                $where_keyword .= $this->Database->placehold(" AND (p.name LIKE '%$kw%' OR p.id in (SELECT product_id FROM __products_variants WHERE sku LIKE '%$kw%') $kwr_sql_c  $kwr_sql_d)");
            }
        }

        // JOIN VARIANT
        $join_variant = "";
        $select_variant = "";
        if (in_array("variant", $join)) {
            $select_variant = $this->Database->placehold(", IF (variant.stock>0 OR variant.stock IS NULL, 1, 0) as variant_in_stock, variant.stock as variant_stock ");
            $join_variant = $this->Database->placehold(" LEFT JOIN __products_variants variant ON variant.id=(SELECT id FROM __products_variants WHERE product_id=p.id ORDER BY stock DESC LIMIT 1) ");
        }

        // JOIN IMAGE
        $select_image = '';
        $join_image = '';
        if (in_array("image", $join)) {
            $select_image = $this->Database->placehold(", i.filename as image_filename");
            $join_image =  $this->Database->placehold("LEFT JOIN __content_images i ON i.entity_id=p.id AND i.entity_name='product' AND i.position=(SELECT MIN(position) FROM __content_images WHERE entity_id=p.id and entity_name='product')");
        }

        // JOIN BRAND
        $select_brand = '';
        $join_brand = '';
        if (in_array("brand", $join)) {
            $select_brand = $this->Database->placehold(", b.url as brand_url, b.image as brand_image, b.name as brand_name");
            $join_brand =  $this->Database->placehold("LEFT JOIN __brands b ON p.brand_id=b.id");
        }

        // JOIN CATEGORY
        $select_category = '';
        $join_category = '';
        if (in_array("category", $join)) {
            $select_category = $this->Database->placehold(", c.url as category_url, c.name as category_name");
            $join_category =  $this->Database->placehold("LEFT JOIN __products_categories c ON p.category_id=c.id");
        }


        // Выбираем товары
        if ($count === false) {
            $query = $this->Database->placehold(
                "SELECT  
					p.id,
                    p.name,
                    p.url,
                    p.annotation,
                    p.brand_id,
                    p.category_id,
                    p.position,
                    p.visible,
                    p.disable,
                    p.featured,
                    p.sale
					$select_category
					$select_brand
					$select_image
                    $select_variant
                    $select_stagnation
                    $select_top
                    $select_purchase
				FROM 
					__products p 
					$join_category 
					$join_brand 
					$join_image 
                    $join_variant 
                    $join_stagnation
                    $join_top
                    $join_purchase
				WHERE 
					1
					$where_category_id
					$where_product_id
					$where_brand_id
					$where_features
					$where_keyword
					$where_is_featured
					$where_discounted 
					$where_sale 
					$where_in_stock
                    $where_stagnation
                    $where_top
                    $where_purchase
					$where_visible
                $group_purchase
				ORDER BY 
                    $order_by_in_stock
                    $order_by_disable
					$order 
				$sql_limit"
            );

            $this->Database->query($query);

            $products = array();
            foreach ($this->Database->results() as $product) {
                $products[$product->id] = $product;
            }
            return $products;


            // Выбираем кол-во
        } else {
            $query = $this->Database->placehold(
                "SELECT
					count(distinct p.id) as count
				FROM 
					__products p 
                    $join_category 
					$join_brand 
					$join_image 
                    $join_variant 
                    $join_stagnation
                    $join_top
                    $join_purchase
				WHERE
					1
					$where_category_id
					$where_product_id
					$where_brand_id
					$where_features
					$where_keyword
					$where_is_featured
					$where_discounted 
					$where_sale 
					$where_in_stock
                    $where_stagnation
                    $where_top
                    $where_purchase
					$where_visible"
            );

            $this->Database->query($query);
            return $this->Database->result('count');
        }
    }


    /**
     * Функция возвращает количество товаров
     * @param $filter
     * @param $join
     */
    public function count_products($filter = array(), $join = array())
    {
        return $this->get_products($filter, $join, true);
    }


    /**
     * Функция возвращает товар
     * @param	$id - id или name
     * @return	object
     */
    public function get_product($id)
    {
        if (is_numeric($id)) {
            $filter = $this->Database->placehold('p.id = ?', intval($id));
        } else {
            $filter = $this->Database->placehold('p.url = ?', $id);
        }

        $query = $this->Database->placehold(
            "SELECT DISTINCT
				p.*
			FROM 
				__products AS p
            WHERE 
				$filter
            LIMIT 
				1"
        );

        $this->Database->query($query);
        $product = $this->Database->result();
        return $product;
    }


    /**
     * Обновляем товар
     * @param Integer|Array $id
     * @param Object|Array $product
     */
    public function update_product($id, $product)
    {
        if (is_array($product)) {
            $product = (object) $product;
        }
        $product = $this->checkProductURL($product);

        $query = $this->Database->placehold("UPDATE __products SET ?% WHERE id in (?@) LIMIT ?", $product, (array)$id, count((array)$id));
        if ($this->Database->query($query)) {
            return $id;
        } else {
            return false;
        }
    }


    /**
     * Создание нового товара
     * @param $product
     */
    public function add_product($product)
    {
        $product = $this->Misc->cleanEntityId($product);
        $product = $this->checkProductURL($product);

        if ($this->Database->query("INSERT INTO __products SET ?%", $product)) {
            $id = $this->Database->getInsertId();
            $this->Database->query("UPDATE __products SET position=id WHERE id=?", intval($id));
            return $id;
        } else {
            return false;
        }
    }


    /**
     * Удалить товар
     * @param $id
     */
    public function delete_product($id)
    {
        if (empty($id)) {
            return false;
        }

        // Удаляем варианты
        $variants = $this->ProductsVariants->getVariants(array('product_id' => $id));
        foreach ($variants as $v) {
            $this->ProductsVariants->delete_variant($v->id);
        }

        // Удаляем основные изображения
        $images = $this->Images->getImages($id, 'product');
        foreach ($images as $i) {
            $this->Images->deleteImage($i->id);
        }

        // Удаляем изображения контента
        $images = $this->Images->getImages($id, 'product_content');
        foreach ($images as $i) {
            $this->Images->deleteImage($i->id);
        }

        // Удаляем свойства
        $options = $this->ProductsFeatures->getOptions(array('product_id' => $id));
        foreach ($options as $o) {
            $this->ProductsFeatures->deleteOption($id, $o->feature_id);
        }

        // Удаляем связанные товары
        $related = $this->get_related_products($id);
        foreach ($related as $r) {
            $this->delete_related_product($id, $r->related_id);
        }

        // Удаляем товар из связанных с другими
        $query = $this->Database->placehold("DELETE FROM __products_products_related WHERE related_id=?", intval($id));
        $this->Database->query($query);

        // Удаляем отзывы
        $comments = $this->Comments->getComments(array('entity_id' => $id, 'type' => 'product'));
        foreach ($comments as $c) {
            $this->Comments->deleteComment($c->id);
        }

        // Зачищаем из покупок
        $this->Database->query('UPDATE __orders_purchases SET product_id=NULL WHERE product_id=?', intval($id));

        // Зачищаем из поставок
        $this->Database->query('UPDATE __wh_purchases SET product_id=NULL WHERE product_id=?', intval($id));

        // Удаляем товар
        $query = $this->Database->placehold("DELETE FROM __products WHERE id=? LIMIT 1", intval($id));
        return $this->Database->query($query);
    }


    /**
     * Создаем дубликат товара
     * @param $id
     */
    public function duplicate_product($id)
    {
        $product = $this->get_product($id);

        unset($product->id);
        unset($product->created);

        $product->visible = 0;
        $product->featured = 0;
        $product->meta_title = '';
        $product->sale = 0;
        $product->name .= " - копия";

        // Сдвигаем товары вперед и вставляем копию на соседнюю позицию
        $this->Database->query('UPDATE __products SET position=position+1 WHERE position>?', $product->position);
        $new_id = $this->Products->add_product($product);
        $this->Database->query('UPDATE __products SET position=? WHERE id=?', $product->position + 1, $new_id);

        // Очищаем url. Если сделать unset, то возьмет url из name (нам это не надо)
        $this->Database->query('UPDATE __products SET url="" WHERE id=?', $new_id);

        // Дублируем изображения
        $images = $this->Images->getImages($id, 'product');
        foreach ($images as $image) {
            $this->Images->addImage($new_id, 'product', $image->filename);
        }

        // Дублируем варианты
        $variants = $this->ProductsVariants->getVariants(array('product_id' => $id));
        foreach ($variants as $variant) {
            $variant->product_id = $new_id;

            // Очищаем лишние данные, их нет в таблице БД
            unset($variant->id);
            unset($variant->sku);
            unset($variant->provider_name);
            unset($variant->infinity);
            unset($variant->old_price);
            unset($variant->price);
            unset($variant->cost_price);

            // Устанавливаем кол-во на складе
            $variant->stock = 0;

            $this->ProductsVariants->add_variant($variant);
        }

        // Дублируем свойства
        $options = $this->ProductsFeatures->getOptions(array('product_id' => $id));
        foreach ($options as $o) {
            $this->ProductsFeatures->update_option($new_id, $o->feature_id, $o->value);
        }

        // Дублируем связанные товары
        $related = $this->get_related_products($id);
        foreach ($related as $r) {
            $this->add_related_product($new_id, $r->related_id);
        }

        return $new_id;
    }


    /**
     * Выбираем связанные товары
     * @param $product_id
     * @param $count
     */
    public function get_related_products($product_id, $count = false)
    {
        if (empty($product_id)) {
            return array();
        }

        $product_id_filter = $this->Database->placehold(' AND product_id=?', intval($product_id));

        $limit = '';
        if ($count) {
            $limit = $this->Database->placehold(' LIMIT ?', $count);
        }

        $query = $this->Database->placehold(
            "SELECT
				product_id, 
                related_id, 
                position
			FROM 
				__products_products_related
			WHERE 
				1
				$product_id_filter 
			ORDER BY
				position 
			$limit"
        );

        $this->Database->query($query);

        $rel_products = array();
        foreach ($this->Database->results() as $rel_product) {
            $rel_products[$rel_product->related_id] = $rel_product;
        }
        return $rel_products;
    }


    /**
     * Добавляем связанные товары
     */
    public function add_related_product($product_id, $related_id, $position = 0)
    {
        $query = $this->Database->placehold("INSERT INTO __products_products_related SET product_id=?, related_id=?, position=?", $product_id, $related_id, $position);
        $this->Database->query($query);
        return $related_id;
    }


    /**
     * Удаление связанного товара
     */
    public function delete_related_product($product_id, $related_id)
    {
        $query = $this->Database->placehold("DELETE FROM __products_products_related WHERE product_id=? AND related_id=? LIMIT 1", intval($product_id), intval($related_id));
        $this->Database->query($query);
    }


    /**
     * Удаляем все связанные товары
     * @param $product_id
     */
    public function delete_all_related_products($product_id)
    {
        $query = $this->Database->placehold('DELETE FROM __products_products_related WHERE product_id=?', intval($product_id));
        $this->Database->query($query);

    }


    /**
     * Проверям совпадения URL
     * @param $product
     */
    private function checkProductURL($product)
    {
        if (isset($product->name)) {

            // Если URL не задан - Создаем URL из названия
            if (empty($product->url)) {
                $product->url = $this->Misc->transliteration_ru_en($product->name);
            } else {

                // Оставляем только допустимые символы
                $product->url = $this->Misc->transliteration_ru_en($product->url);
            }

            // Если есть товар с таким URL, добавляем к нему число
            // Use "-" in URL, because that needs for SEO
            while ($temp_product = $this->get_product((string)$product->url) and (isset($product->id) and $temp_product->id != $product->id)) {
                if (preg_match('/(.+)-([0-9]+)$/', $product->url, $parts)) {
                    $product->url = $parts[1] . '-' . ($parts[2] + 1);
                } else {
                    $product->url = $product->url . '-2';
                }
            }
        }
        return $product;
    }
}
