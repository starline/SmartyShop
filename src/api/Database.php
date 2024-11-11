<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 * @version 1.2
 *
 * Класс для доступа к базе данных
 *
 */

namespace GoodGin;

class Database extends GoodGin
{
    private $mysqli;
    private $res;
    private $query_count = 0;

    /**
     * В конструкторе подключаем базу
     */
    public function __construct()
    {
        parent::__construct();
        $this->connect();
    }


    /**
     * В деструкторе отсоединяемся от базы
     */
    public function __destruct()
    {
        $this->disconnect();
    }


    /**
     * Подключение к базе данных
     */
    public function connect()
    {

        // При повторном вызове возвращаем существующий линк
        if (!empty($this->mysqli)) {
            return $this->mysqli;
        }

        // Иначе устанавливаем соединение
        else {
            $this->mysqli = new \mysqli($this->Config->db_server, $this->Config->db_user, $this->Config->db_password, $this->Config->db_name);
        }

        // Выводим сообщение, в случае ошибки
        if ($this->mysqli->connect_error) {
            trigger_error("Could not connect to the database: " . $this->mysqli->connect_error, E_USER_WARNING);
            return false;
        }

        // Или настраиваем соединение
        else {
            if ($this->Config->db_charset) {
                $this->mysqli->query('SET NAMES ' . $this->Config->db_charset);
            }
            if ($this->Config->db_sql_mode) {
                $this->mysqli->query('SET SESSION SQL_MODE = "' . $this->Config->db_sql_mode . '"');
            }
            if ($this->Config->db_timezone) {
                $this->mysqli->query('SET time_zone = "' . $this->Config->db_timezone . '"');
            }
        }
        return $this->mysqli;
    }


