<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 *
 * Для оператора Delivery
 *
 */

use GoodGin\GoodGin;

class DeliveryAuto extends GoodGin
{
    /**
     * Выводим форму
     */
    public function checkout_form($order_id, $view_type)
    {

        $order = $this->Orders->getOrder((int)$order_id);
        $delivery_method = $this->OrdersDelivery->getDeliveryMethod($order->delivery_id);

        // Проверим сущестование файла
        if (!empty($view_type)) {
            $file_path = $this->Config->delivery_dir . $delivery_method->module . '/' . $delivery_method->module . '_' . $view_type . '.tpl';
            if (is_file($file_path)) {
                return $this->Design->fetch($file_path);
            }
        }

        return false;
    }
}
