<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author 	Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class FinancePaymentsAdmin extends Auth
{
    public function fetch()
    {

        $payments_types =  (object) array(
            (object) array(
                'id' => 0,
                'name' => 'Расход',
                'type' => 'minus'
            ),
            (object) array(
                'id' => 1,
                "name" => "Приход",
                'type' => 'plus'
            ),
            (object) array(
                'id' => 2,
                "name" => "Перевод",
                'type' => 'transfer'
            )
        );

        $filter = array();
        $filter['page'] = max(1, $this->Request->get('page', 'integer'));
        $filter['limit'] = $this->Settings->products_num_admin;

        // Поиск
        $keyword = $this->Request->get('keyword');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->Design->assign('keyword', $keyword);
        }

        // Кошелек
        if ($purse_id = $this->Request->get('purse_id', 'integer')) {
            $filter['purse_id'] = $purse_id;
        }

        // Категория
        if ($category_id = $this->Request->get('category_id', 'integer')) {
            $filter['category_id'] = $category_id;
        }

        // Payments type
        if ($payments_type = $this->Request->get('payments_type', 'string')) {
            $filter['payments_type'] = $payments_type;
        }

        $payments_count = $this->Finance->count_payments($filter);

        // Показать все страницы сразу
        if ($this->Request->get('page') == 'all') {
            $filter['limit'] = $payments_count;
        }

        $payments = $this->Finance->get_payments($filter);

        foreach ($payments as $p) {

            // Выбираем фотоотчеты
            $images = $this->Images->getImages($p->id, 'payment');
            $payments[$p->id]->images = $images;

            // Выбираем контрагента
            $contractor = $this->Finance->get_contractor(intval($p->id));
            if (isset($contractor->entity_name)) {
                $contractor->view_name = $this->Misc->getViewAdmin($contractor->entity_name);
            }
            $payments[$p->id]->contractor = $contractor;
        }

        // Общий баланс
        $total_amount = array();
        $total_dollars = 0;
        $currencies = $this->Money->getCurrencies(); // Все валюты
        foreach ($currencies as $c) {
            $total_amount[$c->id] = $c;
            $total_amount[$c->id]->amount = $this->Finance->get_total_amount($c->id);

            // Подсчитываем общий баланс в USD
            $total_dollars += $this->Money->priceConvert($total_amount[$c->id]->amount, "USD", false, $c->id);
        }

        // Выбираем категории
        $categories = $this->Finance->get_categories($payments_type);
        $categories_income = array();
        $categories_expense = array();
        foreach($categories as $cat) {
            if ($cat->type == 1) {
                $categories_income[] = $cat;
            } else {
                $categories_expense[] = $cat;
            }
        }

        // Выбираем все кошельки
        $purses = $this->Finance->getPurses(array('enabled' => 1));

        $this->Design->assign('pages_count', ceil($payments_count / $filter['limit']));
        $this->Design->assign('current_page', $filter['page']);
        $this->Design->assign('payments_types', $payments_types);
        $this->Design->assign('payments_type', $payments_type);
        $this->Design->assign('payments', $payments);

        $this->Design->assign('categories', $categories);
        $this->Design->assign('categories_income', $categories_income);
        $this->Design->assign('categories_expense', $categories_expense);
        $this->Design->assign('category_id', $category_id);

        $this->Design->assign('payments_count', $payments_count);
        $this->Design->assign('total_amount', $total_amount);
        $this->Design->assign('total_dollars', $total_dollars);
        $this->Design->assign('purses', $purses);
        $this->Design->assign('purse_id', $purse_id);

        // Отображение
        return $this->Design->fetch('finance/payments.tpl');
    }
}
