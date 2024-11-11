<?php

session_start();
define('secure', 'true');

// Composer
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
require_once  dirname(__DIR__) . '/view/Auth.php';
$GoodGin = new Auth();

class ExportAjax extends Auth
{
    private $columns_names = array(
        'id' =>            	 	'№ Заказа',
        'date' =>				'Дата',
        'delivery_note' =>    	'TTH',
        'name' =>             	'Получатель',
        'phone' =>       	 	'Телефон',
        'address' =>          	'Город',
        'total_price' =>      	'Цена',
        'delivery_price' =>     'Цена доставки',
        'payment_price' =>      'Оплачено'
    );

    private $column_delimiter = ';';
    private $export_files_dir = 'files/exports/';
    private $filename = 'orders.csv';

    public function fetch()
    {

        if (!$this->access('orders')) {
            return false;
        }

        // кол-во обрабатываемыз заказов за раз
        $orders_count = $this->Settings->products_num_admin;
        $export_file_path = $this->Config->root_dir . $this->export_files_dir . $this->filename;

        // Эксель кушает только 1251
        //setlocale(LC_ALL, 'ru_RU.1251');
        //$this->Database->query('SET NAMES cp1251');

        // Страница, которую экспортируем
        $page = $this->Request->get('page');
        if (empty($page) || $page == 1) {
            $page = 1;

            // Если начали сначала - удалим старый файл экспорта
            if (is_writable($export_file_path)) {
                unlink($export_file_path);
            }
        }

        // Открываем файл экспорта на добавление
        $f = fopen($export_file_path, 'ab');

        // Если начали сначала - добавим в первую строку названия колонок
        if ($page == 1) {
            fputcsv($f, $this->columns_names, $this->column_delimiter);
        }

        $filter = array();
        $filter['page'] = $page;
        $filter['limit'] = $orders_count;
        $filter['status'] = intval($this->Request->get('status'));
        $filter['label'] = intval($this->Request->get('label'));
        $filter['keyword'] = $this->Request->get('keyword');

        // Выбираем заказы
        foreach ($this->Orders->getOrders($filter) as $order) {
            $str = array();
            foreach ($this->columns_names as $var_name => $c) {
                switch ($var_name) {

                    case 'total_price':

                        // Устанавливаем формат по настройкам валюты. Без форматирования
                        $str[] = $this->Money->priceConvert($order->$var_name, null, false);
                        break;

                    case 'payment_price':

                        // Если заказ оплачен, выводим сумму платежа
                        if (!empty($order->paid)) {
                            $str[] = $this->Money->priceConvert($order->$var_name, null, false);
                        } else {
                            $str[] = '';
                        }
                        break;

                    case 'delivery_price':

                        // Если стоимость доставки включена в счет
                        if (empty($order->separate_delivery)) {
                            $str[] = $this->Money->priceConvert($order->$var_name, null, false);
                        } else {
                            $str[] = '';
                        }
                        break;

                    case 'date':

                        // Устанавливаем формат даты
                        $str[] = date('d.m.Y', strtotime($order->$var_name));
                        break;

                        // Все остальные колонки
                    default:
                        $str[] = $order->$var_name;
                        break;
                }
            }

            fputcsv($f, $str, $this->column_delimiter);
        }

        $total_orders = $this->Orders->getOrdersCount($filter);

        if ($orders_count * $page < $total_orders) {
            return array('end' => false, 'page' => $page, 'totalpages' => ceil($total_orders / $orders_count));
        } else {
            return array('end' => true, 'page' => $page, 'totalpages' => ceil($total_orders / $orders_count));
        }

        fclose($f);
    }
}

$export_ajax = new ExportAjax();

header("Content-type: application/json; charset=utf-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($export_ajax->fetch());
