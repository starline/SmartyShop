<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class OrdersDeliveriesAdmin extends Auth
{
    public function fetch()
    {

        // Обработка действий
        if ($this->Request->method('post')) {

            // Действия с выбранными
            $ids = $this->Request->post('check');

            if (is_array($ids)) {
                switch ($this->Request->post('action')) {
                    case 'disable':{
                        $this->OrdersDelivery->update_delivery($ids, array('enabled' => 0));
                        break;
                    }
                    case 'enable': {
                        $this->OrdersDelivery->update_delivery($ids, array('enabled' => 1));
                        break;
                    }
                    case 'delete': {
                        foreach ($ids as $id) {
                            if ($this->Orders->getOrdersCount(array('delivery_id' => $id)) == 0) {
                                $this->OrdersDelivery->delete_delivery($id);
                            } else {
                                $this->Design->assign('message_error', 'order');
                            }
                        }
                        break;
                    }
                }
            }

            // Сортировка
            $positions = $this->Request->post('positions');
            $positions_ids = array_keys($positions);
            sort($positions);
            foreach ($positions as $i => $position) {
                $this->OrdersDelivery->update_delivery($positions_ids[$i], array('position' => $position));
            }

        }

        // Отображение
        $deliveries = $this->OrdersDelivery->getDeliveryMethods();
        $this->Design->assign('deliveries', $deliveries);

        return $this->Design->fetch('orders/orders_deliveries.tpl');
    }
}
