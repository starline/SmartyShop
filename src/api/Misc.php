<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 1.4
 *
 */

namespace GoodGin;

class Misc extends GoodGin
{
    /**
     * Очищаем номер телефона
     * @param $phone
     */
    public function clearPhoneNummber(String $phone): String
    {

        if (!empty($phone)) {

            // Убираем скобки, тире, пробелы
            $phone = str_replace([' ', '-', '(', ')'], '', $phone);

            // Добавляем +
            if (stripos($phone, "380") === 0) {
                $phone = "+" . $phone;
            }

            // Если вписали номер 0971234567 добавляем +38
            // если вначале 0 (0971234567) и 10 символов и нет +38
            if (stripos($phone, "+38") === false and stripos($phone, "0") === 0 and strlen($phone) == 10) {
                $phone = "+38" . $phone;
            }

            // Если вписали номер 97 123 45 67
            // Если 9 символов
            // Если начинается не на 0
            // если нет +38
            elseif (stripos($phone, "+38") === false and stripos($phone, "0") !== 0 and strlen($phone) == 9) {
                $phone = "+380" . $phone;
            }
        }

        return $phone;
    }


    /**
     * Очищаем цену от лишних знаков
     * @param $price
     */
    public function clearPrice(String $price): Float
    {
        if (!empty($price)) {

            // Убираем пробелы
            $price = str_replace(' ', '', $price);

            // Убираем тире
            $price = str_replace('-', '', $price);

            // заменяем , на .
            $price = str_replace(',', '.', $price);

            // Убираем пробел с Google Sheet
            $price = str_replace(' ', '', $price);
        }

        return floatval($price);
    }


    /**
     * Check type of variable
     * @param $var
     * @param $type
     */
    public function checkVarType($var, $type = null)
    {
        // Проверяет, является ли переменная числом или строкой, содержащей число
        if ($type == 'numeric') {
            if (is_numeric($var)) {
                return true;
            }
        }
    }


    /**
     * Проверяем ID сущности
     * @param object $entity
     */
    public function getEntityId($entity = null)
    {
        $id = null;

        // check id from GET/POST
        if (empty($id = $this->Request->getVar('id', 'integer'))) {

            // check entity id
            if (!empty($entity->id)) {
                $id = $entity->id;
            }
        }

        return $id;
    }


    /**
     * Получаем сущность с пустыми параметрами
     * @param object $entity
     * @param array $entity_params
     */
    public function getEmptyEntity($entity, $entity_params)
    {
        foreach ($entity_params as $param_name => $param_type) {
            $entity->$param_name = null;
        }
    }


    /**
     * Очищаем ID сущности
     * @param array|object $entity
     */
    public function cleanEntityId($entity)
    {
        $entity_object_copy = clone (object)$entity; // Клонируем Object, отвязываем от основного
        if (isset($entity_object_copy->id)) {
            unset($entity_object_copy->id);
        }
        return $entity_object_copy;
    }


    /**
     * Убираем пробелы в начале и в конце строки
     *  @param $entity
     */
    public function trimEntityProps($entity, $props)
    {
        foreach ($props as $prop) {
            if (!empty($entity->$prop)) {
                $entity->$prop = trim($entity->$prop);
            }
        }
        return $entity;
    }


    /**
     * Make redirect
     */
    public function makeRedirect($redirect_url, $redirect_type = "301")
    {
        switch ($redirect_type) {
            case '301':
                header('HTTP/1.1 301 Moved Permanently');
                break;
        }

        header('location: ' . $redirect_url);
        exit();
    }


    /**
     * Check messages in $_SESSION
     * @param $message_type
     */
    public function getSessionMessage($message_type)
    {
        if (!empty($_SESSION[$message_type])) {
            $message_val = $_SESSION[$message_type];
            unset($_SESSION[$message_type]);

            return $message_val;
        }
        return '';
    }


    /**
     * Формируем название модуля из названия сущности
     * @param $entity_name
     */
    public function getViewAdmin($entity_name)
    {
        switch ($entity_name) {
            case 'user':
                return 'UserAdmin';
                break;
            case 'wh_movement':
                return 'WarehouseMovementAdmin';
                break;
            case 'order':
                return 'OrderAdmin';
                break;
            default:
                return false;
                break;
        }
    }


