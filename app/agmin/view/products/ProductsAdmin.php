<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class ProductsAdmin extends Auth
{
    public function fetch()
    {

        $filter = array();
        $filter['page'] = max(1, $this->Request->get('page', 'integer'));
        $filter['limit'] = $this->Settings->products_num_admin;

        // Категории
        $categories = $this->ProductsCategories->getCategoriesTree();
        $this->Design->assign('categories', $categories);

        // Текущая категория
        $category_id = $this->Request->get('category_id', 'integer');
        $filter['category_id'] = $category_id;
        if ($category_id && $category = $this->ProductsCategories->get_category($category_id)) {
            $filter['category_id'] = $category->children;
        }

        // Все Бренды
        $all_brands = $this->ProductsBrands->get_brands();
        $this->Design->assign('all_brands', $all_brands);

        // Бренды категории
        $brands = $this->ProductsBrands->get_brands(array('category_id' => $filter['category_id']));
        $this->Design->assign('brands', $brands);

        // Текущий бренд
        $brand_id = $this->Request->get('brand_id', 'integer');
        if ($brand_id && $brand = $this->ProductsBrands->get_brand($brand_id)) {
            $filter['brand_id'] = $brand->id;
        }

        // Текущий фильтр
        if ($fltr = $this->Request->get('filter', 'string')) {
            if ($fltr == 'featured') {
                $filter['featured'] = 1;
            } elseif ($fltr == 'sale') {
                $filter['sale'] = 1;
            } elseif ($fltr == 'discounted') {
                $filter['discounted'] = 1;
            } elseif ($fltr == 'visible') {
                $filter['visible'] = 1;
            } elseif ($fltr == 'hidden') {
                $filter['visible'] = 0;
            } elseif ($fltr == 'outofstock') {
                $filter['in_stock'] = 0;
            } elseif ($fltr == 'instock') {
                $filter['in_stock'] = 1;
            } elseif ($fltr == 'stagnation') {
                $filter['stagnation'] = 1;
            } elseif ($fltr == 'purchase') {
                $filter['purchase'] = 1;
            } elseif ($fltr == 'top') {
                $filter['top'] = 1;
            }

            $this->Design->assign('filter', $fltr);
        }

        $filter['date_from'] = $this->Request->get('date_from');
        $this->Design->assign('date_from', $filter['date_from']);

        // Поиск (без 'string' - сжирает запятые)
        $keyword = $this->Request->get('keyword');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->Design->assign('keyword', $keyword);
        }

        // Обработка действий
        if ($this->Request->method('post')) {

            // Сохранение цен и наличия
            if ($prices = $this->Request->post('price') and $stocks = $this->Request->post('stock')) {
                foreach ($prices as $id => $price) {
                    if (isset($stocks[$id])) {
                        $stock = $stocks[$id];
                        if ($stock == '∞' || $stock == '') {
                            $stock = null;
                        }

                        $this->ProductsVariants->update_variant($id, array('price' => $price, 'stock' => $stock));
                    }
                }
            }

            // Сортировка
            $positions = $this->Request->post('positions');
            $positions_ids = array_keys($positions);
            sort($positions);
            $positions = array_reverse($positions);
            foreach ($positions as $i => $position) {
                $this->Products->update_product($positions_ids[$i], array('position' => $position));
            }

            // Действия с выбранными
            $ids = $this->Request->post('check');

            if (!empty($ids)) {
                switch ($this->Request->post('action')) {
                    case 'disable': {
                        $this->Products->update_product($ids, array('visible' => 0));
                        break;
                    }
                    case 'enable': {
                        $this->Products->update_product($ids, array('visible' => 1));
                        break;
                    }
                    case 'set_featured': {
                        $this->Products->update_product($ids, array('featured' => 1));
                        break;
                    }
                    case 'unset_featured': {
                        $this->Products->update_product($ids, array('featured' => 0));
                        break;
                    }
                    case 'set_sale': {
                        $this->Products->update_product($ids, array('sale' => 1));
                        break;
                    }
                    case 'unset_sale': {
                        $this->Products->update_product($ids, array('sale' => 0));
                        break;
                    }
                    case 'delete': {
                        foreach ($ids as $id) {
                            $this->Products->delete_product($id);
                        }
                        break;
                    }
                    case 'duplicate': {
                        foreach ($ids as $id) {
                            $this->Products->duplicate_product(intval($id));
                        }
                        break;
                    }
                    case 'move_to_page': {

                        $target_page = $this->Request->post('target_page', 'integer');

                        // Сразу потом откроем эту страницу
                        $filter['page'] = $target_page;

                        // До какого товара перемещать
                        $limit = $filter['limit'] * ($target_page - 1);
                        if ($target_page > $this->Request->get('page', 'integer')) {
                            $limit += count($ids) - 1;
                        } else {
                            $ids = array_reverse($ids, true);
                        }

                        $temp_filter = $filter;
                        $temp_filter['page'] = $limit + 1;
                        $temp_filter['limit'] = 1;
                        $target_product = $this->Products->get_products($temp_filter);
                        $target_product = array_pop($target_product);
                        $target_position = $target_product->position;

                        // Если вылезли за последний товар - берем позицию последнего товара в качестве цели перемещения
                        if ($target_page > $this->Request->get('page', 'integer') && !$target_position) {
                            $query = $this->Database->placehold("
									SELECT distinct 
										p.position AS target
									FROM 
										__products p 
									WHERE
										1
									ORDER BY 
										p.position DESC
									LIMIT 
										1
								", count($ids));
                            $this->Database->query($query);
                            $target_position = $this->Database->result('target');
                        }

                        foreach ($ids as $id) {
                            $query = $this->Database->placehold("SELECT position FROM __products WHERE id=? LIMIT 1", $id);
                            $this->Database->query($query);
                            $initial_position = $this->Database->result('position');

                            if ($target_position > $initial_position) {
                                $query = $this->Database->placehold("	UPDATE __products set position=position-1 WHERE position>? AND position<=?", $initial_position, $target_position);
                            } else {
                                $query = $this->Database->placehold("	UPDATE __products set position=position+1 WHERE position<? AND position>=?", $initial_position, $target_position);
                            }
                            $this->Database->query($query);

                            $query = $this->Database->placehold("UPDATE __products SET __products.position = ? WHERE __products.id = ?", $target_position, $id);
                            $this->Database->query($query);
                        }
                        break;
                    }
                    case 'move_to_category': {
                        $category_id = $this->Request->post('target_category', 'integer');
                        $filter['page'] = 1;
                        $category = $this->ProductsCategories->get_category($category_id);
                        $filter['category_id'] = $category->children;

                        // BUG __products_categories не существует
                        foreach ($ids as $id) {
                            $query = $this->Database->placehold("DELETE FROM __products_categories WHERE category_id=? AND product_id=? LIMIT 1", $category_id, $id);
                            $this->Database->query($query);
                            $query = $this->Database->placehold("UPDATE IGNORE __products_categories set category_id=? WHERE product_id=? ORDER BY position DESC LIMIT 1", $category_id, $id);
                            $this->Database->query($query);
                            if ($this->Database->affected_rows() == 0) {
                                $query = $this->Database->query("INSERT IGNORE INTO __products_categories set category_id=?, product_id=?", $category_id, $id);
                            }
                        }
                        break;
                    }
                    case 'move_to_brand': {
                        $brand_id = $this->Request->post('target_brand', 'integer');
                        $brand = $this->ProductsBrands->get_brand($brand_id);
                        $filter['page'] = 1;
                        $filter['brand_id'] = $brand_id;
                        $this->Products->update_product($ids, array("brand_id" => $brand_id));

                        // Заново выберем бренды категории
                        $brands = $this->ProductsBrands->get_brands(array('category_id' => $category_id));
                        $this->Design->assign('brands', $brands);

                        break;
                    }
                }
            }
        }

        // Отображение
        if (isset($brand)) {
            $this->Design->assign('brand', $brand);
        }
        if (isset($category)) {
            $this->Design->assign('category', $category);
        }

        // BUG Фильтр по характеристике

        $products_count = $this->Products->count_products($filter);

        // Показать все страницы сразу
        if ($this->Request->get('page') == 'all') {
            $filter['limit'] = $products_count;
        }

        $pages_count = 0;
        if ($filter['limit'] > 0) {
            $pages_count = ceil($products_count / $filter['limit']);
        }

        $filter['page'] = min($filter['page'], $pages_count);
        $this->Design->assign('products_count', $products_count);
        $this->Design->assign('pages_count', $pages_count);
        $this->Design->assign('current_page', $filter['page']);

        if (!empty($products = $this->Products->get_products($filter, array("image")))) {

            $variants = array();
            foreach ($this->ProductsVariants->getVariants(array('product_id' => array_keys($products))) as $variant) {
                $variant->profit_price = $variant->price - $variant->cost_price;
                $variants[$variant->id] = $variant;
            }

            $movements = array();
            foreach ($this->Warehouse->get_product_movements(array_keys($variants)) as $movement) {
                $movements[$movement->id] = $movement;
                $variants[$movement->variant_id]->movements[] = $movement;
            }

            foreach ($variants as $variant) {

                // Подсчитываем общее кол-во поставок
                $variant->movements_amount = 0;
                if (!empty($variant->movements)) {
                    foreach ($variant->movements as $move) {
                        $variant->movements_amount += $move->amount;
                    }
                }

                $products[$variant->product_id]->variants[] = $variant;
            }
        }


        $this->Design->assign('products', $products);

        return $this->Design->fetch('products/products.tpl');
    }
}
