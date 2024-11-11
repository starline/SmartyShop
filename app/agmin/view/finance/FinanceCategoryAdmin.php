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

class FinanceCategoryAdmin extends Auth
{
    public function fetch()
    {

        $category = new stdClass();
        $entity_options = array(
            "id" => "integer",
            "type" => "integer",
            "name" => "string",
            "comment" => "string"
        );

        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions =  array(
            "finance" => $entity_options
        );


        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $category = $this->postDataAcces($data_permissions);

            //print_r($category);

            if (empty($category->id)) {
                $category->id = $this->Finance->add_category($category);
                $this->Design->assign('message_success', 'added');
            } else {
                $this->Finance->update_category($category->id, $category);
                $this->Design->assign('message_success', 'updated');
            }
        }


        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($category))) {
            $category = $this->Finance->get_category($id);

            if (empty($category->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }
        }

        $this->Design->assign('category', $category);

        return $this->Design->fetch('finance/category.tpl');
    }
}
