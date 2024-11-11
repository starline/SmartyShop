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

class OrdersDeliveryAdmin extends Auth
{
    public function fetch()
    {

        $delivery = new stdClass();
        $delivery_payments = array();
        $delivery_settings = array();

        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions = array(
            'orders_delivery' => array(
                'id' => 'integer',
                'name' => 'string',
                'enabled' => 'boolean',
                'description' => 'string',
                'price' => 'float',
                'free_from' => 'float',
                'separate_payment' => 'boolean',
                'module' => 'string',
                'finance_purse_id' => 'integer'
            )
        );

        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $delivery = $this->postDataAcces($data_permissions);
            $delivery = $this->Misc->trimEntityProps($delivery, array('name'));

            if (!$delivery_payments = $this->Request->post('delivery_payments')) {
                $delivery_payments = array();
            }

            if (!$delivery_settings = $this->Request->post('delivery_settings')) {
                $delivery_settings = array();
            }

            if (empty($delivery->id)) {
                $delivery->id = $this->OrdersDelivery->add_delivery($delivery);
                $this->Design->assign('message_success', 'added');
            } else {
                $this->OrdersDelivery->update_delivery($delivery->id, $delivery);
                $this->Design->assign('message_success', 'updated');
            }

            if ($delivery->id) {
                $this->OrdersDelivery->update_delivery_payments($delivery->id, $delivery_payments);
                $this->OrdersDelivery->update_delivery_settings($delivery->id, $delivery_settings);
            }
        }

        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($delivery))) {

            $delivery = $this->OrdersDelivery->getDeliveryMethod($id);

            if (empty($delivery->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }


            $delivery_settings = $this->OrdersDelivery->get_delivery_settings($id);
            $delivery_payments = $this->OrdersDelivery->get_delivery_payments($id);
        }

        // Связанные способы оплаты
        $payment_methods = $this->OrdersPayment->getPaymentMethods();

        $delivery_modules = $this->OrdersDelivery->get_delivery_modules();
        $finance_purses = $this->Finance->getPurses();

        $this->Design->assign('delivery', $delivery);
        $this->Design->assign('delivery_settings', $delivery_settings);
        $this->Design->assign('delivery_payments', $delivery_payments);
        $this->Design->assign('payment_methods', $payment_methods);
        $this->Design->assign('delivery_modules', $delivery_modules);
        $this->Design->assign('finance_purses', $finance_purses);


        return $this->Design->fetch('orders/orders_delivery.tpl');
    }
}
