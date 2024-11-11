<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 *
 * Для оператора NovaPoshta.ua
 *
 * Документация Новой Почты
 * https://devcenter.novaposhta.ua/
 *
 */

use GoodGin\GoodGin;

class NovaPoshta extends GoodGin
{
    // Ключ к API
    private $apiKey = '';
    private $api_url = 'https://api.novaposhta.ua/v2.0/json/';


    /**
     * Выводим форму
     *
     */
    public function checkout_form($order_id, $view_type)
    {

        $order = $this->Orders->getOrder((int)$order_id);
        $delivery_method = $this->OrdersDelivery->getDeliveryMethod($order->delivery_id);

        // Проверим сущестование файла
        if (!empty($view_type)) {
            $file_path = $this->Config->delivery_dir . $delivery_method->module . "/" . $delivery_method->module . "_" . "$view_type.tpl";
            if (is_file($file_path)) {
                return $this->Design->fetch($file_path);
            }
        }

        return false;
    }


    /**
     * Выбираем информацию о ТТН
     */
    public function get_delivery_info($order_id)
    {

        $result = "";

        // Выбрать данные заказа
        $order = $this->Orders->getOrder($order_id);

        if (!empty($order->delivery_note)) {

            $phone = !empty($order->phone) ? $order->phone : "";

            // Выбрать данные по ТТН
            $NPresult = $this->checkTracking($order->delivery_note, $phone);

            if (isset($NPresult['success']) and $NPresult['success'] == "true") {
                $DATAresult = $NPresult["data"][0];

                if (isset($DATAresult['CitySender'])) {
                    $result = 	$DATAresult['CitySender'] . ' - ' . $DATAresult['DateCreated'] . '<br>' .
                        $DATAresult['CityRecipient'] . ' - ' . $DATAresult['ActualDeliveryDate'] . '<br>' .
                        'Предварительная дата доставки - ' . $DATAresult['ScheduledDeliveryDate'] . '<br>' .
                        ((isset($DATAresult['DateFirstDayStorage'])) ? 'Платное хранение с ' . $DATAresult['DateFirstDayStorage'] . '<br>' : '') .
                        "<b>" . ((stripos($DATAresult['Status'], "Відправлення отримано") !== false) ? "Відправлення <b class='color_green'>отримано</b>" : $DATAresult['Status']) . '</b> - ' . $DATAresult['RecipientDateTime'] . '<br>' .
                        'Кол-во мест - ' . $DATAresult['SeatsAmount'] . '<br>' .
                        'Итоговая стоимость доставки - ' . $DATAresult['DocumentCost'] . ' грн <br>' .
                        'Оценочная стоимость - ' . $DATAresult['AnnouncedPrice'] . ' грн <br>' .
                        'Отправка от ' . $DATAresult['CounterpartySenderDescription'] . '<br>' .
                        'Наложеный платеж - <b>' . $DATAresult['AfterpaymentOnGoodsCost'] . ' грн </b>' .
                        ((isset($DATAresult['ExpressWaybillPaymentStatus']) and $DATAresult['ExpressWaybillPaymentStatus'] == "Payed") ? '- <b class="color_green">Оплачено</b>' : '- Не оплачено') . '<br>';

                    if (isset($NPresult['warnings'][0])) {
                        foreach ($NPresult['warnings'][0] as $warning) {
                            $result .= $warning . '<br>';
                        }
                    }
                } else {
                    $result = $DATAresult['Status'];
                }
            } else {
                $result = "Возникла непредвиденная ошибка";
            }

            // Выводим весь масив с данными
            //$result = $NPresult;

            // Добавляем дату //22-09-2021
            $result = $result . "</br> Обновлено - " . date("d-m-Y H:i:s");

            // Записываем информацию в базу
            $this->Orders->update_order($order->id, array('delivery_info' => $result), false);

            return $result;
        }
    }


    /**
     * Запрос к api НоваяПочта
     */
    public function checkTracking($track = null, $phone = "")
    {

        // Очистить пробелы вконце и вначале
        $track = trim($track);

        $property["Documents"][] = array(
            "DocumentNumber" => strval($track),
            "Phone" => $phone
        );

        $params["apiKey"] = $this->apiKey;
        $params["modelName"] = "TrackingDocument";
        $params["calledMethod"] = "getStatusDocuments";
        $params["methodProperties"] = $property;

        $res = file_get_contents($this->api_url, false, stream_context_create(array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/json',
                'content' => json_encode($params)
            )
        )));

        return json_decode($res, true);
    }
}
