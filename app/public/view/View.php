<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 * Базовый класс для всех View
 * Добавлен SEO модуль
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

use GoodGin\GoodGin;

class View extends GoodGin
{
    // Смысл класса в доступности следующих переменных в любом View
    public $currency;
    public $currencies;
    public $seo;
    public $categories_tree;

    // Класс View похож на синглтон, храним статически его инстанс
    private static $view_instance;

    public function __construct()
    {

        parent::__construct();

        // Если инстанс класса уже существует - просто используем уже существующие переменные
        if (self::$view_instance) {
            $this->currency     		= &self::$view_instance->currency;
            $this->currencies   		= &self::$view_instance->currencies;
            $this->seo         			= &self::$view_instance->seo;
            $this->categories_tree     	= &self::$view_instance->categories_tree;
        } else {

            // Сохраняем свой инстанс в статической переменной,
            // чтобы в следующий раз использовать его
            self::$view_instance = $this;

            // Все валюты
            $this->currencies = $this->Money->getCurrencies(array('enabled' => 1));

            // Выбор текущей валюты
            if (!empty($currency_id = $this->Request->get('currency_id', 'integer'))) {
                $_SESSION['currency_id'] = $currency_id;
                $this->Misc->makeRedirect($this->Request->url(array('currency_id' => null)));
            }

            // Берем валюту из сессии
            if (!empty($_SESSION['currency_id'])) {
                $this->currency = $this->Money->getCurrency($_SESSION['currency_id']);

                // Или первую из списка
            } else {
                $this->currency = reset($this->currencies);
            }


            // Сохраняем в сессию переход с Adwords
            if ($adWodrs = $this->Request->get('gclid')) {
                $_SESSION['adWodrs'] = $adWodrs;
            }


            // $_SERVER['REQUEST_URI']; // - /info/pravila
            $page_URI = trim($_SERVER['REQUEST_URI'], "/");

            // SEO модуль. Выбираем только SEO страницы (menu_id = 2)
            $this->seo = $this->Pages->getPage((string)$page_URI, array('visible' => 1, 'menu_id' => 2));

            // Категории товаров
            $this->categories_tree = $this->ProductsCategories->getCategoriesTree(true);

            $this->cart = $this->Cart->getCart();
            
            $this->Design->assign([
                'config' => $this->Config, #Configuration
                'settings' => $this->Settings,
                'user' => $this->user,
                'currency' => $this->currency,
                'currencies' => $this->currencies,
                'categories' => $this->categories_tree,
                'seo' => $this->seo,
                'cart' => $this->cart # Содержимое корзины
            ]);

            // Настраиваем плагины для смарти
            $this->Design->smarty->registerPlugin("function", "get_info_block", array($this, 'get_info_block_plugin'));
            $this->Design->smarty->registerPlugin("function", "get_posts", array($this, 'get_posts_plugin'));
            $this->Design->smarty->registerPlugin("function", "get_brands", array($this, 'get_brands_plugin'));
            $this->Design->smarty->registerPlugin("function", "get_pages", array($this, 'get_pages_plugin'));
            $this->Design->smarty->registerPlugin("function", "get_browsed_products", array($this, 'get_browsed_products_plugin'));
            $this->Design->smarty->registerPlugin("function", "get_new_products", array($this, 'get_new_products_plugin'));
            $this->Design->smarty->registerPlugin("function", "get_discounted_products", array($this, 'get_discounted_products_plugin'));
            $this->Design->smarty->registerPlugin("function", "get_category_list", array($this, 'get_category_list_plugin'));

            $this->Design->smarty->registerPlugin("modifier", "instock", array($this, 'instock_modifier'));
        }
    }



