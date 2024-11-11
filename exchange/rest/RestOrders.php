<?php

/**
 * GoodGin CMS
 *
 * @copyright	2012
 * @author Andi Huga
 *
 */

require_once(__DIR__ . '/Rest.php');

class RestOrders extends Rest
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->Users->access('orders')) {
            header('HTTP/1.1 401 Unauthorized');
            exit();
        }
    }

    public function get()
    {
        $items = array();
        $filter = array();

        // id
        $filter['id'] = $this->Request->get('id');
        // Сортировка
        $filter['status'] = $this->Request->get('status');
        // Страница
        $filter['modified_since'] = $this->Request->get('modified_since');
        // Страница
        $filter['page'] = $this->Request->get('page');
        // Количество элементов на странице
        $filter['limit'] = $this->Request->get('limit');

        // Какие поля отдавать
        if ($fields = $this->Request->get('fields')) {
            $fields = explode(',', $fields);
        }

        // Выбираем
        foreach ($this->Orders->getOrders($filter) as $item) {
            $items[$item->id] = new stdClass();
            if ($fields) {
                foreach ($fields as $field) {
                    if (isset($item->$field)) {
                        $items[$item->id]->$field = $item->$field;
                    }
                }
            } else {
                $items[$item->id] = $item;
            }
        }
        if (empty($items)) {
            return false;
        }

        // Выбранные id
        $items_ids = array_keys($items);

        // Присоединяемые данные
        if ($join = $this->Request->get('join')) {
            $join = explode(',', $join);
            // Изображения
            if (in_array('purchases', $join)) {
                foreach ($this->Orders->getPurchases(array('order_id' => $items_ids)) as $i) {
                    if (isset($items[$i->order_id])) {
                        $items[$i->order_id]->purchases[] = $i;
                    }
                }
            }
        }
        return array_values($items);
    }
}