    /**
     * Прроверяем параметры фильта на пустые значения
     * @param $filter
     * @param $params
     */
    public function check_filter_params($filter, $params)
    {
        foreach ($params as $param) {
            if (isset($filter[$param])) {
                if (empty($filter[$param])) {
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * isAjax
     *
     */
    public function isAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Преобразовываем массив в GET запрос
     */
    public function params_to_URL($params)
    {
        $pice = array();
        foreach ($params as $k => $v) {
            $pice[] = $k . '=' . urlencode($v);
        }
        return join('&', $pice);
    }


    /**
     * Создаем токен в 10 символов
     * Для картинок
     */
    public function getToken(string $text, int $length = 10)
    {
        $hash = md5($text . $this->Config->salt);

        // Сut hash
        return substr($hash, 0, $length);
    }


    /**
     * Check token for string
     * @param string $text
     * @param string $token
     */
    public function checkToken(string $text, string $token)
    {
        return $token === $this->getToken($text);
    }


    /**
     * Set COOKIE
     * @param string $name
     * @param array $data
     * @param int $days
     */
    public function setCookie(string $name, array|string $data, int $days = 360, string $dir = '/'): void
    {
        setcookie(
            $this->Config->cookie_prefix . $name,
            $data,
            time() + 3600 * 24 * $days, # how long
            $dir # catalog
        );
    }


    /**
     * Get COOKIE
     * @param string $name
     */
    public function getCookie(string $name): string
    {
        if (isset($_COOKIE[$this->Config->cookie_prefix . $name])) {
            return $_COOKIE[$this->Config->cookie_prefix . $name];
        }
        return false;
    }

    /**
     * Delete COOKIE
     * @param string $name
     */
    public function deleteCookie(string $name): void
    {
        unset($_COOKIE[$this->Config->cookie_prefix . $name]);
        $this->setCookie($name, '', -1);
    }


    /**
     * Преобразуем символы RU->EN
     * @param $text
     * @return $text
     */
    public function transliteration_ru_en($text = null)
    {
        if (is_null($text)) {
            return false;
        }

        $ru = explode('-', "А-а-Б-б-В-в-Ґ-ґ-Г-г-Д-д-Е-е-Ё-ё-Є-є-Ж-ж-З-з-И-и-І-і-Ї-ї-Й-й-К-к-Л-л-М-м-Н-н-О-о-П-п-Р-р-С-с-Т-т-У-у-Ф-ф-Х-х-Ц-ц-Ч-ч-Ш-ш-Щ-щ-Ъ-ъ-Ы-ы-Ь-ь-Э-э-Ю-ю-Я-я");
        $en = explode('-', "A-a-B-b-V-v-G-g-G-g-D-d-E-e-E-e-E-e-ZH-zh-Z-z-I-i-I-i-I-i-J-j-K-k-L-l-M-m-N-n-O-o-P-p-R-r-S-s-T-t-U-u-F-f-H-h-TS-ts-CH-ch-SH-sh-SCH-sch---Y-y---E-e-YU-yu-YA-ya");
        $res = str_replace($ru, $en, $text);
        $res = preg_replace("/[\s\_]+/ui", '-', $res); // заменить пробел и "_" на "-"
        $res = preg_replace("/[^a-zA-Z0-9\.\-]+/ui", '', $res); // удаляем все недопусимвые символы
        $res = strtolower($res); // переводим в нижний регистр
        return $res;
    }


    /**
     *  Конвертация байтов в килобайты и мегабайты
     * 1 КБ = 1024 байта.
     * 1 МБ = 1024 килобайта.
     * 1 ГБ = 1024 мегабайта.
     * 1 ТБ = 1024 гигабайта.
     */
    public function convertBytes($size)
    {
        $i = 0;
        while (floor($size / 1024) > 0) {
            ++$i;
            $size /= 1024;
        }

        $size = str_replace('.', '.', round($size, 1));
        switch ($i) {
            case 0: return $size .= ' Bytes';
            case 1: return $size .= ' Kilobytes';
            case 2: return $size .= ' Megabytes';
        }
    }
}
