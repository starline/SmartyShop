<?php

if (!defined('secure')) {
    exit('Access denied');
}

class BlogAdmin extends Auth
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
                        $this->Blog->updatePost($ids, array('visible' => 0));
                        break;
                    }
                    case 'enable': {
                        $this->Blog->updatePost($ids, array('visible' => 1));
                        break;
                    }
                    case 'delete': {
                        foreach ($ids as $id) {
                            $this->Blog->deletePost($id);
                        }
                        break;
                    }
                }
            }
        }

        $filter = array();
        $filter['page'] = max(1, $this->Request->get('page', 'integer'));
        $filter['limit'] = 20;

        // Поиск
        $keyword = $this->Request->get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->Design->assign('keyword', $keyword);
        }

        $posts_count = $this->Blog->countPosts($filter);

        // Показать все страницы сразу
        if ($this->Request->get('page') == 'all') {
            $filter['limit'] = $posts_count;
        }

        $posts = $this->Blog->getPosts($filter);
        $this->Design->assign('posts_count', $posts_count);

        $this->Design->assign('pages_count', ceil($posts_count / $filter['limit']));
        $this->Design->assign('current_page', $filter['page']);

        $this->Design->assign('posts', $posts);

        // Меню
        $menus = $this->Pages->get_menus();
        $this->Design->assign('menus', $menus);

        return $this->Design->fetch('content/blog.tpl');
    }
}
