<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 2.1
 *
 * Тут работает с данными пользователей
 *
 */

namespace GoodGin;

class Users extends GoodGin
{
    /**
     * Get Users List
     * @param array $filter
     * @param bool $count
     */
    public function getUsers(array $filter = array(), bool $count = false)
    {

        // Pages view
        $sql_limit = "";
        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
            $page = 1;
            if (isset($filter['page'])) {
                $page = max(1, intval($filter['page']));
            }
            $sql_limit = $this->Database->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);
        }

        $where_group_id = '';
        if (!empty(($filter['group_id']))) {
            $where_group_id = $this->Database->placehold('AND u.group_id in(?@)', (array)$filter['group_id']);
        }

        $where_manager = '';
        if (isset($filter['manager'])) { # 1|0
            $where_manager = $this->Database->placehold('AND u.manager = ?', intval($filter['manager']));
        }

        $where_keyword = '';
        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);

            // Ищем по name, email, last_ip, phone
            foreach ($keywords as $keyword) {
                $where_keyword .= $this->Database->placehold('AND (u.name LIKE "%' . $this->Database->escape(trim($keyword)) . '%" OR u.email LIKE "%' . $this->Database->escape(trim($keyword)) . '%" OR u.last_ip LIKE "%' . $this->Database->escape(trim($keyword)) . '%" OR u.phone LIKE "%' . $this->Database->escape(trim($keyword)) . '%")');
            }
        }

        $order = 'u.name';
        if (!empty($filter['sort'])) {
            switch ($filter['sort']) {
                case 'date':
                    $order = 'u.created DESC';
                    break;
                case 'name':
                    $order = 'u.name';
                    break;
                case 'manager':
                    $order = 'u.manager DESC';
                    break;
            }
        }

        // Выбираем пользователей
        if ($count === false) {

            $query = $this->Database->placehold(
                "SELECT 
                    u.*,
                    ug.discount,
                    ug.name as group_name
                FROM 
                    __users u
                    LEFT JOIN __users_groups ug ON u.group_id=ug.id 
                WHERE 
                    1 
                    $where_manager 
                    $where_group_id 
                    $where_keyword 
                ORDER BY
                    $order 
                $sql_limit"
            );

            $this->Database->query($query);
            return $this->Database->results();

        }

        // Выбираем кол-во
        else {

            $query = $this->Database->placehold(
                "SELECT 
					count(*) as count
				FROM 
					__users u
					LEFT JOIN __users_groups ug ON u.group_id=ug.id 
				WHERE 
					1 
					$where_manager
					$where_group_id
					$where_keyword
				ORDER BY 
					u.name"
            );

            $this->Database->query($query);
            return $this->Database->result('count');
        }
    }


    /**
     * Выбираем общее кол-во users
     * @param array $filter
     */
    public function countUsers(array $filter = array())
    {
        return $this->getUsers($filter, true);
    }


    /**
     * Выбираем пользователя с базы
     * @param int|string $id - ID/Email/Phone
     */
    public function getUser(int|string|array $id)
    {
        if (empty($id)) {
            return false;
        }

        $where = '';

        // if Array te_name|email|phone
        if (is_array($id)) {
            if (!empty($id['token'])) {
                $where = $this->Database->placehold(' AND u.token=? ', $id['token']);
            }
            if (!empty($id['email']) and substr_count($id['email'], '@') == 1) {
                $where = $this->Database->placehold(' AND u.email=? ', $id['email']);
            }
            if (!empty($id['phone'])) {
                $id['phone'] = $this->Misc->clearPhoneNummber($id['phone']);
                $where = $this->Database->placehold(' AND u.phone LIKE "%' . $id['phone'] . '%" ');
            }
        }

        // If id
        else {
            $where = $this->Database->placehold(' AND u.id=? ', intval($id));
        }

        // Выбираем пользователя
        $query = $this->Database->placehold(
            "SELECT 
				u.*,
				g.discount,
				g.name as group_name,
                g.id as group_id
			FROM 
				__users u
				LEFT JOIN __users_groups g ON u.group_id = g.id 
            WHERE 
                1 
			    $where
			LIMIT 
				1"
        );

        $this->Database->query($query);
        $user = $this->Database->result();

        if (empty($user)) {
            return false;
        }

        $user->discount *= 1; // Убираем лишние нули, чтобы было 5 вместо 5.00

        //print_r($user);
        return $user;
    }


    /**
     * Добавляем нового пользователя
     * @param $user
     */
    public function addUser($user)
    {
        $user = $this->Misc->cleanEntityId($user);

        // Шифруем пароль
        if (isset($user->password)) {
            $user->password = md5($this->Config->salt_psw . $user->password . md5($user->password));
        }

        // Убираем пробелы в номере телефона и добавляем +38
        if (isset($user->phone)) {
            $user->phone = $this->Misc->clearPhoneNummber($user->phone);
        }

        // Если такой email есть, не добавляем
        if (!empty($user->email)) {
            $query = $this->Database->placehold("SELECT count(*) as count FROM __users WHERE email=?", $user->email);
            $this->Database->query($query);

            if ($this->Database->result('count') > 0) {
                return false;
            }
        }

        // Определяем IP
        $user->last_ip = $_SERVER['REMOTE_ADDR'];
        $user->token = $this->Misc->getToken(uniqid('x', true));

        $query = $this->Database->placehold("INSERT INTO __users SET ?%", $user);
        $this->Database->query($query);

        return $this->Database->getInsertId();
    }


    /**
     * Обновляем данные пользователя
     * @param int $id
     * @param $user
     */
    public function updateUser(int $id, $user)
    {
        $user = (object)$user;

        // Убираем пробелы в номере телефона и добавляем +38
        if (!empty($user->phone)) {
            $user->phone = $this->Misc->clearPhoneNummber($user->phone);
        }

        if (!empty($user->password)) {
            $user->password = md5($this->Config->salt_psw . $user->password . md5($user->password));
        }

        $query = $this->Database->placehold("UPDATE __users SET ?% WHERE id=? LIMIT 1", $user, intval($id));
        return $this->Database->query($query);
    }


    /**
     * Удалить пользователя
     * @param int $id - ID пользователя
     */
    public function deleteUser(int $id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("UPDATE __orders SET user_id=NULL WHERE id=? LIMIT 1", intval($id));
        if ($this->Database->query($query)) {

            $query = $this->Database->placehold("DELETE FROM __users WHERE id=? LIMIT 1", intval($id));
            return $this->Database->query($query);
        }
        return false;
    }


    /**
     * Проверяем пароль пользователя
     * @param $email
     * @param $password
     */
    public function checkPassword($email, $password)
    {
        $encpassword = md5($this->Config->salt_psw . $password . md5($password));
        $query = $this->Database->placehold("SELECT id FROM __users WHERE email=? AND password=? LIMIT 1", $email, $encpassword);

        $this->Database->query($query);
        if ($id = $this->Database->result('id')) {
            return $id;
        }
        return false;
    }


    /**
     * Generate remember me key.
     * @param int $id
     * @return string
     */
    private function generateRememberMeKey(int $user_id): string
    {
        $key = 'rememberme_uid_' . $user_id;
        return md5($key);
    }


    /**
     * Check remember me key.
     * @param string $key
     * @param int $id
     * @return bool
     */
    private function checkRememberMeKey(string $key, int $user_id): bool
    {
        return $key == $this->generateRememberMeKey($user_id);
    }


    /**
     * Set remember me cookie.
     * @param $id
     * @return void
     */
    public function setRememberMeCookie($user_id)
    {
        $data = array(
            'key' => $this->generateRememberMeKey($user_id),
            'id' => $user_id
        );
        $data = serialize($data);
        $this->Misc->setCookie('UID', $data, 360);
    }


    /**
     * Check remember me cookie.
     * Does autologin.
     * @return bool|int
     */
    public function checkRememberMeCookie(): bool|int
    {
        if (empty($cookie_uid = $this->Misc->getCookie('UID'))) {
            return false;
        }

        $cookie_uid = unserialize($cookie_uid);
        if (isset($cookie_uid['key']) && isset($cookie_uid['id'])) {
            if ($this->checkRememberMeKey($cookie_uid['key'], $cookie_uid['id']) === true) {
                return $cookie_uid['id'];
            }
        }

        return false;
    }


    /**
     * Выбираем названия прав доступа пользователя
     * @param int $user_id
     */
    public function getUserPermissionsName(int $user_id)
    {
        $query = $this->Database->placehold(
            "SELECT 
				name 
			FROM 
				__users_permissions  
			WHERE 
				user_id = ?",
            intval($user_id)
        );

        $this->Database->query($query);
        return $this->Database->results('name');
    }


    /**
     * Обновляем настройки доступа
     * @param int $user_id
     * @param array|null $permissions
     */
    public function updatePermissions(int $user_id, array|null $permissions)
    {

        // Delete all permissions
        $query = $this->Database->placehold("DELETE FROM __users_permissions WHERE user_id=?", $user_id);
        $this->Database->query($query);

        if (!empty($permissions) && is_array($permissions)) {
            $values = array();

            foreach ($permissions as $perm) {
                if (!empty($perm)) {
                    $values[] = "($user_id, '$perm')";
                }
            }

            $query = $this->Database->placehold("INSERT INTO __users_permissions (user_id, name) VALUES " . join(', ', $values));
            return $this->Database->query($query);
        }
        return true;
    }


    /**
     * Check user access
     * @param object $user
     * @param array|string $access_type
     */
    public function checkUserAccess(object|null $user, $access_type): bool
    {
        if (empty($user->permissions)) {
            return false;
        }

        if (is_array($access_type)) {
            foreach ($access_type as $access_one) {
                if (in_array($access_one, $user->permissions)) {
                    return true;
                }
            }
            return false;
        }

        return in_array($access_type, $user->permissions);
    }

}
