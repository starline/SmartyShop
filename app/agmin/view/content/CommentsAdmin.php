<?php

if (!defined('secure')) {
    exit('Access denied');
}

class CommentsAdmin extends Auth
{
    public function fetch()
    {


        $filter = array();
        $filter['page'] = max(1, $this->Request->get('page', 'integer'));

        $filter['limit'] = 40;

        // Тип
        $type = $this->Request->get('type', 'string');
        if($type) {
            $filter['type'] = $type;
            $this->Design->assign('type', $type);
        }

        // Поиск
        $keyword = $this->Request->get('keyword', 'string');
        if(!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->Design->assign('keyword', $keyword);
        }


        // Обработка действий
        if($this->Request->method('post')) {

            // Действия с выбранными
            $ids = $this->Request->post('check');
            if(!empty($ids) && is_array($ids)) {
                switch($this->Request->post('action')) {
                    case 'approve':
                        {
                            foreach($ids as $id) {
                                $this->Comments->updateComment($id, array('approved' => 1));
                            }
                            break;
                        }
                    case 'delete':
                        {
                            foreach($ids as $id) {
                                $this->Comments->deleteComment($id);
                            }
                            break;
                        }
                }
            }

        }



        // Отображение
        $comments_count = $this->Comments->getCommentsCount($filter);

        // Показать все страницы сразу
        if($this->Request->get('page') == 'all') {
            $filter['limit'] = $comments_count;
        }
        $comments = $this->Comments->getComments($filter);

        // Выбирает объекты, которые прокомментированы:
        $products_ids = array();
        $posts_ids = array();
        foreach($comments as $comment) {
            if($comment->type == 'product') {
                $products_ids[] = $comment->entity_id;
            }
            if($comment->type == 'blog') {
                $posts_ids[] = $comment->entity_id;
            }
        }

        $products = $this->Products->get_products(array('id' => $products_ids));

        $posts = array();
        foreach($this->Blog->getPosts(array('id' => $posts_ids)) as $p) {
            $posts[$p->id] = $p;
        }

        foreach($comments as &$comment) {
            if($comment->type == 'product' && isset($products[$comment->entity_id])) {
                $comment->product = $products[$comment->entity_id];
            }
            if($comment->type == 'blog' && isset($posts[$comment->entity_id])) {
                $comment->post = $posts[$comment->entity_id];
            }
        }


        $this->Design->assign('pages_count', ceil($comments_count / $filter['limit']));
        $this->Design->assign('current_page', $filter['page']);

        $this->Design->assign('comments', $comments);
        $this->Design->assign('comments_count', $comments_count);

        // Меню
        $menus = $this->Pages->get_menus();
        $this->Design->assign('menus', $menus);

        return $this->Design->fetch('content/comments.tpl');
    }
}
