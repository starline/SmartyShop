<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 2.5
 *
 * Класс для работы с доставкой
 *
 */

namespace GoodGin;

class OrdersDelivery extends GoodGin
{
    /**
     * Выбираем информацию об способе доставки
     * @param $id - ID способа доставки
     */
    public function getDeliveryMethod($id)
    {
        $query = $this->Database->placehold(
            "SELECT 
				*
			FROM 
				__orders_delivery 
			WHERE 
				id=? 
			LIMIT 
				1",
            intval($id)
        );

        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Выбираем все способы доставки
     * @param $filter
     */
    public function getDeliveryMethods(array $filter = array())
    {

        // По умолчанию
        $enabled_filter = '';

        if (!empty($filter['enabled'])) {
            $enabled_filter = $this->Database->placehold('AND enabled=?', intval($filter['enabled']));
        }

        $query = "SELECT id, name, description, free_from, price, enabled, position, separate_payment
					FROM __orders_delivery WHERE 1 $enabled_filter ORDER BY position";

        $this->Database->query($query);

        return $this->Database->results();
    }


    /**
     * Обновляем данные способа доставки
     * @param $id
     * @param $delivery
     */
    public function update_delivery($id, $delivery)
    {
        $query = $this->Database->placehold("UPDATE __orders_delivery SET ?% WHERE id in(?@)", $delivery, (array)$id);
        return $this->Database->query($query);
    }


    /**
     * Добавляем новый способ доставки
     * @param $delivery
     */
    public function add_delivery($delivery)
    {
        $delivery = $this->Misc->cleanEntityId($delivery);

        $query = $this->Database->placehold(
            "INSERT INTO 
                __orders_delivery
		    SET 
                ?%",
            $delivery
        );

        if ($this->Database->query($query)) {
            $id = $this->Database->getInsertId();
            $this->Database->query("UPDATE __orders_delivery SET position=id WHERE id=?", intval($id));
            return $id;
        }
        return false;
    }


    // Удаляем способ доставки
    public function delete_delivery($id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("DELETE FROM __orders_delivery WHERE id=? LIMIT 1", intval($id));
        return $this->Database->query($query);
    }


    /**
     * Выбираем способы оплаты для выбранной доставки
     */
    public function get_delivery_payments($id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("SELECT payment_method_id FROM __orders_delivery_payment WHERE delivery_id=?", intval($id));
        $this->Database->query($query);
        return $this->Database->results('payment_method_id');
    }


    // Обновляем способы оплаты для выбранной доставки
    public function update_delivery_payments($id, $payment_methods_ids)
    {
        $query = $this->Database->placehold("DELETE FROM __orders_delivery_payment WHERE delivery_id=?", intval($id));
        $this->Database->query($query);
        if (is_array($payment_methods_ids)) {
            foreach ($payment_methods_ids as $p_id) {
                $this->Database->query("INSERT INTO __orders_delivery_payment SET delivery_id=?, payment_method_id=?", $id, $p_id);
            }
        }
    }


    /**
     * Выбираем модуль доставки
     */
    public function get_delivery_modules()
    {
        $modules_dir = $this->Config->delivery_dir;

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
                    }
                    $modules[$dir] = $module;
                }
            }
        }
        closedir($handler);
        return $modules;
    }


    /**
     * Выводим модуль доставки
     * Модуль находиться в src/modules/delivery
     * В Smarty подключается как плагин
     * @param array $params
     */
    public function getDeliveryModuleHtml(array $params)
    {
        $module_name = preg_replace("/[^A-Za-z0-9]+/", "", $params['module']);
        $form = '';
        if (!empty($module_name) && is_file($this->Config->delivery_dir . "$module_name/$module_name.php")) {
            include_once($this->Config->delivery_dir . "$module_name/$module_name.php");
            $module = new $module_name();

            $form = $module->checkout_form($params['order_id'], $params['view_type']);
        }
        return $form;
    }


    /**
     * Выбираем настройки модуля доставки
     * @param int $id - ID способа доставки
     */
    public function get_delivery_settings(int $id)
    {
        $query = $this->Database->placehold("SELECT settings FROM __orders_delivery WHERE id=? LIMIT 1", intval($id));
        $this->Database->query($query);
        $settings = $this->Database->result('settings');

        $settings = unserialize($settings);
        return $settings;
    }


    /**
     * Обновляем настройки модуля доставки
     * @param $id
     * @param $settings
     */
    public function update_delivery_settings($id, $settings)
    {
        if (!is_string($settings)) {
            $settings = serialize($settings);
        }

        $query = $this->Database->placehold("UPDATE __orders_delivery SET settings=? WHERE id in(?@) LIMIT 1", $settings, (array)$id);
        $this->Database->query($query);
        return $id;
    }
}
