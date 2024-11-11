<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 *
 * MerchantAdmin
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class MerchantAdmin extends Auth
{
    public function fetch()
    {
        $merchant = new stdClass();

        $data_permissions = array(
            "products_merchants" => array("id" => "integer", "name" => "string")
        );


        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $merchant = $this->postDataAcces($data_permissions);

            if (empty($merchant->id)) {
                if ($merchant->id = $this->ProductsMerchants->addPriceList($merchant)) {
                    $this->Design->assign('message_success', 'added');
                }
            } else {
                if ($this->ProductsMerchants->updatePriceList($merchant->id, $merchant)) {
                    $this->Design->assign('message_success', 'updated');
                }
            }
        }


        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($merchant))) {
            $merchant = $this->ProductsMerchants->getPriceList($id);

            if (empty($merchant->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }
        }

        $this->Design->assign('merchant', $merchant);

        return $this->Design->fetch('products/merchant.tpl');
    }
}
