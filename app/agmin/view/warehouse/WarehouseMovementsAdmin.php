<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class WarehouseMovementsAdmin extends Auth
{
    public function fetch()
    {

        $filter = array();
        $filter['page'] = max(1, $this->Request->get('page', 'integer'));
        $filter['limit'] = $this->Settings->products_num_admin;
        $filter['status'] = $this->Request->get('status', 'integer'); // Тип перемещения

        // Поиск
        $keyword = $this->Request->get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->Design->assign('keyword', $keyword);
        }

        // Обработка действий
        if ($this->Request->method('post')) {

            // Действия с выбранными
            $ids = $this->Request->post('check');
            if (is_array($ids)) {
                switch ($this->Request->post('action')) {
                    case 'delete': {
                        foreach ($ids as $id) {
                            $whm = $this->Warehouse->get_movement(intval($id));

                            // Удалять можно только отмененный (4)
                            if ($whm->status == 4) { 
                                $this->Warehouse->delete_movement(intval($whm->id));
                            }
                        }
                        break;
                    }
                }
            }
        }

        $movements_count = $this->Warehouse->count_movements($filter);

        // Показать все страницы сразу
        if ($this->Request->get('page') == 'all') {
            $filter['limit'] = $movements_count;
        }

        // Выбираем все поставки
        $movements = $this->Warehouse->get_movements($filter);

        if (!empty($movements)) {

            // Выбираем фотоотчеты
            foreach($this->Images->getImages(array_keys($movements), 'warehouse') as $wh_image) {
                $movements[$wh_image->entity_id]->images[] = $wh_image;
            }

            // Товары
            foreach ($this->Warehouse->getPurchases(array('movement_id' => array_keys($movements)), array("image")) as $op) {
                $movements[$op->movement_id]->purchases[] = $op;
            }
        }

        // Собираем статистические данные
        $total = new stdClass();
        $total->sum_wholesale_price = 0;
        $total->sum_price = 0;
        $total->sum_stock = 0;

        // Для новый и ожидаемые
        if ($filter['status'] === 0 || $filter['status'] == 1) {

            $await_filter['status'] = $filter['status'];
            $total->await_movements_count = $this->Warehouse->count_movements($await_filter);

            // Выбираем поставки
            $await_movements = $this->Warehouse->get_movements($await_filter);

            if (!empty($await_movements)) {

                // Выбираем Товары
                foreach ($this->Warehouse->getPurchases(array('movement_id' => array_keys($await_movements))) as $op) {

                    // Выбираем вариант
                    $v = $this->ProductsVariants->getVariant($op->variant_id);

                    // Вычисляем общую сумму
                    if (isset($v->price) and isset($v->cost_price)) {
                        $total->sum_stock += $op->amount;
                        $total->sum_price += ($v->price * $op->amount);
                        $total->sum_wholesale_price += ($v->cost_price * $op->amount);
                    }
                }
            }
        }

        $this->Design->assign('total', $total);


        $this->Design->assign('pages_count', ceil($movements_count / $filter['limit']));
        $this->Design->assign('current_page', $filter['page']);
        $this->Design->assign('movements_count', $movements_count);
        $this->Design->assign('status', $filter['status']);
        $this->Design->assign('movements', $movements);

        return $this->Design->fetch('warehouse/warehouse_movements.tpl');
    }
}
