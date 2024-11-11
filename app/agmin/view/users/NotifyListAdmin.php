<?php

/**
 * GoodGin - The best of gins
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class notifyListAdmin extends Auth
{
    public function fetch()
    {

        if ($this->Request->method('post') and $this->access("users_groups_edit")) {

            // Сортировка
            $positions = $this->Request->post('positions');
            $positions_ids = array_keys($positions);
            sort($positions);
            foreach ($positions as $id => $position) {
                $this->UsersNotify->updateNotify($positions_ids[$id], array('position' => $position));
            }

            // Действия с выбранными
            $ids = $this->Request->post('check');

            if (is_array($ids)) {
                switch ($this->Request->post('action')) {
                    case 'delete': {
                        foreach ($ids as $id) {
                            $this->UsersNotify->deleteNotify($id);
                        }
                        break;
                    }
                }
            }
        }

        $notify_list = $this->UsersNotify->getNotifyList();

        $this->Design->assign('notify_list', $notify_list);
        return $this->Design->fetch('users/notify_list.tpl');
    }
}
