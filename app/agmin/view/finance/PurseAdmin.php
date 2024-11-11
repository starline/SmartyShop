<?php

/**
 * GoodGin CMS - Best of gins
 * 
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class PurseAdmin extends Auth
{
    private $entity_params = array(
        "id" => "integer",
        "amount" => "float",
        "enabled" => "boolean",
        "currency_id" => "integer",
        "name" => "string",
        "comment" => "string"
    );

    public function fetch()
    {

        $purse = new stdClass();

        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions =  array(
            "finance" => $this->entity_params
        );


        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $purse = $this->postDataAcces($data_permissions);

            //print_r($purse);

            if (empty($purse->id)) {
                if ($purse->id = $this->Finance->add_purse($purse)) {
                    $this->Design->assign('message_success', 'added');
                }
            } else {
                if ($this->Finance->update_purse($purse->id, $purse)) {
                    $this->Design->assign('message_success', 'updated');
                }
            }
        }


        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($purse))) {
            
            $purse = $this->Finance->get_purse($id);

            if (empty($purse->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }

            // Делаем сверку приходов и расходов кошелька
            $check_purse_amount = $this->Finance->check_purse_amount($purse->id);
            $this->Design->assign('check_purse_amount', $check_purse_amount);
        }


        //////////////////////
        //------- View Create
        /////////////////////
        else {
            $purse->amount = "0.00";
        }

        //  Выбрать валюту
        $currencies = $this->Money->getCurrencies(array('enabled' => 1));

        $this->Design->assign('currencies', $currencies);
        $this->Design->assign('purse', $purse);

        return $this->Design->fetch('finance/purse.tpl');
    }
}