    /**
     * Отображение
     */
    public function fetch()
    {

        // Текущий модуль (для отображения центрального блока)
        $view = $this->Request->get('view', 'string');
        $view = preg_replace("/[^A-Za-z0-9]+/", "", $view);

        // Создаем соответствующий класс
        if (!empty($view) and is_file(__DIR__ . "/$view.php")) {
            include_once(__DIR__ . "/$view.php");
            if (class_exists($view)) {
                $content_view = new $view($this);

                // Создаем основной блок страницы
                if (!$content = $content_view->fetch()) {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }



        // !!! Before final fetch
        // Задаем meta-теги из SEO страницы
        if (!empty($this->seo->meta_title)) {
            $this->Design->assign('meta_title', $this->seo->meta_title);
        }

        if (!empty($this->seo->meta_description)) {
            $this->Design->assign('meta_description', $this->seo->meta_description);
        }
        // !!!



        // Передаем основной блок в шаблон
        $this->Design->assign('content', $content);

        // Передаем название модуля в шаблон, это может пригодиться
        $this->Design->assign('view', $view);

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






        // !!! After final fetch
        // Сохраняем последнюю просмотренную страницу в переменной $_SESSION['last_visited_page']
        if (empty($_SESSION['last_visited_page']) || empty($_SESSION['current_page']) || $_SERVER['REQUEST_URI'] !== $_SESSION['current_page']) {
            if (!empty($_SESSION['current_page']) && !empty($_SESSION['last_visited_page']) && $_SESSION['last_visited_page'] !== $_SESSION['current_page']) {
                $_SESSION['last_visited_page'] = $_SESSION['current_page'];
            }
            $_SESSION['current_page'] = $_SERVER['REQUEST_URI'];
        }
        // !!!


        return $all_content;
    }



    /**
     * Отображаем наличие
     * Smarty modifier
     * @param $count
     * @param $limit
     * @param $return
     */
    public function instock_modifier($count = null, $limit, $return)
    {
        if (isset($count) and $count < $limit) {
            return $return;
        }

        return '';
    }


    /**
     * Выбираем Список категорий товаров
     * Smarty Plugin
     * @param $params
     *
     */
    public function get_category_list_plugin($params, $smarty)
    {
        if (empty($params['var'])) {
            return false;
        }

        $categories = array();

        if (isset($params['category_url'])) {
            $categories[] = $this->ProductsCategories->get_category($params['category_url']);
        } elseif (isset($params['category_id'])) {
            $categories[] = $this->ProductsCategories->get_category($params['category_id']);
        } elseif (isset($params['main'])) {
            $categories = $this->ProductsCategories->get_categories(array('main' => $params['main']));
        } else {
            return false;
        }

        foreach ($categories as $category) {
            if (!empty($category->children)) {
                $category_images = $this->Images->getImages($category->children, 'category');
                foreach ($category->subcategories as &$scat) {
                    foreach ($scat->subcategories as &$s_scat) {
                        foreach ($category_images as $image) {
                            if ($s_scat->id == $image->entity_id) {
                                $s_scat->images[] = $image;
                            }
                        }
                    }
                }
            }
        }

        $smarty->assign($params['var'], $categories);
    }


    /**
     * Выбрать инфоблок
     * Smarty Plugin
     */
    public function get_info_block_plugin($params, $smarty)
    {
        if (!empty($params['var'])) {
            $smarty->assign($params['var'], $this->Pages->getPage($params['id']));
        }
    }


    /**
     * Плагины для смарти
     * Smarty Plugin
     */
    public function get_posts_plugin($params, $smarty)
    {
        if (!isset($params['visible'])) {
            $params['visible'] = 1;
        }

        if (isset($params['random'])) {
            $params['random'] = 1;
        }

        if (!empty($params['var'])) {
            $smarty->assign($params['var'], $this->Blog->getPosts($params));
        }
    }


    /**
     * Выбираем страницы меню
     * Smarty Plugin
     */
    public function get_pages_plugin($params, $smarty)
    {
        if (!isset($params['visible'])) {
            $params['visible'] = 1;
        }

        if (!empty($params['var'])) {
            $smarty->assign($params['var'], $this->Pages->getPages($params));
        }
    }


    /**
     * Выбрать бренды
     * Smarty Plugin
     */
    public function get_brands_plugin($params, $smarty)
    {
        if (!isset($params['visible'])) {
            $params['visible'] = 1;
        }
        if (!empty($params['var'])) {
            $smarty->assign($params['var'], $this->ProductsBrands->get_brands($params));
        }
    }


    /**
     * Выбираем просмотренные продукты
     * Smarty Plugin
     */
    public function get_browsed_products_plugin($params, $smarty)
    {

        if (!empty($cookie_bp = $this->Misc->getCookie('BP'))) {
            $browsed_products_ids = explode(',', $cookie_bp);
            $browsed_products_ids = array_reverse($browsed_products_ids);

            if (isset($params['limit'])) {
                $browsed_products_ids = array_slice($browsed_products_ids, 0, $params['limit']);
            }

            $browsed_products = $this->Products->get_products(array('id' => $browsed_products_ids, 'visible' => 1));

            if (!empty($browsed_products)) {

                // id выбраных товаров
                $pids = array_keys($browsed_products);

                // Выбираем варианты товаров
                $variants = $this->ProductsVariants->getVariants(array('product_id' => $pids));

                // Для каждого варианта, добавляем вариант в соответствующий товар
                foreach ($variants as &$variant) {
                    $browsed_products[$variant->product_id]->variants[] = $variant;
                }

                // Выбираем изображения товаров
                $browsed_products_images = $this->Images->getImages($pids, 'product');
                foreach ($browsed_products_images as $image) {
                    $browsed_products[$image->entity_id]->images[] = $image;
                }

                foreach ($browsed_products as &$product) {
                    if (isset($product->variants[0])) {
                        $product->variant = $product->variants[0];
                    }
                    if (isset($product->images[0])) {
                        $product->image = $product->images[0];
                    }
                }

                // Сортируем товары в порядке просмотра
                $browsed_products_sort = array();
                foreach ($browsed_products_ids as $bp_id) {
                    if ($browsed_products[$bp_id]) {
                        $browsed_products_sort[] = $browsed_products[$bp_id];
                    }
                }

                $smarty->assign($params['var'], $browsed_products_sort);
            }
        }
    }


    /**
     * Выбрать новые продукты
     * Smarty Plugin
     */
    public function get_new_products_plugin($params, $smarty)
    {
        if (!isset($params['visible'])) {
            $params['visible'] = 1;
        }
        if (!isset($params['sort'])) {
            $params['sort'] = 'created';
        }
        if (!empty($params['var'])) {

            $products = $this->Products->get_products($params);
            $products_ids = array_keys($products); // id выбраных товаров

            if (!empty($products)) {

                // Выбираем варианты товаров
                $variants = $this->ProductsVariants->getVariants(array('product_id' => $products_ids, 'in_stock' => true));

                // Для каждого варианта
                foreach ($variants as &$variant) {
                    // добавляем вариант в соответствующий товар
                    $products[$variant->product_id]->variants[] = $variant;
                }

                // Выбираем изображения товаров
                $images = $this->Images->getImages($products_ids, 'product');
                foreach ($images as $image) {
                    $products[$image->entity_id]->images[] = $image;
                }

                foreach ($products as &$product) {
                    if (isset($product->variants[0])) {
                        $product->variant = $product->variants[0];
                    }
                    if (isset($product->images[0])) {
                        $product->image = $product->images[0];
                    }
                }
            }

            $smarty->assign($params['var'], $products);
        }
    }


    /**
     * Выбрать продукты со скидкой
     * Smarty Plugin
     */
    public function get_discounted_products_plugin($params, $smarty)
    {
        if (!isset($params['visible'])) {
            $params['visible'] = 1;
        }
        $params['discounted'] = 1;

        if (!empty($params['var'])) {

            $products = $this->Products->get_products($params);

            if (!empty($products)) {

                $products_ids = array_keys($products); // id выбраных товаров

                // Выбираем варианты товаров
                $variants = $this->ProductsVariants->getVariants(array('product_id' => $products_ids, 'in_stock' => true));

                // Для каждого варианта
                foreach ($variants as &$variant) {

                    // добавляем вариант в соответствующий товар
                    $products[$variant->product_id]->variants[] = $variant;
                }

                // Выбираем изображения товаров
                $images = $this->Images->getImages($products_ids, 'product');
                foreach ($images as $image) {
                    $products[$image->entity_id]->images[] = $image;
                }

                foreach ($products as &$product) {
                    if (isset($product->variants[0])) {
                        $product->variant = $product->variants[0];
                    }
                    if (isset($product->images[0])) {
                        $product->image = $product->images[0];
                    }
                }
            }

            $smarty->assign($params['var'], $products);
        }
    }
}