    /**
     * Закрываем подключение к базе данных
     */
    public function disconnect()
    {
        if (!@$this->mysqli->close()) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Запрос к базе. Обазятелен первый аргумент - текст запроса.
     * При указании других аргументов автоматически выполняется placehold() для запроса с подстановкой этих аргументов
     */
    public function query()
    {
        if (is_object($this->res)) {
            $this->res->free();
        }

        // Считаем кол-во запросов в базу за один сеанс
        $this->query_count += 1;

        $args = func_get_args();
        $query = call_user_func_array(array($this, 'placehold'), $args);

        return $this->res = $this->mysqli->query($query);
    }


    /**
     * Кол-во запросов к БД
     */
    public function get_query_count()
    {
        return $this->query_count;
    }


    /**
     * Плейсхолдер для запросов.
     * Пример работы: $query = $db->placehold('SELECT name FROM products WHERE id=?', $id);
     */
    public function placehold()
    {
        $args = func_get_args();
        $tmpl = array_shift($args);

        // Заменяем все __ на префикс, но только необрамленные кавычками
        $tmpl = preg_replace('/([^"\'0-9a-z_])__([a-z_]+[^"\'])/i', "\$1" . $this->Config->db_prefix . "\$2", $tmpl);
        if (!empty($args)) {
            $result = $this->sql_placeholder_ex($tmpl, $args, $error);
            if ($result === false) {
                $error = "Placeholder substitution error. Diagnostics: \"$error\"";
                trigger_error($error, E_USER_WARNING);
                return false;
            }
            return $result;
        } else {
            return $tmpl;
        }
    }


    /**
     * Возвращает результаты запроса.
     * Необязательный второй аргумент указывает какую колонку возвращать вместо всего массива колонок
     * @param $field
     */
    public function results(string $field = null): array|bool
    {
        $results = array();

        if ($this->res === false) {
            trigger_error($this->mysqli->error, E_USER_WARNING);
            return false;
        }

        if ($this->res->num_rows == 0) {
            return $results;
        }

        while ($row = $this->res->fetch_object()) {
            if (empty($field)) {
                $results[] = $row;
            } else {

                // If it has data
                if (isset($row->$field)) {
                    $results[] = $row->$field;
                }

                // If doesn't have Data or Column
                else {
                    $results[] = null;
                }
            }
        }
        return $results;
    }


    /**
     * Возвращает первый результат запроса.
     * Необязательный второй аргумент указывает какую колонку возвращать вместо всего массива колонок
     * @param string $field
     */
    public function result(string $field = null) # all types
    {
        if ($this->res === false) {
            trigger_error($this->mysqli->error, E_USER_WARNING);
            return false;
        }

        // Get 1-st row
        // If empty result, return NULL
        // If error, return FALSE
        $row = $this->res->fetch_object();

        // If $field = null, return all data Table
        if (empty($field)) {
            return $row;
        } else {

            // If it has data
            if (isset($row->$field)) {
                return $row->$field;
            }

            // If doesn't have Data or Column
            else {
                return null;
            }
        }
    }


    /**
     * Get last inser ID
     */
    public function getInsertId()
    {
        return ($this->mysqli->insert_id != 0) ? $this->mysqli->insert_id : false;
    }


    /**
     * Экранирование
     * @param $str
     */
    public function escape($str)
    {
        return $this->mysqli->real_escape_string($str);
    }


    /**
     * Возвращает количество выбранных строк
     */
    public function num_rows()
    {
        return $this->res->num_rows;
    }


    /**
     * Возвращает количество затронутых строк
     */
    public function affected_rows()
    {
        return $this->mysqli->affected_rows;
    }


    /**
     * Компиляция плейсхолдера
     */
    private function sql_compile_placeholder($tmpl)
    {
        $compiled = array();
        $p = 0;	 // текущая позиция в строке
        $i = 0;	 // счетчик placeholder-ов
        $has_named = false;
        while (false !== ($start = $p = strpos($tmpl, "?", $p))) {

            // Определяем тип placeholder-а.
            switch ($c = substr($tmpl, ++$p, 1)) {
                case '%':
                case '@':
                case '#':
                    $type = $c;
                    ++$p;
                    break;
                default:
                    $type = '';
                    break;
            }

            // Проверяем, именованный ли это placeholder: "?keyname"
            if (preg_match('/^((?:[^\s[:punct:]]|_)+)/', substr($tmpl, $p), $pock)) {
                $key = $pock[1];
                if ($type != '#') {
                    $has_named = true;
                }
                $p += strlen($key);
            } else {
                $key = $i;
                if ($type != '#') {
                    $i++;
                }
            }

            // Сохранить запись о placeholder-е.
            $compiled[] = array($key, $type, $start, $p - $start);
        }
        return array($compiled, $tmpl, $has_named);
    }


    /**
     * Выполнение плейсхолдера
     */
    private function sql_placeholder_ex($tmpl, $args, &$errormsg)
    {
        // Запрос уже разобран?.. Если нет, разбираем.
        if (is_array($tmpl)) {
            $compiled = $tmpl;
        } else {
            $compiled = $this->sql_compile_placeholder($tmpl);
        }

        list($compiled, $tmpl, $has_named) = $compiled;

        // Если есть хотя бы один именованный placeholder, используем
        // первый аргумент в качестве ассоциативного массива.
        if ($has_named) {
            $args = @$args[0];
        }

        // Выполняем все замены в цикле.
        $p	 = 0;		    // текущее положение в строке
        $out = '';		    // результирующая строка
        $error = false;     // были ошибки?

        foreach ($compiled as $num => $e) {
            list($key, $type, $start, $length) = $e;

            // Pre-string.
            $out .= substr($tmpl, $p, $start - $p);
            $p = $start + $length;

            $repl = '';		// текст для замены текущего placeholder-а
            $errmsg = '';   // сообщение об ошибке для этого placeholder-а

            do {

                // Это placeholder-константа?
                if ($type === '#') {
                    $repl = @constant($key);
                    if (null === $repl) {
                        $error = $errmsg = "UNKNOWN_CONSTANT_$key";
                    }
                    break;
                }

                // Обрабатываем ошибку.
                if (!isset($args[$key])) {
                    $error = $errmsg = "UNKNOWN_PLACEHOLDER_$key";
                    break;
                }

                // Вставляем значение в соответствии с типом placeholder-а.
                $a = $args[$key];
                if ($type === '') {

                    // Скалярный placeholder.
                    if (is_array($a)) {
                        $error = $errmsg = "NOT_A_SCALAR_PLACEHOLDER_$key";
                        break;
                    }

                    // Заменил addslashes (устарело) на mysqli->real_escape_string. В функуции $this->escape()
                    $repl = is_int($a) || is_float($a) ? str_replace(',', '.', $a) : "'" . $this->escape($a) . "'";
                    break;
                }

                // Иначе это массив или список.
                if (is_object($a)) {
                    $a = get_object_vars($a);
                }

                if (!is_array($a)) {
                    $error = $errmsg = "NOT_AN_ARRAY_PLACEHOLDER_$key";
                    break;
                }

                // Это список.
                if ($type === '@') {
                    foreach ($a as $v) {
                        if (is_null($v)) {
                            $r = "NULL";
                        } else {
                            $r = "'" . @$this->escape($v) . "'";
                        }

                        $repl .= ($repl === '' ? "" : ",") . $r;
                    }

                    // Это набор пар ключ=>значение.
                } elseif ($type === '%') {
                    $lerror = array();
                    foreach ($a as $k => $v) {
                        if (!is_string($k)) {
                            $lerror[$k] = "NOT_A_STRING_KEY_{$k}_FOR_PLACEHOLDER_$key";
                        } else {
                            $k = preg_replace('/[^a-zA-Z0-9_]/', '_', $k);
                        }

                        if (is_null($v)) {
                            $r = "=NULL";
                        } else {
                            $r = "='" . @$this->escape($v) . "'";
                        }

                        $repl .= ($repl === '' ? "" : ", ") . $k . $r;
                    }

                    // Если была ошибка, составляем сообщение.
                    if (count($lerror)) {
                        $repl = '';
                        foreach ($a as $k => $v) {
                            if (isset($lerror[$k])) {
                                $repl .= ($repl === '' ? "" : ", ") . $lerror[$k];
                            } else {
                                $k = preg_replace('/[^a-zA-Z0-9_-]/', '_', $k);
                                $repl .= ($repl === '' ? "" : ", ") . $k . "=?";
                            }
                        }
                        $error = $errmsg = $repl;
                    }
                }
            } while (false);

            if ($errmsg) {
                $compiled[$num]['error'] = $errmsg;
            }
            if (!$error) {
                $out .= $repl;
            }
        }
        $out .= substr($tmpl, $p);

        // Если возникла ошибка, переделываем результирующую строку
        // в сообщение об ошибке (расставляем диагностические строки
        // вместо ошибочных placeholder-ов).
        if ($error) {
            $out = '';
            $p	 = 0; // текущая позиция
            foreach ($compiled as $num => $e) {
                list($key, $type, $start, $length) = $e;
                $out .= substr($tmpl, $p, $start - $p);
                $p = $start + $length;
                if (isset($e['error'])) {
                    $out .= $e['error'];
                } else {
                    $out .= substr($tmpl, $start, $length);
                }
            }

            // Последняя часть строки.
            $out .= substr($tmpl, $p);
            $errormsg = $out;
            return false;
        } else {
            $errormsg = false;
            return $out;
        }
    }


    /**
     * Создаем бэкап всех таблиц базы данных.
     * Сохраняем в вайл
     * @param $filename
     */
    public function dump($filename)
    {
        $h = fopen($filename, 'w');

        // Выбираем все таблицы
        $q = $this->placehold("SHOW FULL TABLES LIKE '__%';");
        $result = $this->mysqli->query($q);

        while ($row = $result->fetch_row()) {
            if ($row[1] == 'BASE TABLE') {
                $this->dump_table($row[0], $h);
            }
        }

        fclose($h);
    }


    /**
     * Восстанавливаем БД из файла
     * @param $filename
     */
    public function restore($filename)
    {
        $templine = '';
        $h = fopen($filename, 'r');

        // Loop through each line
        if ($h) {
            while (!feof($h)) {
                $line = fgets($h);

                // Only continue if it's not a comment
                if (substr($line, 0, 2) != '--' && $line != '') {

                    // Add this line to the current segment
                    $templine .= $line;

                    // If it has a semicolon at the end, it's the end of the query
                    if (substr(trim($line), -1, 1) == ';') {

                        // Perform the query
                        $this->mysqli->query($templine) or print('Error performing query \'<b>' . $templine . '</b>\': ' . $this->mysqli->error . '<br/><br/>');

                        // Reset temp variable to empty
                        $templine = '';
                    }
                }
            }
        }
        fclose($h);
    }


    private function dump_table($table, $h)
    {
        $sql = "SELECT * FROM `$table`;";
        $result = $this->mysqli->query($sql);

        if ($result) {
            fwrite($h, "/* Data for table $table */\n");
            fwrite($h, "TRUNCATE TABLE `$table`;\n");

            $num_rows = $result->num_rows;
            $num_fields = $this->mysqli->field_count;

            if ($num_rows > 0) {
                $field_type = array();
                $field_name = array();
                $meta = $result->fetch_fields();

                foreach ($meta as $m) {
                    array_push($field_type, $m->type);
                    array_push($field_name, $m->name);
                }

                $fields = join('`, `', $field_name);
                fwrite($h, "INSERT INTO `$table` (`$fields`) VALUES\n");
                $index = 0;

                while ($row = $result->fetch_row()) {
                    fwrite($h, "(");
                    for ($i = 0; $i < $num_fields; $i++) {
                        if (is_null($row[$i])) {
                            fwrite($h, "null");
                        } else {
                            switch ($field_type[$i]) {
                                case 'int':
                                    fwrite($h, $row[$i]);
                                    break;
                                case 'string':
                                case 'blob':
                                default:
                                    fwrite($h, "'" . $this->mysqli->real_escape_string($row[$i]) . "'");
                            }
                        }
                        if ($i < $num_fields - 1) {
                            fwrite($h, ",");
                        }
                    }
                    fwrite($h, ")");

                    if ($index < $num_rows - 1) {
                        fwrite($h, ",");
                    } else {
                        fwrite($h, ";");
                    }
                    fwrite($h, "\n");

                    $index++;
                }
            }
        }
        $result->free();
        fwrite($h, "\n");
    }
}
