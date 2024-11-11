<?php

/**
 * GoodGin - The Best of gins
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class UsersAdmin extends Auth
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
                            $this->Users->updateUser($id, array('enabled' => 0));
                        }
                        break;
                    }
                    case 'enable': {
                        foreach ($ids as $id) {
                            $this->Users->updateUser($id, array('enabled' => 1));
                        }
                        break;
                    }
                    case 'delete': {
                        foreach ($ids as $id) {
                            $this->Users->deleteUser($id);
                        }
                        break;
                    }
                }
            }
        }

        $groups = null;
        foreach ($this->UsersGroups->getGroups() as $g) {
            $groups[$g->id] = $g;
        }


        $group = null;
        $filter = array();


        $filter['page'] =  max(1, $this->Request->get('page', 'integer'));
        $filter['limit'] = $this->Settings->products_num_admin;

        // Ограничеваем просмотр списка только 1й страницей
        if (!$this->access('users_edit')) {
            $filter['page'] = 1;
            $this->Design->assign('pagination_hide', true);
        }

        $group_id = $this->Request->get('group_id', 'integer');
        if ($group_id) {
            $group = $this->UsersGroups->getGroup($group_id);
            $filter['group_id'] = $group->id;
        }

        // Показать сотрудников
        $manager = $this->Request->get('manager');
        if (!empty($manager) and $this->access('users_manager')) {
            $filter['manager'] = $manager;
            $this->Design->assign('manager', $manager);
        }

        // Поиск
        $keyword = $this->Request->get('keyword');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->Design->assign('keyword', $keyword);
        }

        // Сортировка пользователей, сохраняем в сессии, чтобы текущая сортировка не сбрасывалась
        if ($sort = $this->Request->get('sort', 'string')) {
            $_SESSION['users_admin_sort'] = $sort;
        }

        if (!empty($_SESSION['users_admin_sort'])) {
            $filter['sort'] = $_SESSION['users_admin_sort'];
        } else {
            $filter['sort'] = 'name';
        }
        $this->Design->assign('sort', $filter['sort']);

        $users_count = $this->Users->countUsers($filter);

        // Показать все страницы сразу
        if ($this->Request->get('page') == 'all') {
            $filter['limit'] = $users_count;
        }

        $users = $this->Users->getUsers($filter);

        $this->Design->assign('pages_count', ceil($users_count / $filter['limit']));
        $this->Design->assign('current_page', $filter['page']);
        $this->Design->assign('page_limit', $filter['limit']);
        $this->Design->assign('groups', $groups);
        $this->Design->assign('group', $group);
        $this->Design->assign('users', $users);
        $this->Design->assign('users_count', $users_count);

        return $this->Design->fetch('users/users.tpl');
    }
}
