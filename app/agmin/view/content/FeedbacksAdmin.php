<?php

if (!defined('secure')) {
    exit('Access denied');
}

class FeedbacksAdmin extends Auth
{
    public function fetch()
    {

        // Поиск
        $keyword = $this->Request->get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->Design->assign('keyword', $keyword);
        }


        // Обработка действий
        if ($this->Request->method('post')) {

            // Действия с выбранными
            $ids = $this->Request->post('check');
            if (!empty($ids)) {
                switch ($this->Request->post('action')) {
                    case 'delete':
                        {
                            foreach ($ids as $id) {
                                $this->Feedbacks->deleteFeedback($id);
                            }
                            break;
                        }
                }
            }
        }

        // Отображение
        $filter = array();
        $filter['page'] = max(1, $this->Request->get('page', 'integer'));
        $filter['limit'] = 40;

        // Поиск
        $keyword = $this->Request->get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->Design->assign('keyword', $keyword);
        }

        $feedbacks_count = $this->Feedbacks->countFeedbacks($filter);

        // Показать все страницы сразу
        if ($this->Request->get('page') == 'all' and $feedbacks_count) {
            $filter['limit'] = $feedbacks_count;
        }

        $feedbacks = $this->Feedbacks->getFeedbacks($filter, true);

        $this->Design->assign('pages_count', ceil($feedbacks_count / $filter['limit']));
        $this->Design->assign('current_page', $filter['page']);

        $this->Design->assign('feedbacks', $feedbacks);
        $this->Design->assign('feedbacks_count', $feedbacks_count);

        // Меню
        $menus = $this->Pages->get_menus();
        $this->Design->assign('menus', $menus);

        return $this->Design->fetch('content/feedbacks.tpl');
    }
}
