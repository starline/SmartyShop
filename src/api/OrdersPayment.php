<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 2.5
 *
 */

namespace GoodGin;

class OrdersPayment extends GoodGin
{
    /**
     * @param array $filter
     */
    public function getPaymentMethods(array $filter = array())
    {
        $delivery_filter = '';
        if (!empty($filter['delivery_id'])) {
            $delivery_filter = $this->Database->placehold('AND id in (SELECT payment_method_id FROM __orders_delivery_payment dp WHERE dp.delivery_id=?)', intval($filter['delivery_id']));
        }

        $enabled_filter = '';
        if (!empty($filter['enabled'])) {
            $enabled_filter = $this->Database->placehold('AND enabled=?', intval($filter['enabled']));
        }

        $enabled_public_filter = '';
        if (!empty($filter['enabled_public'])) {
            $enabled_public_filter = $this->Database->placehold('AND enabled_public=?', intval($filter['enabled_public']));
        }

        $query =
            "SELECT
			 	*
			FROM 
				__orders_payment_methods 
			WHERE 
				1 
				$delivery_filter 
				$enabled_filter 
				$enabled_public_filter 
			ORDER BY 
				position
			";

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Выбираем информацию о способе оплаты
     * @param int $id
     */
    public function getPaymentMethod(int $id = null): object|bool
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold(
            "SELECT 
                p.id, 
                p.name, 
                p.public_name, 
                p.description, 
                p.module, # Module (FopUA, BankCard)
                p.comment, 
                p.settings, 
                p.currency_id, 
                p.finance_purse_id, 
                p.position, 
                p.enabled, 
                p.enabled_public
            FROM 
                __orders_payment_methods as p 
            WHERE 
                p.id=? 
            LIMIT 
                1",
            intval($id)
        );

        $this->Database->query($query);
        $payment_method = $this->Database->result();

        // Преобразовывем Settings json в object
        if (!empty($payment_method->settings)) {
            $payment_method->settings = (object) unserialize($payment_method->settings);
        }

        return $payment_method;
    }


    /**
     * Выбираем настройки способа оплаты
     * @param int $method_id
     * @return object
     */
    public function getPaymentMethodSettings(int $id = null): object|bool
    {
        if (empty($id)) {
            false;
        }

        $query = $this->Database->placehold("SELECT settings FROM __orders_payment_methods WHERE id=? LIMIT 1", intval($id));
        $this->Database->query($query);
        $settings = $this->Database->result('settings'); # json

        // преобразовывем json в object
        return (object) unserialize($settings);
    }


    /**
     * Выбираем модули доставки
     * Переменные в файле settings.xml
     * tax - % налога, платит покупатель, будет включен в общую стоимось заказа
     * tax_inside - % налога, платит продавец, будет вычтен из общей стоимости заказа
     * fee - % комиссия сервиса, платит продавец, будет вычтен из обшей стоимости заказа
     * fee_inside - % комиссия сервиса, платит продавец, будет вычтена из обзей стоимости заказа
     * fee_fix_inside - фиксированый платеж за сервис, платит продавец, будет вычтена из общей стоимости заказа
     */
    public function getPaymentModules()
    {

        $modules_dir = $this->Config->payment_dir;

        $modules = array();
        $handler = opendir($modules_dir);

        while ($dir = readdir($handler)) {
            $dir = preg_replace("/[^A-Za-z0-9]+/", "", $dir);
            if (!empty($dir) && $dir != "." && $dir != ".." && is_dir($modules_dir . $dir)) {

                if (is_readable($modules_dir . $dir . '/settings.xml') && $xml = simplexml_load_file($modules_dir . $dir . '/settings.xml')) {

                    $module = new \stdClass();
                    $module->name = (string)$xml->name;
                    $module->settings = array();

                    foreach ($xml->settings as $setting) {
                        $module->settings[(string)$setting->variable] = new \stdClass();
                        $module->settings[(string)$setting->variable]->name = (string)$setting->name;
                        $module->settings[(string)$setting->variable]->variable = (string)$setting->variable;
                        $module->settings[(string)$setting->variable]->variable_options = array();

                        foreach ($setting->options as $option) {
                            $module->settings[(string)$setting->variable]->options[(string)$option->value] = new \stdClass();
                            $module->settings[(string)$setting->variable]->options[(string)$option->value]->name = (string)$option->name;
                            $module->settings[(string)$setting->variable]->options[(string)$option->value]->value = (string)$option->value;
                        }

                        // input file
                        if (!empty($setting->type)) {
                            $module->settings[(string)$setting->variable]->type = (string)$setting->type;
                        }
                    }
                    $modules[$dir] = $module;
                }
            }
        }
        closedir($handler);
        return $modules;
    }


