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

class StatsAdmin extends Auth
{
    public function fetch()
    {
        $payment_methods = $this->OrdersPayment->getPaymentMethods();
        $this->Design->assign('payment_methods', $payment_methods);

        $total =  new stdClass();
        $total->products_count = $this->Products->count_products();
        $total->sum_wholesale_price = 0;
        $total->sum_price = 0;
        $total->sum_stock = 0;

        $variants = $this->ProductsVariants->getVariants();
        foreach ($variants as $v) {

            // Пропускаем товары "Под заказ", ∞, отсутствующие
            if (!empty($v->stock) and empty($v->infinity) and empty($v->custom)) {
                $total->sum_stock += $v->stock;
                $total->sum_price += ($v->price * $v->stock);
                $total->sum_wholesale_price += ($v->cost_price * $v->stock);
            }
        }

        $this->Design->assign('total', $total);

        return $this->Design->fetch('stats.tpl');
    }
}
