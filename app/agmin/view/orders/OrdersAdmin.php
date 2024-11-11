<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 * status: 0 - Новый, 1 - Принят, 4 - Отгружен,  2 - Выполнен, 3 - Отмена
 */

if (!defined('secure')) {
    exit('Access denied');
}

class OrdersAdmin extends Auth
{
    public function fetch()
    {

        $filter = array();

        // Поиск
        $keyword = $this->Request->get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->Design->assign('keyword', $keyword);
        }

        // Фильтр по метке
        $label = $this->OrdersLabels->get_label($this->Request->get('label'));
        if (!empty($label)) {
            $filter['label'] = $label->id;
            $this->Design->assign('label', $label);
        }

        // Оплачены/Не оплачены
        $paid = $this->Request->get('paid', "integer");
        $filter['paid'] = $paid;
        $this->Design->assign('paid', $paid);


        // Обработка действий
        if ($this->Request->method('post')) {

            // Действия с выбранными
            $ids = $this->Request->post('check');
            if (is_array($ids)) {
                switch ($this->Request->post('action')) {
                    case 'delete': {
                        foreach ($ids as $id) {
                            $o = $this->Orders->getOrder(intval($id));

                            // Если заказ Новый(0) Принят(1) Выполнен(2) Отгружен(4)
                            if ($o->status < 3 || $o->status == 4) {
                                $this->Orders->update_order($id, array('status' => 3));
                                $this->Orders->open($id);
                            }
                            // Если заказ Отменен(3) - удаляем из базы
                            elseif ($this->access('orders_delete')) {
                                $this->Orders->delete_order($id);
                            }
                        }
                        break;
                    }
                    case 'set_status_0': {
                        foreach ($ids as $id) {
                            if ($this->Orders->open(intval($id))) {
                                $this->Orders->update_order($id, array('status' => 0));
                            }
                        }
                        break;
                    }
                    case 'set_status_1': {
                        foreach ($ids as $id) {
                            if (!$this->Orders->close(intval($id))) {
                                $this->Design->assign('message_error', 'error_closing');
                            } else {
                                $this->Orders->update_order($id, array('status' => 1));
                            }
                        }
                        break;
                    }
                    case 'set_status_2': {
                        foreach ($ids as $id) {
                            if (!$this->Orders->close(intval($id))) {
                                $this->Design->assign('message_error', 'error_closing');
                            } else {
                                $this->Orders->update_order($id, array('status' => 2));
                            }
                        }
                        break;
                    }
                    case 'set_status_4': {
                        foreach ($ids as $id) {
                            if (!$this->Orders->close(intval($id))) {
                                $this->Design->assign('message_error', 'error_closing');
                            } else {
                                $this->Orders->update_order($id, array('status' => 4));
                            }
                        }
                        break;
                    }
                    case (preg_match('/^set_label_([0-9]+)/', $this->Request->post('action'), $a) ? true : false): {
                        $l_id = intval($a[1]);
                        if ($l_id > 0) {
                            foreach ($ids as $id) {
                                $this->OrdersLabels->add_order_labels($id, $l_id);
                            }
                        }
                        break;
                    }
                    case (preg_match('/^unset_label_([0-9]+)/', $this->Request->post('action'), $a) ? true : false): {
                        $l_id = intval($a[1]);
                        if ($l_id > 0) {
                            foreach ($ids as $id) {
                                $this->OrdersLabels->delete_order_labels($id, $l_id);
                            }
                        }
                        break;
                    }
                }
            }
        }

        if (empty($keyword)) {

            // если status не задан, ставим 0
            if (!$status = $this->Request->get('status', 'integer')) {
                $status = 0;
            }

            $filter['status'] = $status;
            $this->Design->assign('status', $status);
        }

        $filter['page'] = max(1, $this->Request->get('page', 'integer'));
        $filter['limit'] = $this->Settings->products_num_admin;

        // Ограничиваем просмотр кол-во страниц
        // для выполненых(2) отмененых(3) и поиска(keyword)
        if (((isset($status) && ($status == 3 || $status == 2)) || !empty($keyword)) && !$this->access('orders_view_all')) {
            $filter['page'] = 1;
            $this->Design->assign('pagination_hide', true);
        }

        $orders_count = $this->Orders->getOrdersCount($filter);

        // Показать все страницы сразу
        if ($this->Request->get('page') == 'all') {
            $filter['limit'] = $orders_count;
        }

        // Выбираем все заказы
        $orders = $this->Orders->getOrders($filter, false, array('delivery_method', 'payment_method'));

        // Выбираем общую сумму заказов
        $orders_price = $this->Orders->getOrdersPrice($filter);

        // Метки заказов
        foreach ($this->OrdersLabels->get_order_labels(array_keys($orders)) as $ol) {
            $orders[$ol->order_id]->labels[] = $ol;
        }

        // Товары и их фото
        foreach ($this->Orders->getPurchases(array('order_id' => array_keys($orders)), array("image")) as $op) {
            $orders[$op->order_id]->purchases[] = $op;
        }


        if (!empty($orders_count)) {
            $this->Design->assign('pages_count', ceil($orders_count / $filter['limit']));
        }

        $this->Design->assign('current_page', $filter['page']);
        $this->Design->assign('page_limit', $filter['limit']);
        $this->Design->assign('orders_count', $orders_count);
        $this->Design->assign('orders_price', $orders_price);
        $this->Design->assign('orders', $orders);

        // Метки заказов
        $labels = $this->OrdersLabels->get_labels();
        $this->Design->assign('labels', $labels);

        return $this->Design->fetch('orders/orders.tpl');
    }
}
