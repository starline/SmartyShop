<?php

/**
 * GoodGin CMS - The Best of gins
 * BackupAdmin
 *
 * @author Andi Huga
 * @version 2.5
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class BackupAdmin extends Auth
{
    private $backup_dir;


    public function __construct()
    {
        parent::__construct();

        set_time_limit(600);
        $this->backup_dir = $this->Config->root_dir . 'files/backup/';
        define('PCLZIP_TEMPORARY_DIR', $this->backup_dir);
    }


    public function fetch()
    {

        // Обработка действий
        if ($this->Request->method('post')) {
            switch ($this->Request->post('action')) {

                // Создаем Бэкап
                case 'create': {

                    // Определяем название файла
                    if (empty($file_name = $this->Settings->site_name)) {
                        $file_name = $this->Config->db_name;
                    }

                    $file_path = $this->backup_dir . $file_name . '_' . date("Y-m-d_G-i-s") . '.zip';

                    ## Дамп базы
                    // Ложим базу в папку чтобы затем добавить файл в архив
                    $this->Database->dump($this->backup_dir . $this->Config->db_name . ".sql");
                    chmod($this->backup_dir . $this->Config->db_name . ".sql", 0777);

                    ### Архивируем.
                    // Для корректной апхивации нужно func_overload = 0
                    // fastcgi_param  PHP_VALUE "mbstring.func_overload = 0";
                    $zip = new PclZip($file_path);
                    $v_list = $zip->create($this->Config->root_dir . "files", PCLZIP_OPT_REMOVE_PATH, $this->Config->root_dir, PCLZIP_CB_PRE_ADD, "myCallBack");
                    if ($v_list == 0) {
                        trigger_error('Не могу заархивировать ' . $zip->errorInfo(true));
                    }

                    $v_list = $zip->add(array($this->backup_dir . $this->Config->db_name . ".sql"), PCLZIP_OPT_REMOVE_PATH, $this->backup_dir);
                    if ($v_list == 0) {
                        trigger_error('Не могу добавить в архив ' . $zip->errorInfo(true));

                        // Удаляем файл с базой
                    } else {
                        unlink($this->backup_dir . $this->Config->db_name . ".sql");
                    }

                    $this->Design->assign('message_success', 'created');
                    break;
                }

                    // Восстанавливаем Бэкап
                case 'restore': {
                    $name = $this->Request->post('name');
                    $archive_path = $this->backup_dir . $name;
                    $zip = new PclZip($archive_path);

                    $this->clean_dir($this->Config->root_dir . 'files');

                    if (!$zip->extract(PCLZIP_OPT_PATH, $this->Config->root_dir, PCLZIP_OPT_BY_PREG, "/^files\//", PCLZIP_CB_POST_EXTRACT, 'myPostExtractCallBack')) {
                        trigger_error('Не могу разархивировать ' . $zip->errorInfo(true));
                    } elseif (!$zip->extract(PCLZIP_OPT_PATH, $this->backup_dir, PCLZIP_OPT_BY_NAME, $this->Config->db_name . ".sql")) {
                        trigger_error('Не могу разархивировать ' . $zip->errorInfo(true));
                    } elseif (!is_readable($this->backup_dir . $this->Config->db_name . ".sql")) {
                        trigger_error('Не могу прочитать файл ' . $this->Config->db_name . ".sql");
                    } else {
                        $this->Database->restore($this->backup_dir . $this->Config->db_name . ".sql");
                        unlink($this->backup_dir . $this->Config->db_name . ".sql");
                        $this->Design->assign('message_success', 'restored');
                    }
                    break;
                }

                    // Удаляем Бэкап
                case 'delete': {
                    $names = $this->Request->post('check');
                    foreach ($names as $name) {
                        unlink($this->backup_dir . $name);
                    }
                    break;
                }
            }
        }

        $backup_files = glob($this->backup_dir . "*.zip");
        $backups = array();
        if (is_array($backup_files)) {
            foreach ($backup_files as $backup_file) {
                $backup = new stdClass();
                $backup->name = basename($backup_file);
                $backup->size = filesize($backup_file);
                $backups[] = $backup;
            }
        }

        $backups = array_reverse($backups);
        if (!is_writable($this->backup_dir)) {
            $this->Design->assign('message_error', 'no_permission');
        }

        $this->Design->assign('backup_dir', $this->backup_dir);
        $this->Design->assign('backups', $backups);

        return $this->Design->fetch('settings/backup.tpl');
    }


    private function clean_dir($path)
    {
        $path = rtrim($path, '/') . '/';
        $handle = opendir($path);

        while (false !== ($file = readdir($handle))) {
            if ($file != "." and $file != ".." and $file != "backup") {
                $fullpath = $path . $file;
                if (is_dir($fullpath)) {
                    $this->clean_dir($fullpath);
                    rmdir($fullpath);
                } else {
                    unlink($fullpath);
                }
            }
        }

        closedir($handle);
    }
}


function myPostExtractCallBack($p_event, &$p_header)
{

    // проверяем успешность распаковки
    if ($p_header['status'] == 'ok') {

        // Меняем права доступа
        @chmod($p_header['filename'], 0777);
    }
    return 1;
}


function myCallBack($p_event, &$p_header)
{
    // Example: files/originals/img_20200429_200303.jpg
    $file_name = $p_header['stored_filename'];

    // пропускаем оригинальные фото товаров
    //if (preg_match('/^files\/originals\/.+/i', $file_name))
    //	return 0;

    // пропускам resize фотографий (фото генерируются автоматически из оригинала)
    if (preg_match('/^files\/resize\/.+/i', $file_name)) {
        return 0;
    }

    // пропускаем файлы в папке backup
    if (preg_match('/^files\/backup\/.+/i', $file_name)) {
        return 0;
    }

    return 1;
}
