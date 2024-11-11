<?php

/**
 * GoodGin - The Best of gins
 *
 * @author Andi Huga
 * @version 1.2
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class UserAdmin extends Auth
{
    public function fetch()
    {

        $current_user = new \stdClass();
        $orders = new \stdClass();
        $orders_count = null;
        $orders_price = null;

        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions = array(
            "users" => array(
                "id" => "integer",
                "name" => "string",
                "email" => "string",
                "phone" => "string",
                "comment" => "string"
            ),
            "users_manager" => ["manager" => "boolean"],
            "users_edit" => array("enabled" => "boolean"),
            "users_groups" => array("group_id" => "integer")
        );


        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $current_user = $this->postDataAcces($data_permissions);

            if (empty($current_user->name)) {
                $this->Design->assign('message_error', 'empty_name');
            }

            // Не допустить одинаковые email пользователей
            elseif (isset($current_user->email) and ($u_check = $this->Users->getUser(['email' => $current_user->email])) and $u_check->id != $current_user->id) {
                $this->Design->assign('message_error', 'email_exists');
            }

            // Не допустить одинаковые телефон пользователей
            elseif (isset($current_user->phone) and ($u_check = $this->Users->getUser(['phone' => $current_user->phone])) and $u_check->id != $current_user->id) {
                $this->Design->assign('message_error', 'phone_exists');
            }

            // Update User data
            else {
                $this->Users->updateUser(intval($current_user->id), $current_user);
                $this->Design->assign('message_success', 'updated');

                // If NOT already a manager - clear permission data
                if ($current_user->manager == 0) {
                    $this->Users->updatePermissions($current_user->id, null);
                    $this->Users->updateUserNotifyTypes($current_user->id, null);
                }
            }
        }


        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($current_user))) {

            $current_user = $this->Users->getUser($id);

            if (empty($current_user->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }

            $filter = array('user_id' => $current_user->id);
            $orders = $this->Orders->getOrders($filter);

            // Товары и картинка
            foreach ($this->Orders->getPurchases(array('order_id' => array_keys($orders)), array("image")) as $op) {
                $orders[$op->order_id]->purchases[] = $op;
            }

            // Кол-во заказов
            $orders_count = $this->Orders->getOrdersCount($filter);

            // Выбираем общую сумму заказов
            $filter["paid"] = 1;
            $orders_price = $this->Orders->getOrdersPrice($filter);
        }

        // Выбираем все группы пользователей
        $groups = $this->UsersGroups->getGroups();

        $this->Design->assign([
            'current_user' => $current_user,
            'groups' => $groups,
            'orders' => $orders,
            'orders_count' => $orders_count,
            'orders_price' => $orders_price,
        ]);

        return $this->Design->fetch('users/user.tpl');
    }
}
