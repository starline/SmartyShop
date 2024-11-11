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

class OrdersLabelAdmin extends Auth
{
    public function fetch()
    {

        $label = new stdClass();
        $label->color = 'ffffff';


        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions = array(
            'orders_labels' => array(
                'id' => 'integer',
                'name' => 'string',
                'color' => 'string',
                'enabled' => 'boolean',
                'in_filter' => 'boolean'
            )
        );

        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('POST')) {

            $label = $this->postDataAcces($data_permissions);
            $label = $this->Misc->trimEntityProps($label, array('name'));

            if (empty($label->id)) {
                $label->id = $this->OrdersLabels->add_label($label);
                $this->Design->assign('message_success', 'added');
            } else {
                $this->OrdersLabels->update_label($label->id, $label);
                $this->Design->assign('message_success', 'updated');
            }
        }

        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($label))) {

            $label = $this->OrdersLabels->get_label(intval($id));

            if (empty($label->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }

        }

        $this->Design->assign('label', $label);
        return $this->Design->fetch('orders/orders_label.tpl');
    }
}
