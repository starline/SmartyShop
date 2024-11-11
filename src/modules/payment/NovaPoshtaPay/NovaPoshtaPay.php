<?php

use GoodGin\GoodGin;

class NovaPoshtaPay extends GoodGin
{
    public function checkout_form($order_id, $view_type)
    {

        if (!empty($order_id)) {
            $order = $this->Orders->getOrder((int)$order_id);

            $payment_method = $this->OrdersPayment->getPaymentMethod($order->payment_method_id);
            $payment_settings = $this->OrdersPayment->getPaymentMethodSettings($payment_method->id);
            $payment_currency = $this->Money->getCurrency(intval($payment_method->currency_id));

            if (empty($payment_settings->tax)) {
                $payment_settings->tax = 0;
            }

            if (empty($payment_settings->tax_inside)) {
                $payment_settings->tax_inside = 0;
            }

            $tax_amount = $this->Money->priceConvert($order->total_price * $payment_settings->tax / 100, $payment_method->currency_id, false);
            $tax_inside_amount = $this->Money->priceConvert($order->total_price * $payment_settings->tax_inside / 100, $payment_method->currency_id, false);

            if ($tax_amount == 0 and $tax_inside_amount == 0) {
                return false;
            }

            $this->Design->assign('payment_settings', $payment_settings);
            $this->Design->assign('payment_currency', $payment_currency);
            $this->Design->assign('tax_amount', $tax_amount);
            $this->Design->assign('tax_inside_amount', $tax_inside_amount);


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
}
