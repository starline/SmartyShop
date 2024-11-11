<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 * @version 2.5
 *
 * Класс-обертка для обращения к переменным _GET, _POST, _FILES
 *
 */

namespace GoodGin;

class Request extends GoodGin
{
    /**
     * Конструктор, чистка слешей
     */
    public function __construct()
    {
        parent::__construct();

        $_POST = $this->stripslashes_recursive($_POST);
        $_GET = $this->stripslashes_recursive($_GET);
    }


    /**
     * Определение request-метода обращения к странице (GET, POST)
     * Если задан аргумент функции (название метода, в любом регистре), возвращает true или false
     * Если аргумент не задан, возвращает имя метода
     * Пример:
     *
     *	if($GoodGin->Request->method('post'))
     *		print 'Request method is POST';
     *
     */
    public function method($method = null)
    {
        if (!empty($method)) {
            return strtolower($_SERVER['REQUEST_METHOD']) == strtolower($method);
        }
        return $_SERVER['REQUEST_METHOD'];
    }


    /**
     * Возвращает variable из методов GET и POST
     */
    public function getVar($var_name = null, $type = null)
    {
        $val = null;
        $val = $this->post($var_name, $type);
        if (empty($val)) {
            $val = $this->get($var_name, $type);
        }
        return $val;
    }


    /**
     * Возвращает переменную _GET, отфильтрованную по заданному типу, если во втором параметре указан тип фильтра
     * Если $type не задан, возвращает переменную в чистом виде
     * @param $name
     * @param $type
     */
    public function get($name = null, $type = null)
    {
        // Returne all $_GET values
        if (is_null($name) and is_null($type)) {
            return $this->gets();
        }

        $val = null; // если переменная не задана возвращаем null
        if (!empty($name) and isset($_GET[$name])) {
            $val = $_GET[$name];
            if (!is_null($type)) {
                $val = $this->getValueByType($val, $type);
            }
        }

        return $val;
    }


    /**
     * Выбираем все значения $_GET
     */
    public function gets()
    {
        $res = null;
        foreach ($_GET as $p_name => $p_data) {
            $res[$p_name] = $p_data;
        }
        return $res;
    }


    /**
     * Возвращает переменную $_POST, отфильтрованную по заданному типу, если во втором параметре указан тип фильтра
     * Если $type не задан, возвращает переменную в чистом виде
     * Если переменной не существует в $_POST, возвращаем NULL
     * @param $name
     * @param $type
     */
    public function post($name = null, $type = null)
    {

        // Returne all $_POST values
        if (is_null($name) and is_null($type)) {
            return $this->posts();
        }

        $val = null;
        if (!empty($name)) {
            if (isset($_POST[$name])) {
                $val = $_POST[$name];
            }
            if (!is_null($type)) {
                $val = $this->getValueByType($val, $type);
            }
        } elseif (empty($name)) {
            $val = file_get_contents('php://input');
        }

        //echo $name."=".$val.'<br>';

        return $val;
    }


    /**
     * Выбираем все значения $_POST
     */
    public function posts()
    {
        $res = null;

        if (!empty($_POST)) {
            foreach ($_POST as $p_name => $p_data) {
                $res[$p_name] = $p_data;
            }
        }

        return $res;
    }


    /**
     * Перобразовываем переменные согласно их типу
     * @param $var
     * @param $type - может иметь такие значения: integer, string, boolean, float
     */
    private function getValueByType($val, $type)
    {

        // Массив
        if (is_array($val) or ($type == 'array' and is_array($val))) {
            reset($val);
            return $val;
        }

        // Строка
        if ($type == 'string') {

            // Оставляем только допустимые знаки
            // Были проблемы, что страница имела несколько url
            //$val = preg_replace('/[^\p{L}\p{Nd}\d\s_\-\.\%\s]/ui', '', $val);

            return strval($val);
        }

        // Целое число
        if ($type == 'integer') {

            // Преобразуем строки в NULL
            if ($val == 'null' or $val == '' or is_null($val)) {
                return null;
            } else {
                return intval($val);
            }
        }

        // Число с плавающей запятой
        if ($type == 'float') {

            // заменим запятые на точку
            $val = str_replace(',', '.', $val);
            return floatval($val);
        }

        // Да или Нет
        if ($type == 'boolean') {
            return empty($val) ? 0 : 1;
        }
    }


    /**
     * Возвращает переменную $_FILES
     * Обычно переменные $_FILES являются двухмерными массивами, поэтому можно указать второй параметр,
     * например, чтобы получить имя загруженного файла: $filename = $GoodGin->Request->files('myfile', 'name');
     */
    public function files($name, $name2 = null)
    {
        if (!empty($name2) && !empty($_FILES[$name][$name2])) {
            return $_FILES[$name][$name2];
        } elseif (empty($name2) && !empty($_FILES[$name])) {
            return $_FILES[$name];
        } else {
            return null;
        }
    }


