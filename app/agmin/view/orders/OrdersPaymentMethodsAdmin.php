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

class OrdersPaymentMethodsAdmin extends Auth
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
                $this->OrdersPayment->updatePaymentMethod($positions_ids[$i], array('position' => $position));
            }

            // Действия с выбранными
            $ids = $this->Request->post('check');

            if (is_array($ids)) {
                switch ($this->Request->post('action')) {
                    case 'disable': {
                        $this->OrdersPayment->updatePaymentMethod($ids, array('enabled' => 0));
                        break;
                    }
                    case 'enable': {
                        $this->OrdersPayment->updatePaymentMethod($ids, array('enabled' => 1));
                        break;
                    }
                    case 'delete': {
                        foreach ($ids as $id) {
                            if ($this->Orders->getOrdersCount(array('payment_method_id' => $id)) == 0) {
                                $this->OrdersPayment->deletePaymentMethod($id);
                            } else {
                                $this->Design->assign('message_error', 'order');
                            }
                        }
                        break;
                    }
                }
            }
        }

        // Отображение
        $payment_methods = $this->OrdersPayment->getPaymentMethods();
        $this->Design->assign('payment_methods', $payment_methods);

        return $this->Design->fetch('orders/orders_payment_methods.tpl');
    }
}
