<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

use Recaptcha\Recaptcha;

class RegisterView extends View
{
    public function fetch()
    {
        // Закрываем от индексации
        $this->Design->assign('noindex', true);

        $default_status = 1; // Активен ли пользователь сразу после регистрации (0 или 1)

        if ($this->Request->method('post') && $this->Request->post('register')) {

            $name			= $this->Request->post('name');
            $email			= $this->Request->post('email');
            $password		= $this->Request->post('password');

            $this->Design->assign('name', $name);
            $this->Design->assign('email', $email);

            $this->Database->query('SELECT count(*) as count FROM __users WHERE email=?', $email);
            $user_exists = $this->Database->result('count');

            if ($user_exists) {
                $this->Design->assign('error', 'user_exists');
            } elseif (empty($name)) {
                $this->Design->assign('error', 'empty_name');
            } elseif (empty($email)) {
                $this->Design->assign('error', 'empty_email');
            } elseif (empty($password)) {
                $this->Design->assign('error', 'empty_password');
            }

            // Check google recaptchia POST
            elseif (empty($this->Request->post('g-recaptcha-response'))) {
                $this->Design->assign('error', 'captcha');
            } else {

                // Verify google recaptchia
                $googleResp = Recaptcha::recaptchaCheckAnswer(
                    $this->Config->rc_private_key,
                    $_SERVER["REMOTE_ADDR"],
                    $_POST["g-recaptcha-response"]
                );

                if ($googleResp->success) {

                    $user_id = $this->Users->addUser(array('name' => $name, 'email' => $email, 'password' => $password, 'enabled' => $default_status));

                    $_SESSION['user_id'] = $user_id;
                    $this->Users->setRememberMeCookie($user_id);

                    if (!empty($_SESSION['last_visited_page'])) {
                        header('Location: ' . $_SESSION['last_visited_page']);
                    } else {
                        header('Location: ' . $this->Config->root_url);
                    }
                }
            }
        }

        $this->Design->assign('canonical', "/user/register");
        return $this->Design->fetch('user_register.tpl');
    }
}