    /**
     * Выводим форму оплаты
     * Модуль оплаты находиться в modules/payments
     * В Smarty подключается как плагин
     * @param array $params
     */
    public function getPaymentModuleHtml(array $params)
    {
        $module_name = preg_replace("/[^A-Za-z0-9]+/", "", $params['module']);
        $modules_dir = $this->Config->payment_dir;

        $form = '';
        if (!empty($module_name) && is_file($modules_dir . "$module_name/$module_name.php")) {
            include_once($modules_dir . "$module_name/$module_name.php");
            $module = new $module_name();

            $form = $module->checkout_form($params['order_id'], $params['view_type']);
        }
        return $form;
    }


    /**
     * Get payment delivert methods
     * @param int $id
     */
    public function getPaymentDeliveries(int $id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("SELECT delivery_id FROM __orders_delivery_payment WHERE payment_method_id=?", intval($id));
        $this->Database->query($query);
        return $this->Database->results('delivery_id');
    }


    public function updatePaymentMethod(int $id, $payment_method)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("UPDATE __orders_payment_methods SET ?% WHERE id in(?@)", $payment_method, (array)$id);
        $this->Database->query($query);
        return $id;
    }


    /**
     * Update payment module settings
     * @return int $id
     */
    public function updatePaymentSettings(int $id, $settings)
    {
        if (empty($id)) {
            return false;
        }

        if (!is_string($settings)) {
            $settings = serialize($settings);
        }
        $query = $this->Database->placehold("UPDATE __orders_payment_methods SET settings=? WHERE id in(?@) LIMIT 1", $settings, (array)$id);
        $this->Database->query($query);
        return $id;
    }


    /**
     * Устанавливаем доступные способы доставки для платежа
     * @param int $id - ID способа оплаты
     * @param array $deliveries_ids - array(ID) способов доставки
     */
    public function updatePaymentDeliveries(int $id, array $deliveries_ids)
    {

        if (empty($id)) {
            return false;
        }

        // Удаляем ранее установленные настройки
        $query = $this->Database->placehold("DELETE FROM __orders_delivery_payment WHERE payment_method_id=?", intval($id));
        $this->Database->query($query);

        if (is_array($deliveries_ids)) {
            foreach ($deliveries_ids as $d_id) {
                $this->Database->query("INSERT INTO __orders_delivery_payment SET payment_method_id=?, delivery_id=?", $id, $d_id);
            }
        }
        return true;
    }


    /**
     * Добавляем способ оплаты
     * @param object $payment_method
     */
    public function addPaymentMethod(object $payment_method)
    {
        $payment_method = $this->Misc->cleanEntityId($payment_method);

        $query = $this->Database->placehold(
            "INSERT INTO 
                __orders_payment_methods
		    SET 
                ?%",
            $payment_method
        );

        if (!$this->Database->query($query)) {
            return false;
        }

        $id = $this->Database->getInsertId();

        $this->Database->query("UPDATE __orders_payment_methods SET position=id WHERE id=?", $id);
        return $id;
    }


    /**
     * Delete payment method
     */
    public function deletePaymentMethod($id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("DELETE FROM __orders_payment_methods WHERE id=? LIMIT 1", intval($id));
        return $this->Database->query($query);
    }
}
