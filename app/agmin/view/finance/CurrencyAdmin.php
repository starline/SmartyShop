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

class CurrencyAdmin extends Auth
{
    public function fetch()
    {

        // Обработка действий
        if ($this->Request->method('post')) {

            foreach ($this->Request->post('currency') as $n => $va) {
                foreach ($va as $i => $v) {
                    if (empty($currencies[$i])) {
                        $currencies[$i] = new stdClass();
                    }
                    $currencies[$i]->$n = $v;
                }
            }

            $currencies_ids = array();
            foreach ($currencies as $currency) {
                if ($currency->id) {
                    $this->Money->updateCurrency($currency->id, $currency);
                } else {
                    $currency->id = $this->Money->addCurrency($currency);
                }
                $currencies_ids[] = $currency->id;
            }

            // Удалить непереданные валюты
            $query = $this->Database->placehold('DELETE FROM __finance_currencies WHERE id NOT IN(?@)', $currencies_ids);
            $this->Database->query($query);

            // Пересчитать курсы
            $old_currency = $this->Money->getCurrency();
            $new_currency = reset($currencies);
            if ($old_currency->id != $new_currency->id) {
                $coef = $new_currency->rate_from / $new_currency->rate_to;

                if ($this->Request->post('recalculate') == 1) {

                    // Пересчитываем цены товаров
                    $this->Database->query("UPDATE __products_variants SET price=price*?", $coef);
                    $this->Database->query("UPDATE __products_variants SET cost_price=cost_price*?", $coef);
                    $this->Database->query("UPDATE __products_variants SET old_price=old_price*?", $coef);

                    // Заказы
                    $this->Database->query("UPDATE __orders SET delivery_price=delivery_price*?", $coef);
                    $this->Database->query("UPDATE __orders SET total_price=total_price*?", $coef);
                    $this->Database->query("UPDATE __orders SET profit_price=profit_price*?", $coef);
                    $this->Database->query("UPDATE __orders SET coupon_discount=coupon_discount*?", $coef);
                    $this->Database->query("UPDATE __orders SET interest_price=interest_price*?", $coef);
                    $this->Database->query("UPDATE __orders SET payment_price=payment_price*?", $coef);
                    $this->Database->query("UPDATE __orders SET delivery_price=delivery_price*?", $coef);

                    // Товары заказа
                    $this->Database->query("UPDATE __orders_purchases SET price=price*?", $coef);
                    $this->Database->query("UPDATE __orders_purchases SET cost_price=cost_price*?", $coef);


                    $this->Database->query("UPDATE __users_coupons SET value=value*? WHERE type='absolute'", $coef);
                    $this->Database->query("UPDATE __users_coupons SET min_order_price=min_order_price*?", $coef);

                    $this->Database->query("UPDATE __orders_delivery SET price=price*?, free_from=free_from*?", $coef, $coef);


                    // Склад
                }

                $this->Database->query("UPDATE __finance_currencies SET rate_from=1.0*rate_from*$new_currency->rate_to/$old_currency->rate_to");
                $this->Database->query("UPDATE __finance_currencies SET rate_to=1.0*rate_to*$new_currency->rate_from/$old_currency->rate_from");
                $this->Database->query("UPDATE __finance_currencies SET rate_to = rate_from WHERE id=?", $new_currency->id);
                $this->Database->query("UPDATE __finance_currencies SET rate_to = 1, rate_from = 1 WHERE (rate_to=0 OR rate_from=0) AND id=?", $new_currency->id);
            }

            // Отсортировать валюты
            asort($currencies_ids);
            $i = 0;
            foreach ($currencies_ids as $currency_id) {
                if ($i == 0) {
                    $this->Money->updateCurrency($currencies_ids[$i], array('position' => $currency_id, 'rate_to' => 1, 'rate_from' => 1));
                } else {
                    $this->Money->updateCurrency($currencies_ids[$i], array('position' => $currency_id));
                }
                $i++;
            }

            // Действия с выбранными
            $action = $this->Request->post('action');
            $id = $this->Request->post('action_id');

            if (!empty($action) && !empty($id)) {
                switch ($action) {
                    case 'disable': {
                        $this->Money->updateCurrency($id, array('enabled' => 0));
                        break;
                    }
                    case 'enable': {
                        $this->Money->updateCurrency($id, array('enabled' => 1));
                        break;
                    }
                    case 'show_cents': {
                        $this->Money->updateCurrency($id, array('cents' => 1));
                        break;
                    }
                    case 'hide_cents': {
                        $this->Money->updateCurrency($id, array('cents' => 0));
                        break;
                    }
                    case 'delete': {
                        $this->Money->deleteCurrency($id);
                        break;
                    }
                }
            }

            $this->Design->assign('message_success', "update");
        }


        // Отображение
        $currencies = $this->Money->getCurrencies();
        $this->Design->assign('currencies', $currencies);

        $currency = $this->Money->getCurrency();
        $this->Design->assign('currency', $currency);

        return $this->Design->fetch('finance/currency.tpl');
    }
}
