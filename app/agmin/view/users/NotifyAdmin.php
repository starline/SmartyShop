<?php

/**
 * GoodGin CMS - Best of gins
 * @author Andi Huga
 *
 *	User Notyfi
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class notifyAdmin extends Auth
{
    public function fetch()
    {
        $notify = new stdClass();
        $notify_settings = array();

        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions =  array(
            "users_notify" => array(
                "id" => "integer",
                'name' => 'string',
                "enabled" => 'boolean',
                'comment' => 'string',
                'module' => 'string'
            )
        );

        $notify_modules = $this->UsersNotify->getNotifyModules();

        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $notify = $this->postDataAcces($data_permissions);

            if (!empty($this->Request->post('notify_settings'))) {
                $notify_settings_arr = $this->Request->post('notify_settings');
            }

            if (empty($notify->id)) {
                $notify->id = $this->UsersNotify->addNotify($notify);
                $this->Design->assign('message_success', 'added');
            } else {
                $notify->id = $this->UsersNotify->updateNotify($notify->id, $notify);
                $this->Design->assign('message_success', 'updated');
            }

            // Если есть модуль оповещения, собираем его настройки
            if (!empty($notify->module)) {

                if (!empty($notify->module) and !empty($notify_modules[$notify->module])) {
                    $notify_module = $notify_modules[$notify->module];

                    foreach ($notify_module->settings as $module_setting) {
                        if (!empty($module_setting->type) and $module_setting->type == "file") {

                            // Upload
                            // tmp_name - file path
                            // name - file name
                            $temp_file_name = $this->Request->files($module_setting->variable, 'tmp_name');
                            $new_file_name =  "files/watermark/". $module_setting->variable . "_" . $notify->module . "_" . $notify->id . ".png";

                            if (!empty($temp_file_name) && in_array(pathinfo($this->Request->files($module_setting->variable, 'name'), PATHINFO_EXTENSION), $this->allowed_image_extentions)) {
                                if (@move_uploaded_file($temp_file_name, $this->Config->root_dir . $new_file_name)) {
                                    $notify_settings_arr[$module_setting->variable] = $new_file_name;
                                }
                            } elseif (file_exists($this->Config->root_dir . $new_file_name)) {
                                $notify_settings_arr[$module_setting->variable] = $new_file_name;
                            }

                        }
                    }
                }

                $this->UsersNotify->updateNotifySettings($notify->id, $notify_settings_arr);
            }
        }


        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($notify))) {

            $notify = $this->UsersNotify->getNotify(intval($id));

            if (empty($notify->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }

            $notify_settings = $this->UsersNotify->getNotifySettings($id);
        }


        $this->Design->assign('notify', $notify);
        $this->Design->assign('notify_modules', $notify_modules);
        $this->Design->assign('notify_settings', $notify_settings);


        return $this->Design->fetch('users/notify.tpl');
    }
}
