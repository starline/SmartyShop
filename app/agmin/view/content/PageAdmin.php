<?php

if (!defined('secure')) {
    exit('Access denied');
}

class PageAdmin extends Auth
{
    public function fetch()
    {

        $page = new stdClass();

        if ($this->Request->method('POST')) {
            $page->id = $this->Request->post('id', 'integer');
            $page->name = $this->Request->post('name');
            $page->h1 = $this->Request->post('h1');
            $page->url = $this->Request->post('url');
            $page->meta_title = $this->Request->post('meta_title');
            $page->meta_description = $this->Request->post('meta_description');
            $page->body = $this->Request->post('body');
            $page->menu_id = $this->Request->post('menu_id', 'integer');
            $page->visible = $this->Request->post('visible', 'boolean');

            ## Не допустить одинаковые URL разделов.
            if (($p = $this->Pages->getPage($page->url)) && $p->id != $page->id) {
                $this->Design->assign('message_error', 'url_exists');
            } else {
                if (empty($page->id)) {
                    $page->id = $this->Pages->add_page($page);
                    $page = $this->Pages->getPage($page->id);
                    $this->Design->assign('message_success', 'added');
                } else {
                    $this->Pages->update_page($page->id, $page);
                    $page = $this->Pages->getPage($page->id);
                    $this->Design->assign('message_success', 'updated');
                }
            }
        } else {
            $id = $this->Request->get('id', 'integer');
            if (!empty($id)) {
                $page = $this->Pages->getPage(intval($id));
            } else {
                $page->menu_id = $this->Request->get('menu_id');
                $page->visible = 1;
            }
        }

        $this->Design->assign('page', $page);

        $menus = $this->Pages->get_menus();
        $this->Design->assign('menus', $menus);

        // Текущее меню
        if (isset($page->menu_id)) {
            $menu_id = $page->menu_id;
        }

        if (empty($menu_id) || !$menu = $this->Pages->get_menu($menu_id)) {
            $menu = reset($menus);
        }

        $this->Design->assign('menu', $menu);

        return $this->Design->fetch('content/page.tpl');
    }

}
