<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class OrderView extends View
{
    // подключаем плагин Smarty
    public function __construct()
    {
        parent::__construct();
        $this->Design->smarty->registerPlugin("function", "get_payment_module_html", array($this, 'payment_module_plugin'));
        $this->Design->smarty->registerPlugin("function", "get_delivery_module_html", array($this, 'delivery_module_plugin'));
    }

    public function fetch()
    {

        // Закрываем от индексации
        $this->Design->assign('noindex', true);

        $order = new stdClass();

        if ($url = $this->Request->get('url', 'string')) {
            $order = $this->Orders->getOrder((string)$url);
        } elseif (!empty($_SESSION['order_id'])) {
            $order = $this->Orders->getOrder(intval($_SESSION['order_id']));
        }

        $purchases = $this->Orders->getPurchases(array('order_id' => intval($order->id)));
        if (empty($purchases)) {
            $this->Misc->makeRedirect($this->Config->root_url . '/cart', '301');
        }

        // Способ доставки
        $delivery = $this->OrdersDelivery->getDeliveryMethod($order->delivery_id);
        $this->Design->assign('delivery', $delivery);

        // Способ оплаты
        if ($order->payment_method_id) {
            $payment_method = $this->OrdersPayment->getPaymentMethod($order->payment_method_id);
            $this->Design->assign('payment_method', $payment_method);

            // Валюта оплаты
            $payment_currency = $this->Money->getCurrency(intval($payment_method->currency_id));
            $this->Design->assign('payment_currency', $payment_currency);

            // Выбираем настройки способа оплаты
            $payment_settings = $this->OrdersPayment->getPaymentMethodSettings($payment_method->id);
        }

        $order = $this->Orders->getOrder($order->id);

        $subtotal = 0;
        $purchases_count = 0;

        $products_ids = [];
        $variants_ids = [];
        $variants_sku = [];

        foreach ($purchases as $purchase) {
            $products_ids[] = $purchase->product_id;
            $variants_ids[] = $purchase->variant_id;
            $variants_sku[] = $purchase->sku;
        }

        $products = $this->Products->get_products(array('id' => $products_ids));

        $images = $this->Images->getImages($products_ids, 'product');
        foreach ($images as $image) {
            $products[$image->entity_id]->images[] = $image;
        }

        $variants = $this->ProductsVariants->getVariants(array('id' => $variants_ids));
        foreach ($variants as $variant) {
            $products[$variant->product_id]->variants[] = $variant;
        }

        foreach ($purchases as &$purchase) {
            if (!empty($products[$purchase->product_id])) {
                $purchase->product = $products[$purchase->product_id];
            }

            if (!empty($variants[$purchase->variant_id])) {
                $purchase->variant = $variants[$purchase->variant_id];
            }

            // Общая стоимость товаров. Без учета скидок
            $subtotal += $purchase->price * $purchase->amount;
            $purchases_count += $purchase->amount;
        }

        $this->Design->assign('order', $order);
        $this->Design->assign('variants_sku', $variants_sku);
        $this->Design->assign('purchases', $purchases);
        $this->Design->assign('purchases_count', $purchases_count);
        $this->Design->assign('subtotal', $subtotal);


        // Способы доставки
        $deliveries = $this->OrdersDelivery->getDeliveryMethods(array('enabled' => 1));
        $this->Design->assign('deliveries', $deliveries);

        // Варианты оплаты
        $payment_methods = $this->OrdersPayment->getPaymentMethods(array('enabled' => 1, 'enabled_public' => 1));
        $this->Design->assign('payment_methods', $payment_methods);

        // Все валюты
        $this->Design->assign('all_currencies', $this->Money->getCurrencies());

        // Выводим заказ
        if ($this->Request->get('type') == 'print') {
            return $this->Design->fetch("order_print.tpl", $this->Config->root_dir . "templates/agmin/html/orders/");
        } else {
            return $this->Design->fetch('order.tpl');
        }
    }


    /**
     * Форма оплаты
     * Smarty Plugin
     */
    public function payment_module_plugin($params)
    {
        return $this->OrdersPayment->getPaymentModuleHtml($params);
    }


    /**
     * Выводим модуль доставки
     * Smarty Plugin
     */
    public function delivery_module_plugin($params)
    {
        return $this->OrdersDelivery->getDeliveryModuleHtml($params);
    }
}
