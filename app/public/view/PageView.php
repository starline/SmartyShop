<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 * Этот класс использует шаблон page.tpl
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class PageView extends View
{
    public function fetch()
    {
        $url = $this->Request->get('page_url', 'string');
        $page = $this->Pages->getPage($url);

        if (empty($page) || !$page->visible) {
            return false;
        }

        // Если страница - SEO
        if ($page->menu_id == 2) {
            $canonical = "/" . $page->url;

            //  Если обычная страница
        } else {
            $canonical = "/info" . "/" . $page->url;
        }

        $this->Design->assign('page', $page);
        $this->Design->assign('meta_title', $page->meta_title);
        $this->Design->assign('meta_description', $page->meta_description);
        $this->Design->assign('canonical', $canonical);

        return $this->Design->fetch('page.tpl');
    }
}
