<?php

session_start();
define('secure', 'true');

// Composer
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
require_once  dirname(__DIR__) . '/view/Auth.php';
$GoodGin = new Auth();

class ExportAjax extends Auth
{
    // Названия столбцов соответсвуют названиям в mySQL
    private $columns_names = array(
        'id' =>				'id',
        'name' =>           'name',
        'email' =>          'Email',
        'phone' =>          'phone',
        'group_name' =>     'group_name',
        'discount' =>       'discount',
        'enabled' =>        'enabled',
        'created' =>        'created',
        'last_ip' =>        'last_ip',
        'comment' =>		'comment'
    );

    private $column_delimiter = ';';
    private $users_count = 40;
    private $export_files_dir = 'files/exports/';
    private $filename = 'users.csv';

    public function fetch()
    {

        if (!$this->access('users')) {
            return false;
        }

        $export_file_path = $this->Config->root_dir . $this->export_files_dir . $this->filename;

        // Эксель кушает только 1251
        //setlocale(LC_ALL, 'ru_RU.1251');
        //$this->Database->query('SET NAMES cp1251');

        // Страница, которую экспортируем
        $page = $this->Request->get('page');
        if (empty($page) || $page == 1) {
            $page = 1;

            // Если начали сначала - удалим старый файл экспорта
            if (is_writable($export_file_path)) {
                unlink($export_file_path);
            }
        }

        // Открываем файл экспорта на добавление
        $f = fopen($export_file_path, 'ab');

        // Если начали сначала - добавим в первую строку названия колонок
        if ($page == 1) {
            fputcsv($f, $this->columns_names, $this->column_delimiter);
        }

        $filter = array();
        $filter['page'] = $page;
        $filter['limit'] = $this->users_count;

        if (!empty($this->Request->get('group_id'))) {
            $filter['group_id'] = intval($this->Request->get('group_id'));
        }

        $filter['sort'] = $this->Request->get('sort');
        $filter['keyword'] = $this->Request->get('keyword', "string");

        // Выбираем пользователей
        foreach ($this->Users->getUsers($filter) as $u) {
            $str = array();
            foreach ($this->columns_names as $n => $c) {
                $str[] = $u->$n;
            }

            fputcsv($f, $str, $this->column_delimiter);
        }

        $total_users = $this->Users->countUsers($filter);

        if (($this->users_count * $page) < $total_users) {
            return array('end' => false, 'page' => $page, 'totalpages' => ceil($total_users / $this->users_count));
        } else {
            return array('end' => true, 'page' => $page, 'totalpages' => ceil($total_users / $this->users_count));
        }

        fclose($f);
    }
}

$export_ajax = new ExportAjax();

header("Content-type: application/json; charset=utf-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($export_ajax->fetch());
