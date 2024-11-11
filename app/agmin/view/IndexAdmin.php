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

require_once(__DIR__ . '/Auth.php');

// Этот класс выбирает модуль в зависимости от параметра Section и выводит его на экран
class IndexAdmin extends Auth
{
    // Соответсвие модулей и названий соответствующих прав
    // Описание прав находиться в Auth.php
    private $view_permissions = array(
        'OrdersAdmin'         		    => 'orders',
        'OrderAdmin'          		    => 'orders',
        'OrdersLabelsAdmin'   		    => 'orders_labels',
        'OrdersLabelAdmin'    		    => 'orders_labels',
        'OrdersDeliveriesAdmin'     	=> 'orders_delivery',
        'OrdersDeliveryAdmin'       	=> 'orders_delivery',
        'OrdersPaymentMethodAdmin'  	=> 'orders_payment',
        'OrdersPaymentMethodsAdmin' 	=> 'orders_payment',

        'ProductAdmin'       	 	=> 'products_content',
        'ProductPriceAdmin'       	=> 'products_price',
        'ProductMarkingAdmin'      	=> 'products_marking',
        'ProductsAdmin'      	 	=> 'products_view',
        'ProductsImportAdmin'       => 'products_import',
        'FeaturesAdmin'             => 'products_features',
        'FeatureAdmin'              => 'products_features',
        'CategoriesAdmin'     		=> 'products_categories',
        'CategoryAdmin'       		=> 'products_categories',
        'MerchantsAdmin'            => 'products_merchants',
        'MerchantAdmin'             => 'products_merchants',
        'BrandsAdmin'         		=> 'products_brands',
        'BrandAdmin'          		=> 'products_brands',

        'WarehouseMovementsAdmin'	=> 'warehouse',
        'WarehouseMovementAdmin'	=> 'warehouse',
        'ProvidersAdmin'     	 	=> 'warehouse_providers',
        'ProviderAdmin'       		=> 'warehouse_providers',

        'UsersAdmin'          		=> 'users',
        'UserAdmin'           		=> 'users',
        'UserSettingsAdmin'         => 'users_settings',
        'GroupsAdmin'         		=> 'users_groups',
        'GroupAdmin'          		=> 'users_groups',
        'CouponsAdmin'        		=> 'users_coupons',
        'CouponAdmin'         		=> 'users_coupons',
        'NotifyListAdmin'        	=> 'users_notify',
        'NotifyAdmin'         		=> 'users_notify',

        'PagesAdmin'          		=> 'pages',
        'PageAdmin'           		=> 'pages',
        'BlogAdmin'           		=> 'blog',
        'PostAdmin'           		=> 'blog',
        'CommentsAdmin'       		=> 'comments',
        'CommentAdmin'       		=> 'comments',
        'FeedbacksAdmin'      		=> 'feedbacks',

        'FinancePaymentsAdmin'     	=> 'finance',
        'FinancePaymentAdmin'       => 'finance',
        'PurseAdmin'        		=> 'finance',
        'PursesAdmin'        		=> 'finance',
        'FinanceCategoryAdmin'      => 'finance',
        'FinanceCategoriesAdmin'    => 'finance',
        'CurrencyAdmin'       		=> 'finance',

        'ThemeAdmin'          		=> 'design',
        'StylesAdmin'         		=> 'design',
        'TemplatesAdmin'      		=> 'design',
        'ImagesAdmin'         		=> 'design',
        'BackupAdmin'         		=> 'backup',
        'ScriptsAdmin'         		=> 'settings',
        'SettingsAdmin'       		=> 'settings',

        'ExportEntityAdmin'         => 'export',
        'StatsAdmin'          		=> 'stats'
    );

    private $view = null;

    // Конструктор
    public function __construct()
    {

        // Вызываем конструктор базового класса
        parent::__construct();

        if (empty($this->user->id)) {
            $this->Misc->makeRedirect('/user/login', '301');
        }

        // Передаем в дизайн то, что может понадобиться в нем
        $this->Design->assign('user', $this->user);

        $this->Config->setTemplateSubdir('templates/agmin/');
        $this->Design->setTemplateDir($this->Config->root_dir . $this->Config->templates_subdir . 'html');
        $this->Design->setCompiledDir($this->Config->root_dir . 'compiled/agmin');
        $this->Design->setCacheDir($this->Config->root_dir . 'compiled/agmin/cache');


        // Берем название модуля из get-запроса
        $view = $this->Request->get('view', 'string');
        $view = preg_replace("/[^A-Za-z0-9]+/", "", $view);

        // Если не запросили модуль - используем модуль первый из разрешенных
        if (empty($view)) {
            foreach ($this->view_permissions as $m => $p) {
                if (in_array($p, $this->user->permissions)) {
                    $view = $m;
                    break;
                }
            }
        }

        $view_dir  = __DIR__ . "/";
        $view_file =  $view_dir . $view . '.php';

        // Проверяем существование файла модуля
        if (!is_file($view_file)) {

            // Файл не существует, сбрасываем путь
            $view_file = null;

            // росматриваем все директории
            foreach (glob($view_dir . '*', GLOB_ONLYDIR) as $dir) {

                // Ищем файл модуля в подпапках
                if (is_file($dir . "/" . $view . '.php')) {
                    $view_file = $dir . "/" . $view . '.php';
                }
            }
        }

        if (empty($view_file)) {
            die("Файл view не найден.");
        }

        // Проверяем, разрешен ли доступ к модулю
        if (empty($this->view_permissions[$view]) || $this->access($this->view_permissions[$view]) === false) {

            // Если доступ к редактированию товара закрыт, перебрасываем на страницу сайта
            if ($view == "ProductAdmin" || $view == "ProductPriceAdmin") {
                $this->Misc->makeRedirect($this->Config->root_url . '/product/' . $this->Request->get('id', 'integer'), '301');
            }

            die("Access denied. Failed Permissions");
        }

        $this->Design->assign('view', $view);

        // Подключаем файл с необходимым модулем
        require_once($view_file);


        // Создаем соответствующий view
        if (class_exists($view)) {
            $this->view = new $view();
        } else {
            die("Error creating $view class");
        }
    }


    // Выводим HTML
    public function fetch()
    {

        // Order INFO count for top menu
        // 0 - new order
        // 1 - accepted order
        // 4 - shipped order
        $get_status_info = array(0, 1, 4);
        foreach ($get_status_info as $status) {
            $orders_info_count[$status] = $this->Orders->getOrdersCount(array('status' => $status));
        }
        $this->Design->assign('orders_info_count', $orders_info_count);

        // Выбираем основную валюту
        $currency = $this->Money->getMainCurrency();
        $this->Design->assign("currency", $currency);

        $content = $this->view->fetch();
        $this->Design->assign("content", $content);

        $new_comments_counter = $this->Comments->getCommentsCount(array('approved' => 0));
        $this->Design->assign("new_comments_counter", $new_comments_counter);


        // Создаем текущую обертку сайта (обычно index.tpl)
        $wrapper = $this->Design->smarty->getTemplateVars('wrapper');
        if (is_null($wrapper)) {
            $wrapper = 'index.tpl';
        }

        if (!empty($wrapper) and !$this->Misc->isAjax()) {
            return $this->Design->fetch($wrapper);
        } else {
            return $content;
        }
    }
}
