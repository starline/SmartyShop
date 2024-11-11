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

class FinanceCategoriesAdmin extends Auth
{
    public function fetch()
    {

        // Обработка действий
        if ($this->Request->method('post')) {

            // Действия с выбранными
            $ids = $this->Request->post('check');

            if (is_array($ids)) {
                switch ($this->Request->post('action')) {
                    case 'disable': {
                        $this->Finance->update_category($ids, array('enabled' => 0));
                        break;
                    }
                    case 'enable': {
                        $this->Finance->update_category($ids, array('enabled' => 1));
                        break;
                    }
                    case 'delete': {
                        foreach ($ids as $id) {
                            $this->Finance->deleteCategory($id);
                        }
                        break;
                    }
                }
            }

            // Сортировка
            $positions = $this->Request->post('positions');
            $positions_ids = array_keys($positions);
            sort($positions);
            foreach ($positions as $i => $position) {
                $this->Finance->update_category($positions_ids[$i], array('position' => $position));
            }
        }

        $categories = $this->Finance->get_categories();
        $categories_count  = count($categories);

        $this->Design->assign('categories_count', $categories_count);
        $this->Design->assign('categories', $categories);

        //  Отображение
        return $this->body = $this->Design->fetch('finance/categories.tpl');
    }
}
