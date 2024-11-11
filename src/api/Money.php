<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 2.4
 *
 */

namespace GoodGin;

class Money extends GoodGin
{
    private $currencies = array();
    private $currency;

    public function __construct()
    {
        parent::__construct();
        $this->initCurrencies();
    }


    /**
     * Выбираем все валюты $this->currencies
     * Определяем оcновную валюту $this->currency
     */
    private function initCurrencies()
    {

        // Выбираем из базы валюты
        $query = $this->Database->placehold(
            "SELECT 
                id, 
                name, 
                sign, 
                code, 
                rate_from, 
                rate_to, 
                cents, 
                position, 
                enabled 
            FROM 
                __finance_currencies 
            ORDER BY 
                position"
        );

        $this->Database->query($query);
        $results = $this->Database->results();

        foreach ($results as $c) {
            $this->currencies[$c->id] = $c;
        }

        // Основная валюта
        $this->currency = reset($this->currencies);
    }


    /**
     * Get currencies
     */
    public function getCurrencies($filter = array())
    {
        $currencies = array();
        foreach ($this->currencies as $id => $currency) {
            if ((isset($filter['enabled']) && $filter['enabled'] == 1 && $currency->enabled) || empty($filter['enabled'])) {
                $currencies[$id] = $currency;
            }
        }

        return $currencies;
    }


    /**
     * Get currency
     * @param int|string $id - if NULL will return main currency
     */
    public function getCurrency(int|string $id = null)
    {
        // по id
        if (!empty($id) && is_numeric($id)) {
            if (isset($this->currencies[intval($id)])) {
                return $this->currencies[intval($id)];
            }
        }

        // по ISO коду
        if (!empty($id) && is_string($id)) {
            foreach ($this->currencies as $currency) {
                if ($currency->code == $id) {
                    return $currency;
                }
            }
        }

        // Return main currency
        return $this->getMainCurrency();
    }


    /**
     * Get main currency
     */
    public function getMainCurrency()
    {
        return $this->currency;
    }


    /**
     * Добавляем валюту
     * @param $currency
     */
    public function addCurrency($currency)
    {
        $currency = $this->Misc->cleanEntityId($currency);

        $query = $this->Database->placehold(
            "INSERT INTO 
                __finance_currencies
		    SET 
                ?%",
            $currency
        );

        if (!$this->Database->query($query)) {
            return false;
        }

        $id = $this->Database->getInsertId();

        $this->Database->query("UPDATE __finance_currencies SET position=id WHERE id=?", $id);
        $this->initCurrencies();

        return $id;
    }


    /**
     * Update currency
     */
    public function updateCurrency($id, $currency)
    {
        $query = $this->Database->placehold(
            "UPDATE 
                __finance_currencies
			SET 
                ?%
			WHERE 
                id in (?@)",
            $currency,
            (array)$id
        );

        if (!$this->Database->query($query)) {
            return false;
        }

        $this->initCurrencies();
        return $id;
    }


    /**
     * Delete currency
     */
    public function deleteCurrency($id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("DELETE FROM __finance_currencies WHERE id=? LIMIT 1", intval($id));
        $this->Database->query($query);

        $this->initCurrencies();
    }


    /**
     * Конвертируем в другую валюту
     * Установлен плагин в Smarty(class Design)
     * @param $amount
     * @param $format - Форматировать цены соласно настройкам сайта
     * @param $currency_to_id - id валюты в которую перевести
     * @param $currency_from_id - id валюты с которой переводим. Если не задана, берется основная валюта
     */
    public function priceConvert($amount, $currency_to_id = null, $format = true, $currency_from_id = null)
    {

        if (empty($amount)) {
            return 0;
        }

        // Выбираем данные по валюте
        if (!is_null($currency_to_id)) {
            $currency_to = $this->getCurrency($currency_to_id);
        } elseif (isset($_SESSION['currency_id'])) {
            $currency_to = $this->getCurrency($_SESSION['currency_id']);
        } else {
            $currency_to = current($this->getCurrencies(array('enabled' => 1)));
        }

        if (!is_null($currency_from_id)) {
            $currency_from = $this->getCurrency($currency_from_id);
        }

        $result = $amount;

        // Переводим между валютами, кроме основной
        if (!empty($currency_from) && !empty($currency_to) && $currency_from->id != $this->currency->id) {

            // Если переводим любую валюту В основную валюту
            if ($currency_to->id == $this->currency->id) {
                $result = $amount * $currency_from->rate_to / $currency_from->rate_from;

                // Переводим между валютами
            } else {
                $result = $amount / $currency_from->rate_from * $currency_from->rate_to; // переводим в основную валюту
                $result = $result * $currency_to->rate_from / $currency_to->rate_to; // переводим в нужную валюту
            }

            // Переводим ИЗ основной валюты. Eсли $currency_from = основная валюта
        } elseif (!empty($currency_to)) {

            // Умножим на курс валюты
            $result = $amount * $currency_to->rate_from / $currency_to->rate_to;
        }

        // Точность отображения, знаков после запятой.
        // Показывать копейки или нет .00
        $precision = $currency_to->cents ? 2 : 0;

        // есть знаки после запятой, оставляем отображение их
        $result_arr = explode('.', strval($result));
        if (!empty($result_arr[1]) and $precision == 0) {

            // обрезаем до 2х знаков
            $precision = (strlen($result_arr[1]) > 0) ? 2 : 0;
        }

        // Форматирование цены. Задается в настройках сайта
        // Пример 1 234.56
        if ($format) {
            $result = $this->priceFormat($result, $precision, true);
        } else {
            $result = round($result, $precision);
        }

        return $result;
    }


    /**
     * Форматируем показ цены
     * Установлен плагин в Smarty(class Design)
     */
    public function priceFormat($price, $precision = 2, $html = null, $decimals_point = null, $thousands_separator = null)
    {

        if (!isset($decimals_point)) {
            $decimals_point = $this->Settings->decimals_point;
        }

        if (!isset($thousands_separator)) {
            $thousands_separator = $this->Settings->thousands_separator;
        }

        $result = number_format($price, $precision, $decimals_point, $thousands_separator);

        if (isset($html)) {
            $price_arr = explode($decimals_point, $result);
            if ($precision > 0) {
                $result = "<span class='price_main'>" . $price_arr[0] . "</span><span class='price_decimal'>" . $decimals_point . $price_arr[1] . "</span>";
            } else {
                $result = "<span class='price_main'>" . $price_arr[0] . "</span>";
            }
        }

        return $result;
    }
}
