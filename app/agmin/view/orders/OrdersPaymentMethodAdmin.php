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

class OrdersPaymentMethodAdmin extends Auth
{
    private $allowed_image_extentions = array('png');

    public function fetch()
    {

        $payment_method = new stdClass();
        $payment_settings = array();
        $payment_deliveries = array();

        // Проверять права на изменение данных
        // Типы прав находятся в файле Auth.php
        $data_permissions = array(
            'orders_payment' => array(
                'id' => 'integer',
                'name' => 'string',
                'public_name' => 'string',
                'enabled' => 'boolean',
                'enabled_public' => 'boolean',
                'currency_id' => 'integer',
                'comment' => 'string',
                'module' => 'string',
                'description' => 'string',
                'finance_purse_id' => 'integer'
            )
        );

        $payment_modules = $this->OrdersPayment->getPaymentModules();

        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $payment_method = $this->postDataAcces($data_permissions);

            if (!empty($this->Request->post('payment_settings'))) {
                $payment_settings_arr = $this->Request->post('payment_settings');
            }

            if (!empty($this->Request->post('payment_deliveries'))) {
                $payment_deliveries = $this->Request->post('payment_deliveries');
            }

            if (empty($payment_method->id)) {
                $payment_method->id = $this->OrdersPayment->addPaymentMethod($payment_method);
                $this->Design->assign('message_success', 'added');
            } else {
                $this->OrdersPayment->updatePaymentMethod($payment_method->id, $payment_method);
                $this->Design->assign('message_success', 'updated');
            }

            // Если есть модуль оплаты, собираем его настройки
            if (!empty($payment_method->module)) {

                if (!empty($payment_method->module) and !empty($payment_modules[$payment_method->module])) {
                    $payment_module = $payment_modules[$payment_method->module];

                    foreach ($payment_module->settings as $module_setting) {
                        if (!empty($module_setting->type) and $module_setting->type == "file") {

                            // Upload
                            // tmp_name - file path
                            // name - file name
                            $temp_file_name = $this->Request->files($module_setting->variable, 'tmp_name');
                            $new_file_name =  "files/watermark/". $module_setting->variable . "_" . $payment_method->module . "_" . $payment_method->id . ".png";

                            if (!empty($temp_file_name) && in_array(pathinfo($this->Request->files($module_setting->variable, 'name'), PATHINFO_EXTENSION), $this->allowed_image_extentions)) {
                                if (@move_uploaded_file($temp_file_name, $this->Config->root_dir . $new_file_name)) {
                                    $payment_settings_arr[$module_setting->variable] = $new_file_name;
                                }
                            } elseif (file_exists($this->Config->root_dir . $new_file_name)) {
                                $payment_settings_arr[$module_setting->variable] = $new_file_name;
                            }

                        }
                    }
                }

                $this->OrdersPayment->updatePaymentSettings($payment_method->id, $payment_settings_arr);
                $this->OrdersPayment->updatePaymentDeliveries($payment_method->id, $payment_deliveries);
            }
        }


        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($payment_method))) {

            $payment_method = $this->OrdersPayment->getPaymentMethod($id);

            if (empty($payment_method->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }

            $payment_settings = $this->OrdersPayment->getPaymentMethodSettings($id);
            $payment_deliveries = $this->OrdersPayment->getPaymentDeliveries($id); # Связанные способы доставки
        }


        $deliveries = $this->OrdersDelivery->getDeliveryMethods();
        $purses = $this->Finance->getPurses();
        $currencies = $this->Money->getCurrencies(array('enabled' => 1));

        $this->Design->assign('deliveries', $deliveries);
        $this->Design->assign('purses', $purses);
        $this->Design->assign('payment_modules', $payment_modules);
        $this->Design->assign('currencies', $currencies);
        $this->Design->assign('payment_deliveries', $payment_deliveries);
        $this->Design->assign('payment_method', $payment_method);
        $this->Design->assign('payment_settings', $payment_settings);


        return $this->Design->fetch('orders/orders_payment_method.tpl');
    }
}
