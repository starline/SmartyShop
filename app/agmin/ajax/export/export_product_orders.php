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
        'sku' =>                'арт.',
        'product_name' =>       'Наименование товара',
        'amount' =>             'шт.',
        'name' =>             	'Получатель',
        'phone' =>       	 	'Телефон',
        'address' =>          	'Город',
        'delivery_note' =>    	'TTH',
        'total_price' =>      	'Цена',
        'delivery_price' =>     'Цена доставки',
        'payment_price' =>      'Оплачено',
        'payment_name'  =>      'Способ оплаты',
        'interest_price' =>     '% менеджера',
    );

    private $column_delimiter = ';';
    private $export_files_dir = 'files/exports/';
    private $filename = 'product_orders.csv';

    public function fetch()
    {

        if (!$this->access('orders')) {
            return false;
        }

        // кол-во обрабатываемых заказов за раз
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

        $filter['paid'] = 1;
        $filter['product_id'] = $this->Request->get('product_id');

        // Выбираем заказы
        foreach ($this->Orders->getOrders($filter) as $order) {

            // Выбираем товары заказа
            $order_purchases = $this->Orders->getPurchases(array('order_id' => $order->id));
            foreach ($order_purchases as $index => $purchase) {
                if ($index > 0 and !empty($order)) {
                    unset($order);
                }

                $str = array();
                foreach ($this->columns_names as $var_name => $c) {
                    switch ($var_name) {

                        case 'sku':
                            $str[] = $purchase->sku;
                            break;

                        case 'product_name':
                            $product_name = $purchase->product_name;
                            if (!empty($purchase->variant_name)) {
                                $product_name .= ' - ' . $purchase->variant_name;
                            }
                            $str[] = $product_name;
                            break;

                        case 'amount':
                            $str[] = $purchase->amount;
                            break;

                        case 'payment_name':
                            if (!empty($order->payment_method_id)) {
                                $payment_method = $this->OrdersPayment->getPaymentMethod($order->payment_method_id);
                                $str[] = $payment_method->name;
                            } else {
                                $str[] = "";
                            }

                            break;

                        case 'total_price':

                            // Устанавливаем формат по настройкам валюты. Без форматирования
                            if (isset($order->$var_name)) {
                                $str[] = $this->Money->priceConvert($order->$var_name, null, false);
                            } else {
                                $str[] = "";
                            }
                            break;

                        case 'payment_price':

                            // Если заказ оплачен, выводим сумму платежа
                            if (!empty($order->paid)) {
                                $str[] = $this->Money->priceConvert($order->$var_name, null, false);
                            } else {
                                $str[] = "";
                            }
                            break;

                        case 'delivery_price':

                            // Если стоимость доставки включена в счет
                            if (!empty($order) and empty($order->separate_delivery)) {
                                $str[] = $this->Money->priceConvert($order->$var_name, null, false);
                            } else {
                                $str[] = "";
                            }
                            break;

                        case 'date':

                            // Устанавливаем формат даты
                            if (isset($order->$var_name)) {
                                $str[] = date('d.m.Y', strtotime($order->$var_name));
                            } else {
                                $str[] = "";
                            }
                            break;

                            // Все остальные колонки
                        default:
                            if (!empty($order->$var_name)) {
                                $str[] = $order->$var_name;
                            } else {
                                $str[] = "";
                            }
                            break;
                    }
                }

                fputcsv($f, $str, $this->column_delimiter);
            }
            fputcsv($f, array(), $this->column_delimiter);
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
