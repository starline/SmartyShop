<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 *
 * Тут проверяем авторизацию и права пользователя
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

use GoodGin\GoodGin;

class Auth extends GoodGin
{
    // Виды прав доступа
    public $permissions_list = array(
        'orders'     		    => 'Заказы - Создание, редактирование своих',
        'orders_view_all'	    => 'Заказы - Просмотр всего списка',
        'orders_edit'		    => 'Заказы - Редактирование всех',
        'orders_delete'		    => 'Заказы - Удаление',
        'orders_labels' 	    => 'Заказы - Управление Метками',
        'orders_finance'	    => 'Заказы - Показать Финансы',
        'orders_delivery'       => 'Заказы - Способы доставки',
        'orders_payment'        => 'Заказы - Способы оплаты',


        'products_view'   		=> 'Товары - Просмотр списка',
        'products_content'      => 'Товары - Редактирование контента',
        'products_price' 	    => 'Товары - Цены, аналитика',
        'products_import' 	    => 'Товары - Импорт цен',
        'products_marking'      => 'Товары - Маркировка',
        'products_categories'   => 'Товары - Категории',
        'products_brands'       => 'Товары - Бренды',
        'products_features'     => 'Товары - Харакетеристики',
        'products_merchants'    => 'Товары - Прайсы',


        'warehouse'  		    => 'Поставки - Просмотр',
        'warehouse_add' 	    => 'Поставки - Добавить',
        'warehouse_edit' 	    => 'Поставки - Редактирование',
        'warehouse_providers'   => 'Поставки - Поставщики',


        'users'      		    => 'Покупатели - Просмотр',
        'users_edit' 		    => 'Покупатели - Редактирование',
        'users_delete' 		    => 'Покупатели - Удаление',
        'users_manager'		    => 'Покупатели - Управление Сотрудниками',
        'users_groups'     	    => 'Покупатели - Группы Просмотр',
        'users_groups_edit'	    => 'Покупатели - Группы Редактирование',
        'users_groups_delete'	=> 'Покупатели - Группы Удаление',
        'users_coupons'         => 'Покупатели - Купоны',
        'users_notify'          => 'Покупатели - Оповещения',
        'users_settings'        => 'Покупатели - Управление настройками',


        'pages'                 => 'Контент - Страницы',
        'blog'                  => 'Контент - Блог',
        'comments'              => 'Контент - Комментарии',
        'feedbacks'             => 'Контент - Обратная связь',


        'stats'                 => 'Статистика',


        'finance'	            => 'Финансы',


        'export'                => 'Экспорт',
        'backup'                => 'Бекап',
        'design'                => 'Дизайн',
        'settings'              => 'Настройки сайта'
    );


    // Конструктор
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Собираем + Проверяем данные и разрешения на редактирование POST переменных
     * Пример $data_permissions = array('perm1'=>array('post_name'=>'post_type'), 'perm2'=>array('post_name'=>'post_type'));
     * @param array $data_permissions
     */
    public function postDataAcces($data_permissions = array())
    {
        $res = new \stdClass();
        foreach ($data_permissions as $p_name => $d_array) {

            // Если есть права на редактирование переменной
            if ($this->access($p_name)) {
                foreach ($d_array as $d_name => $d_type) {

                    // Если переменная передана POST или checkbox(boolean), добавляем в Object
                    if (isset($_POST[$d_name]) or $d_type == "boolean") {
                        $res->$d_name = $this->Request->post($d_name, $d_type);
                    }
                }
            }
        }

        return $res;
    }


    /**
     * Проверка разрешения
     * $permissions может быть array() c несколькими разрешениями
     * $permissions может быть строка с разрешениями через запятую
     * @param array|string $permissions
     */
    public function access($permissions): Bool
    {
        return $this->Users->checkUserAccess($this->user, $permissions);
    }
}
