<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class PursesAdmin extends Auth
{
    public function fetch()
    {

        // Обработка действий
        if ($this->Request->method('post')) {

            // Сортировка
            $positions = $this->Request->post('positions');
            $positions_ids = array_keys($positions);
            sort($positions);
            foreach ($positions as $i => $position) {
                $this->Finance->update_purse($positions_ids[$i], array('position' => $position));
            }

            // Действия с выбранными
            $ids = $this->Request->post('check');

            if (is_array($ids)) {
                switch ($this->Request->post('action')) {
                    case 'disable': {
                        $this->Finance->update_purse($ids, array('enabled' => 0));
                        break;
                    }
                    case 'enable': {
                        $this->Finance->update_purse($ids, array('enabled' => 1));
                        break;
                    }
                    case 'delete': {
                        foreach ($ids as $id) {
                            $this->Finance->delete_purse($id);
                        }
                        break;
                    }
                }
            }
        }

        $purses = $this->Finance->getPurses();
        $purses_count  = count($purses);

        // Общий баланс, грн (id=2)
        $total_amount = array();
        $currencies = $this->Money->getCurrencies(array('enabled' => 1));
        foreach ($currencies as $c) {
            $total_amount[$c->id] = $c;
            $total_amount[$c->id]->amount = $this->Finance->get_total_amount($c->id);
        }

        $this->Design->assign('total_amount', $total_amount);
        $this->Design->assign('purses_count', $purses_count);
        $this->Design->assign('purses', $purses);

        //  Отображение
        return $this->Design->fetch('finance/purses.tpl');
    }
}
