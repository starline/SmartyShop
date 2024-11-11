<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class UserView extends View
{
    public function fetch()
    {

        // Закрываем от индексации
        $this->Design->assign('noindex', true);

        if (empty($this->user->id)) {
            $this->Misc->makeRedirect('/user/login', '301');
        }

        if ($this->Request->method('post') && $this->Request->post('name')) {

            $name			= $this->Request->post('name');
            $email			= $this->Request->post('email');
            $password		= $this->Request->post('password');

            $this->Design->assign('name', $name);
            $this->Design->assign('email', $email);

            $this->Database->query('SELECT count(*) as count FROM __users WHERE email=? AND id!=?', $email, $this->user->id);
            $user_exists = $this->Database->result('count');

            if ($user_exists) {
                $this->Design->assign('error', 'user_exists');
            } elseif (empty($name)) {
                $this->Design->assign('error', 'empty_name');
            } elseif (empty($email)) {
                $this->Design->assign('error', 'empty_email');
            } elseif ($this->Users->updateUser($this->user->id, ['name' => $name, 'email' => $email])) {
                $this->user = $this->Users->getUser($this->user->id);
                $this->Design->assign('name', $this->user->name);
                $this->Design->assign('user', $this->user);
                $this->Design->assign('email', $this->user->email);
            } else {
                $this->Design->assign('error', 'unknown error');
            }

            if (!empty($password)) {
                $this->Users->updateUser($this->user->id, ['password' => $password]);
            }
        } else {

            // Передаем в шаблон
            $this->Design->assign('name', $this->user->name);
            $this->Design->assign('email', $this->user->email);
        }

        $orders = $this->Orders->getOrders(['user_id' => $this->user->id]);
        $this->Design->assign('orders', $orders);


        // Устанавливаем meta-теги
        $this->Design->assign('meta_title', $this->user->name);
        $this->Design->assign('meta_description', $this->user->name);


        return $this->Design->fetch('user.tpl');
    }
}
