<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class OrdersLabelsAdmin extends Auth
{
    public function fetch()
    {

        // Обработка действий
        if ($this->Request->method('post')) {

            // Сортировка
            $positions = $this->Request->post('positions');
            $positions_ids = array_keys($positions);
            sort($positions);
            foreach ($positions as $i => $position) {
                $this->OrdersLabels->update_label($positions_ids[$i], array('position' => $position));
            }


            // Действия с выбранными
            $ids = $this->Request->post('check');
            if (is_array($ids)) {
                switch ($this->Request->post('action')) {
                    case 'delete': {
                        foreach ($ids as $id) {

                            // UPD: Проверить, связана ли метка с заказами

                            $this->OrdersLabels->delete_label($id);
                        }
                        break;
                    }
                }
            }
        }

        // Отображение
        $labels = $this->OrdersLabels->get_labels();


        $this->Design->assign('labels', $labels);
        return $this->Design->fetch('orders/orders_labels.tpl');
    }
}
