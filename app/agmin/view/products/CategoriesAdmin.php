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

class CategoriesAdmin extends Auth
{
    public function fetch()
    {
        if ($this->Request->method('post')) {

            // Действия с выбранными
            $ids = $this->Request->post('check');

            if (is_array($ids)) {
                switch ($this->Request->post('action')) {
                    case 'disable': {
                        foreach ($ids as $id) {
                            $this->ProductsCategories->update_category($id, array('visible' => 0));
                            $this->Design->assign('message_success', 'update');
                        }
                        break;
                    } case 'enable': {
                        foreach ($ids as $id) {
                            $this->ProductsCategories->update_category($id, array('visible' => 1));
                            $this->Design->assign('message_success', 'update');
                        }
                        break;
                    } case 'delete': {
                        $this->ProductsCategories->deleteCategory($ids);
                        $this->Design->assign('message_success', 'delete');
                        break;
                    }
                }
            }

            // Сортировка
            $positions = $this->Request->post('positions');
            $positions_ids = array_keys($positions);
            sort($positions);
            foreach ($positions as $i => $position) {
                $this->ProductsCategories->update_category($positions_ids[$i], array('position' => $position));
            }
        }

        $categories = $this->ProductsCategories->getCategoriesTree();
        $this->Design->assign('categories', $categories);

        return $this->Design->fetch('products/categories.tpl');
    }
}
