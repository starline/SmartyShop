<?php

/**
 * GoodGin CMS - Best of gins
 * @author Andi Huga
 *
 *	Users Group
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class GroupAdmin extends Auth
{
    public function fetch()
    {
        $group = new stdClass();

        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions =  array(
            "users_groups_edit" => array(
                "id" => "integer",
                'name' => 'string',
                "discount" => "float"
            )
        );

        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $group = $this->postDataAcces($data_permissions);

            if (empty($group->id)) {
                $group->id = $this->UsersGroups->addGroup($group);
                $this->Design->assign('message_success', 'added');
            } else {
                $group->id = $this->UsersGroups->updateGroup($group->id, $group);
                $this->Design->assign('message_success', 'updated');
            }
        }

        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($group))) {
            $group = $this->UsersGroups->getGroup(intval($id));

            if (empty($group->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }
        }


        $this->Design->assign('group', $group);

        return $this->Design->fetch('users/group.tpl');
    }
}
