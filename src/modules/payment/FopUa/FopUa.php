<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 *
 * Использует библиотку intl (MessageFormatter)
 * Установка на Linus: sudo apt-get install php7.4-intl
 *
 * Использует библиотку TCPDF для преобразования HTML в PDF
 * include 2D barcode class (search for installation path)
 *
 */

use GoodGin\GoodGin;

class FopUa extends GoodGin
{
    public function checkout_form($order_id, $view_type)
    {

        if (!empty($order_id)) {

            $order = $this->Orders->getOrder((int)$order_id);
            $payment_method = $this->OrdersPayment->getPaymentMethod($order->payment_method_id);
            $payment_settings = $this->OrdersPayment->getPaymentMethodSettings($payment_method->id);
            $payment_currency = $this->Money->getCurrency(intval($payment_method->currency_id));

            $final_price = $order->total_price;

            // Учитываем стоимость доставки
            if ($order->separate_delivery == 0 and !empty($order->delivery_price)) {
                $final_price += $order->delivery_price;
            }

            $tax_amount = $this->Money->priceConvert(($final_price / ((100 - $payment_settings->tax) / 100)) - $final_price, $payment_method->currency_id, false);

            $this->Design->assign('tax_amount', $tax_amount);
            $this->Design->assign('payment_settings', $payment_settings);
            $this->Design->assign('payment_currency', $payment_currency);

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


    public function callback($order_url = null, $form_type = null)
    {

        // Отображаем документ по ссылке на заказ
        if (empty($order_url)) {
            $order_url = $this->Request->get('order_url', 'string');
        }

        if (empty($form_type)) {
            $form_type = $this->Request->get('form_type', 'string');
            if (empty($form_type)) {
                $form_type = "invoice";
            }
        }

        // Для безопасности, предоставляем доступ к квитаанциям только по order_url
        if (!empty($order_url) and !empty($form_type)) {
            $order = $this->Orders->getOrder($order_url);

            if (empty($order)) {
                display_error('Оплачиваемый заказ не найден');
            }

            // Форматируем дату создания счета
            if (!empty($order->settings->payment_checkdate)) {
                $order->date = $order->settings->payment_checkdate;
            }

            // Set buyer name
            if (!empty($order->address)) {
                $order->name .= ', '. $order->address;
            }
            if (!empty($order->settings->payment_name)) {
                $order->name = $order->settings->payment_name;
            }

            $payment_method = $this->OrdersPayment->getPaymentMethod($order->payment_method_id);
            $payment_settings = $this->OrdersPayment->getPaymentMethodSettings($payment_method->id);
            $delivery_method = $this->OrdersDelivery->getDeliveryMethod($order->delivery_id);


            // Выбираем товары заказа
            $purchases = array();
            if (!empty($order) and !empty($purchases = $this->Orders->getPurchases(array('order_id' => $order->id)))) {

                // Покупки
                $products_ids = array();
                $variants_ids = array();

                foreach ($purchases as $purchase) {
                    $products_ids[] = $purchase->product_id;
                    $variants_ids[] = $purchase->variant_id;
                }

                $products = $this->Products->get_products(array('id' => $products_ids));
                $variants = $this->ProductsVariants->getVariants(array('product_id' => $products_ids));
                foreach ($variants as $variant) {
                    if (!empty($products[$variant->product_id])) {
                        $products[$variant->product_id]->variants[] = $variant;
                    }
                }

                foreach ($purchases as &$purchase) {
                    if (!empty($products[$purchase->product_id])) {
                        $purchase->product = $products[$purchase->product_id];
                    }

                    if (!empty($variants[$purchase->variant_id])) {
                        $purchase->variant = $variants[$purchase->variant_id];
                    }

                    // Вычисляем скидку %
                    $purchase->price = $purchase->price - ($purchase->price * ($order->discount / 100));

                    // Добавляем наценку
                    $purchase->price = number_format(($purchase->price / ((100 - $payment_settings->tax) / 100)), 2, ".", "");
                }
            }

            // Если есть оплата за доставку
            if (empty($order->separate_delivery) and $order->delivery_price > 0) {
                $product = new stdClass();
                $product->product_name = 'Пакувальний матеріал';
                $product->variant_name = ''; // . $delivery_method->name;
                $product->sku = 'sku000';
                $product->amount = 1;
                $product->price = $order->delivery_price / ((100 - $payment_settings->tax) / 100);
                $purchases[] = $product;
            }

            $payment_price_converted = $this->Money->priceConvert($order->payment_price, $payment_method->currency_id, false);
            $payment_price_converted = explode(".", $payment_price_converted);

            $order->payment_price_spellout_int = (new MessageFormatter('uk_UA', '{n, spellout}'))->format(['n' => $payment_price_converted[0]]);
            if (!empty($payment_price_converted[1])) {
                $order->payment_price_spellout_dec  = (new MessageFormatter('uk_UA', '{n, spellout}'))->format(['n' => $payment_price_converted[1]]);
            }

            // Date spellout
            $order->date_spellput = (new IntlDateFormatter(
                'uk_UA',
                IntlDateFormatter::FULL,
                IntlDateFormatter::FULL,
                'Europe/Kiev',
                IntlDateFormatter::GREGORIAN,
                "d MMMM YYYY"
            ))->format(strtotime($order->date));

            // get currency info
            $payment_method->currency = $this->Money->getCurrency($payment_method->currency_id);

            $this->Design->assign('payment_method', $payment_method);
            $this->Design->assign('payment_settings', $payment_settings);
            $this->Design->assign('delivery_method', $delivery_method);
            $this->Design->assign('order', $order);
            $this->Design->assign('form_type', $form_type);
            $this->Design->assign('purchases', $purchases);


            // Create a PDF object
            $pdf = new TCPDF('');

            $pdf->setPDFVersion('1.6');
            $pdf->SetFont('dejavusanscondensed', '', 8);

            // Set document properties
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->setPageOrientation('P');

            // Set font for the entire document
            $pdf->SetTextColor(0, 0, 0);

            // Set up a page
            $pdf->AddPage();
            $pdf->SetDisplayMode('real');
            $pdf->SetFontSize(9);


            $file_path = $this->Config->payment_dir . $payment_method->module . '/' . $payment_method->module . '_invoice.tpl';
            $html =  $this->Design->fetch($file_path);
            $pdf->writeHTML($html, true, false, true, false, '');


            if ($form_type == "invoice") {
                $file_name = "Order N$order->id " . date("d.m.Y", strtotime($order->date)) . '.pdf';
            } elseif ($form_type == "packing_list") {
                $file_name = "Packing List N$order->id " . date("d.m.Y", strtotime($order->date)) . '.pdf';
            }

            // Output the document
            $pdf->Output($file_name, 'I');

        } else {
            display_error('Не заданы параметры');
        }
    }


    public function display_error($msg)
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
        //mail("test@test", "interkassa: $msg", $msg);
        die($msg);
    }
}
