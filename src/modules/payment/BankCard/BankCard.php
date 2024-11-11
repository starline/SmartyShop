<?php

use GoodGin\GoodGin;

class BankCard extends GoodGin
{
    public function checkout_form($order_id, $view_type)
    {

        $order = $this->Orders->getOrder((int)$order_id);
        $payment_method = $this->OrdersPayment->getPaymentMethod($order->payment_method_id);
        $payment_settings = $this->OrdersPayment->getPaymentMethodSettings($payment_method->id);
        $amount = $this->Money->priceConvert($order->payment_price, $payment_method->currency_id, false);

        $this->Design->assign('payment_settings', $payment_settings);

        // Проверим сущестование файла
        if (!empty($view_type)) {
            $file_path = $this->Config->payment_dir . $payment_method->module . "/" . $payment_method->module . "_" . "$view_type.tpl";
            if (is_file($file_path)) {
                return $this->Design->fetch($file_path);
            }
        }

        return false;
    }
}
