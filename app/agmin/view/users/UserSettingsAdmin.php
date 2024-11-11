<?php

/**
 * GoodGin - The Best of gins
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class UserSettingsAdmin extends Auth
{
    public function fetch()
    {

        $current_user = new stdClass();
        $permissions = null;

        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions = array(
            "users" => array(
                "id" => "integer"
            ),
            "users_groups" => ["group_id" => "integer"]
        );


        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $current_user = $this->postDataAcces($data_permissions);

            $this->Users->updateUser(intval($current_user->id), $current_user);

            // Update user Permission
            $permissions_arr = $this->Request->post('permissions', 'array');
            $this->Users->updatePermissions($current_user->id, $permissions_arr);

            // Update User Notify Types
            $user_notify_types_arr = $this->Request->post('user_notify_types', 'array');
            $this->UsersNotify->updateUserNotifyTypes($current_user->id, $user_notify_types_arr);

            $this->Design->assign('message_success', 'updated');

        }


        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($current_user))) {

            $current_user = $this->Users->getUser($id);

            if (empty($current_user->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }

            if ($this->access('users_manager')) {
                $permissions = $this->Users->getUserPermissionsName($current_user->id);
            }
        }

        // Выбираем все группы пользователей
        $groups = $this->UsersGroups->getGroups();

        //
        $notify_methods = $this->UsersNotify->getNotifyList(['enabled' => 1]);
        $notify_types = $this->UsersNotify->getNotifyTypes('admmin');
        $user_notify_types = $this->UsersNotify->getUserNotifyTypes($current_user->id);

        $this->Design->assign([
            'current_user' => $current_user,
            'permissions' => $permissions,
            'permissions_list' => $this->permissions_list,
            'groups' => $groups,
            'notify_types' => $notify_types,
            'notify_methods' => $notify_methods,
            'user_notify_types' => $user_notify_types
        ]);

        return $this->Design->fetch('users/user_settings.tpl');
    }
}
