<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class LoginView extends View
{
    public function fetch()
    {

        // Закрываем от индексации
        $this->Design->assign('noindex', true);

        // Выход
        if ($this->Request->get('action') == 'logout') {

            unset($_SESSION['user_id']);
            $this->Misc->deleteCookie('UID');
            $this->Misc->makeRedirect($this->Config->root_url, '301');
        }


        // Вспомнить пароль
        elseif ($this->Request->get('action') == 'password_remind') {

            // Если запостили email
            if (!empty($email = $this->Request->post('email'))) {

                $this->Design->assign('email', $email);

                // Выбираем пользователя из базы
                $user = $this->Users->getUser(['email' => $email]);
                if (!empty($user)) {

                    // Генерируем секретный код и сохраняем в сессии
                    $code = md5(uniqid($this->Config->salt, true));
                    $_SESSION['password_remind_code'] = $code;
                    $_SESSION['password_remind_user_id'] = $user->id;

                    // Отправляем письмо пользователю для восстановления пароля
                    if ($this->UsersNotify->sendNotify('Email', 'userPasswordRemind', [
                        'user_id' => $user->id,
                        'code' => $code,
                        'to_email' => $user->email
                    ])) {
                        $this->Design->assign('email_sent', true);
                    }
                } else {
                    $this->Design->assign('error', 'user_not_found');
                }
            }


            // Если к нам перешли по ссылке для восстановления пароля
            elseif ($this->Request->get('code')) {
                if ($this->checkRemindCode() === false) {
                    $this->Design->assign('error', 'user_not_found');
                }
            }

            $this->Design->assign('canonical', "/user/password-remind");
            return $this->Design->fetch('user_password_remind.tpl');
        }


        // Вход
        elseif ($this->Request->method('post')) {

            $email			= $this->Request->post('email');
            $password		= $this->Request->post('password');

            $this->Design->assign('email', $email);

            if ($user_id = $this->Users->checkPassword($email, $password)) {
                $user = $this->Users->getUser(['email' => $email]);

                if ($user->enabled) {

                    $_SESSION['user_id'] = $user_id;
                    $this->Users->setRememberMeCookie($user_id);

                    $this->Users->updateUser($user_id, ['last_ip' => $_SERVER['REMOTE_ADDR']]);

                    // Перенаправляем пользователя на прошлую страницу, если она известна
                    if (!empty($_SESSION['last_visited_page'])) {
                        $this->Misc->makeRedirect($_SESSION['last_visited_page'], '301');
                    } else {
                        $this->Misc->makeRedirect($this->Config->root_url, '301');
                    }
                } else {
                    $this->Design->assign('error', 'user_disabled');
                }
            } else {
                $this->Design->assign('error', 'login_incorrect');
            }
        }

        $this->Design->assign('canonical', "/user/login");
        return $this->Design->fetch('user_login.tpl');
    }


    /**
     * Check remind code
     */
    private function checkRemindCode()
    {
        // Проверяем существование сессии
        if (empty($_SESSION['password_remind_code']) || empty($_SESSION['password_remind_user_id'])) {
            return false;
        }

        // Проверяем совпадение кода в сессии и в ссылке
        if ($this->Request->get('code') != $_SESSION['password_remind_code']) {
            return false;
        }

        // Выбераем пользователя из базы
        if (empty($user = $this->Users->getUser($_SESSION['password_remind_user_id']))) {
            return false;
        }

        // Залогиниваемся под пользователем
        $_SESSION['user_id'] = $user->id;

        // И переходим в кабинет для изменения пароля
        $this->Misc->makeRedirect($this->Config->root_url . '/user', '301');
    }
}