    /**
     * Рекурсивная чистка магических слешей
     * @param $var
     */
    private function stripslashes_recursive($var)
    {
        $res = null;
        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $res[stripcslashes($k)] = $this->stripslashes_recursive($v);
            }
        } else {
            $res = stripcslashes($var);
        }

        return $res;
    }


    /**
     * Проверка сессии
     */
    public function check_session()
    {
        if (!empty($_POST)) {
            if (empty($_POST['session_id']) || $_POST['session_id'] != session_id()) {
                unset($_POST);
                return false;
            }
        }
        return true;
    }


    /**
     * Creat URL
     * @param $params - переменные
     * @param $clear - зачищает пустые переменные
     */
    public function url($params = array(), $clear = false)
    {
        $url = @parse_url($_SERVER["REQUEST_URI"]);

        $query = array();
        if (isset($url['query']) and !$clear) {
            parse_str($url['query'], $query);
        }

        foreach ($query as &$v) {
            if (!is_array($v)) {
                $v = stripslashes(urldecode($v));
            }
        }

        foreach ($params as $name => $value) {
            $query[$name] = $value;
        }

        // Check empty value
        foreach ($query as $name => $value) {
            if ($value === '' or $value === null) { // При не срогом сравнение '' = 0
                unset($query[$name]);
            }
        }

        if (!empty($query)) {
            $url['query'] = http_build_query($query);
        } else {
            $url['query'] = null;
        }

        $result = http_build_url("", $url);
        return $result;
    }
}




if (!function_exists('http_build_url')) {
    define('HTTP_URL_REPLACE', 1);				// Replace every part of the first URL when there's one of the second URL
    define('HTTP_URL_JOIN_PATH', 2);			// Join relative paths
    define('HTTP_URL_JOIN_QUERY', 4);			// Join query strings
    define('HTTP_URL_STRIP_USER', 8);			// Strip any user authentication information
    define('HTTP_URL_STRIP_PASS', 16);			// Strip any password authentication information
    define('HTTP_URL_STRIP_AUTH', 32);			// Strip any authentication information
    define('HTTP_URL_STRIP_PORT', 64);			// Strip explicit port numbers
    define('HTTP_URL_STRIP_PATH', 128);			// Strip complete path
    define('HTTP_URL_STRIP_QUERY', 256);		// Strip query string
    define('HTTP_URL_STRIP_FRAGMENT', 512);		// Strip any fragments (#identifier)
    define('HTTP_URL_STRIP_ALL', 1024);			// Strip anything but scheme and host

    /**
     * Build an URL
     * The parts of the second URL will be merged into the first according to the flags argument.
     *
     * @param	mixed			(Part(s) of) an URL in form of a string or associative array like parse_url() returns
     * @param	mixed			Same as the first argument
     * @param	int				A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
     * @param	array			If set, it will be filled with the parts of the composed url like parse_url() would return
     *
     */
    function http_build_url($url = "", $parts = array(), $flags = HTTP_URL_REPLACE, &$new_url = false)
    {
        $keys = array('user', 'pass', 'port', 'path', 'query', 'fragment');

        // HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
        if ($flags & HTTP_URL_STRIP_ALL) {
            $flags |= HTTP_URL_STRIP_USER;
            $flags |= HTTP_URL_STRIP_PASS;
            $flags |= HTTP_URL_STRIP_PORT;
            $flags |= HTTP_URL_STRIP_PATH;
            $flags |= HTTP_URL_STRIP_QUERY;
            $flags |= HTTP_URL_STRIP_FRAGMENT;
        }
        // HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
        elseif ($flags & HTTP_URL_STRIP_AUTH) {
            $flags |= HTTP_URL_STRIP_USER;
            $flags |= HTTP_URL_STRIP_PASS;
        }

        // Parse the original URL
        $parse_url = parse_url($url);

        // Scheme and Host are always replaced
        if (isset($parts['scheme'])) {
            $parse_url['scheme'] = $parts['scheme'];
        }
        if (isset($parts['host'])) {
            $parse_url['host'] = $parts['host'];
        }

        // (If applicable) Replace the original URL with it's new parts
        if ($flags & HTTP_URL_REPLACE) {
            foreach ($keys as $key) {
                if (isset($parts[$key])) {
                    $parse_url[$key] = $parts[$key];
                }
            }
        } else {

            // Join the original URL path with the new path
            if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH)) {
                if (isset($parse_url['path'])) {
                    $parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
                } else {
                    $parse_url['path'] = $parts['path'];
                }
            }

            // Join the original query string with the new query string
            if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY)) {
                if (isset($parse_url['query'])) {
                    $parse_url['query'] .= '&' . $parts['query'];
                } else {
                    $parse_url['query'] = $parts['query'];
                }
            }
        }

        // Strips all the applicable sections of the URL
        // Note: Scheme and Host are never stripped
        foreach ($keys as $key) {
            if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key))) {
                unset($parse_url[$key]);
            }
        }


        $new_url = $parse_url;

        return ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
            . ((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') . '@' : '')
            . ((isset($parse_url['host'])) ? $parse_url['host'] : '')
            . ((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
            . ((isset($parse_url['path'])) ? $parse_url['path'] : '')
            . ((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
            . ((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '');
    }
}


if (!function_exists('http_build_query')) {
    function http_build_query($data, $prefix = null, $sep = '', $key = '')
    {
        $ret    = array();
        foreach ((array)$data as $k => $v) {
            $k    = urlencode($k);
            if (is_int($k) && $prefix != null) {
                $k    = $prefix . $k;
            }
            if (!empty($key)) {
                $k    = $key . "[" . $k . "]";
            }

            if (is_array($v) || is_object($v)) {
                array_push($ret, http_build_query($v, "", $sep, $k));
            } else {
                array_push($ret, $k . "=" . urlencode($v));
            }
        }

        if (empty($sep)) {
            $sep = ini_get("arg_separator.output");
        }

        return join($sep, $ret);
    }
}
