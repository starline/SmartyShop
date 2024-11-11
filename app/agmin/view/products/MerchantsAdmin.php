<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 *
 * MerchantsAdmin
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class MerchantsAdmin extends Auth
{
    public function fetch()
    {
        // Обработка действий
        if ($this->Request->method('post')) {

            // Действия с выбранными
            $ids = $this->Request->post('check');

            if (is_array($ids)) {
                switch ($this->Request->post('action')) {
                    case 'disable':
                        {
                            $this->ProductsMerchants->updatePriceLists($ids, array('enabled' => 0));
                            break;
                        }
                    case 'enable':
                        {
                            $this->ProductsMerchants->updatePriceLists($ids, array('enabled' => 1));
                            break;
                        }
                    case 'delete':
                        {
                            foreach ($ids as $id) {
                                $this->ProductsMerchants->deletePriceList($id);
                            }
                            break;
                        }
                }
            }

            // Сортировка
            if (!empty($positions = $this->Request->post('positions'))) {
                $positions_ids = array_keys($positions);
                sort($positions);
                foreach ($positions as $i => $position) {
                    $this->ProductsMerchants->updatePriceList($positions_ids[$i], array('sort' => $position));
                }
            }
        }

        $merchants = $this->ProductsMerchants->getPriceLists();
        $this->Design->assign('merchants', $merchants);

        //  Отображение
        return $this->body = $this->Design->fetch('products/merchants.tpl');
    }
}
