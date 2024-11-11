<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 * @version 1.4
 *
 * Класс для работы со страницами
 *
 */

namespace GoodGin;

class Pages extends GoodGin
{
    /**
     * Функция возвращает страницу по ее id или url (в зависимости от типа)
     * @param Integer|String $id = id или url страницы
     * @param Array $filter
     */
    public function getPage($id)
    {

        $where_id = '';
        if (gettype($id) == 'string') {
            $where_id = $this->Database->placehold(' AND p.url=?', $id);
        } else {
            $where_id = $this->Database->placehold(' AND p.id=?', intval($id));
        }

        $where_visible = '';
        if (isset($filter['visible'])) {
            $where_visible = $this->Database->placehold(' AND p.visible=?', intval($filter['visible']));
        }

        $where_menu_id = '';
        if (isset($filter['menu_id'])) {
            $where_menu_id = $this->Database->placehold(' AND p.menu_id=?', intval($filter['menu_id']));
        }

        $query = $this->Database->placehold(
            "SELECT
				p.id, 
                p.url, 
                p.h1, 
                p.name, 
                p.meta_title, 
                p.meta_description, 
                p.body, 
                p.menu_id, 
                p.position, 
                p.visible
		    FROM 
		        __content_pages p
		    WHERE
                1 
                $where_visible
                $where_id
                $where_menu_id
		    LIMIT 
                1"
        );

        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Функция возвращает массив страниц, удовлетворяющих фильтру
     * @param $filter
     */
    public function getPages($filter = array())
    {

        $menu_filter = '';
        $visible_filter = '';
        $pages = array();

        if (isset($filter['menu_id'])) {
            $menu_filter = $this->Database->placehold('AND menu_id in (?@)', (array)$filter['menu_id']);
        }

        if (isset($filter['visible'])) {
            $visible_filter = $this->Database->placehold('AND visible=?', intval($filter['visible']));
        }

        $query = $this->Database->placehold(
            "SELECT 
                p.id,
                p.url, 
                p.h1, 
                p.name, 
                p.meta_title, 
                p.meta_description, 
                p.body, 
                p.menu_id, 
                p.position, 
                p.visible 
		    FROM 
                __content_pages p
            WHERE 
                1 
                $menu_filter 
                $visible_filter 
            ORDER BY 
                position"
        );

        $this->Database->query($query);

        foreach ($this->Database->results() as $page) {
            $pages[$page->id] = $page;
        }

        return $pages;
    }


    /**
     * Создание страницы
     * @param $page
     */
    public function add_page($page)
    {
        $page = $this->Misc->cleanEntityId($page);

        $query = $this->Database->placehold('INSERT INTO __content_pages SET ?%', $page);
        if (!$this->Database->query($query)) {
            return false;
        }

        $id = $this->Database->getInsertId();
        $this->Database->query("UPDATE __content_pages SET position=id WHERE id=?", $id);
        return $id;
    }


    /**
     * Обновить страницу
     * @param $id
     * @param $page
     */
    public function update_page($id, $page)
    {
        $query = $this->Database->placehold('UPDATE __content_pages SET ?% WHERE id in (?@)', $page, (array)$id);
        if (!$this->Database->query($query)) {
            return false;
        }
        return $id;
    }



    /**
     * Удалить страницу
     * @param $id
     */
    public function delete_page($id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("DELETE FROM __content_pages WHERE id=? LIMIT 1", intval($id));
        return $this->Database->query($query);
    }


    /**
     * Функция возвращает массив меню
     */
    public function get_menus()
    {
        $menus = array();
        $query = "SELECT * FROM __content_menu ORDER BY position";
        $this->Database->query($query);
        foreach ($this->Database->results() as $menu) {
            $menus[$menu->id] = $menu;
        }
        return $menus;
    }


    /**
     * Функция возвращает меню по id
     * @param $menu_id
     */
    public function get_menu($menu_id)
    {
        $query = $this->Database->placehold("SELECT * FROM __content_menu WHERE id=? LIMIT 1", intval($menu_id));
        $this->Database->query($query);
        return $this->Database->result();
    }
}
