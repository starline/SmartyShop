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

class PagesAdmin extends Auth
{
    public function fetch()
    {

        // Меню
        $menus = $this->Pages->get_menus();
        $this->Design->assign('menus', $menus);

        // Текущее меню
        $menu_id = $this->Request->get('menu_id', 'integer');
        if (!$menu_id || !$menu = $this->Pages->get_menu($menu_id)) {
            $menu = reset($menus);
        }
        $this->Design->assign('menu', $menu);

        // Обработка действий
        if ($this->Request->method('post')) {

            // Сортировка
            $positions = $this->Request->post('positions');
            $positions_ids = array_keys($positions);
            sort($positions);
            foreach ($positions as $i => $position) {
                $this->Pages->update_page($positions_ids[$i], array('position' => $position));
            }


            // Действия с выбранными
            $ids = $this->Request->post('check');
            if (is_array($ids)) {
                switch ($this->Request->post('action')) {
                    case 'disable': {
                        $this->Pages->update_page($ids, array('visible' => 0));
                        break;
                    }
                    case 'enable': {
                        $this->Pages->update_page($ids, array('visible' => 1));
                        break;
                    }
                    case 'delete': {
                        foreach ($ids as $id) {
                            $this->Pages->delete_page($id);
                        }
                        break;
                    }
                }
            }

        }


        // Отображение
        $pages = $this->Pages->getPages(array('menu_id' => $menu->id));
        $this->Design->assign('pages', $pages);

        return $this->Design->fetch('content/pages.tpl');
    }
}
