<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 3.0
 *
 * Smarty 5.x require PHP7.4/PHP8.1
 * Commbine Plugin to collect and minimize css and js
 *
 */

namespace GoodGin;

use Smarty\Smarty;
use Combine\Combine;

//use Smarty\Filter\Output\TrimWhitespace;

class Design extends GoodGin
{
    public $smarty;

    public function __construct()
    {
        parent::__construct();

        // Initialization Smarty
        $this->smarty = new Smarty();

        // Caching
        $this->smarty->setCaching($this->Config->smarty_caching);
        $this->smarty->setCacheLifetime($this->Config->smarty_cache_lifetime);
        $this->smarty->setCacheDir($this->Config->root_dir .'compiled/' . $this->Settings->theme . '/cache/');

        // Debugging
        // The debugging console does not work when you use the fetch() API, only when using display().
        $this->smarty->setDebugging($this->Config->smarty_debugging);
        $this->smarty->setErrorReporting(E_ALL & ~E_NOTICE);

        // To make Smarty convert Errors warnings into Notices
        $this->smarty->muteUndefinedOrNullWarnings();

        // Compiling Smmarty Template
        $this->smarty->setCompileCheck($this->Config->smarty_compile_check);
        $this->smarty->setCompileDir($this->Config->root_dir . 'compiled/' . $this->Settings->theme);

        // Создаем папку для скомпилированных шаблонов текущей темы
        if (!is_dir($this->smarty->getCompileDir())) {
            mkdir($this->smarty->getCompileDir(), 0777);
        }

        // Make a folder for Cache
        if (!is_dir($this->smarty->getCacheDir())) {
            mkdir($this->smarty->getCacheDir(), 0777);
        }

        // Add Smarty Plugins
        $this->smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'url', array($this, 'smarty_function_url'));
        $this->smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'combine', array($this, 'smarty_function_combine'));

        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'resize', array($this, 'resize_modifier'));
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'plural', array($this, 'plural_modifier'));
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'first', array($this, 'first_modifier'));
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'cut', array($this, 'cut_modifier'));

        // DATE Pligins
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'date', array($this, 'date_modifier'));
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'time', array($this, 'time_modifier'));

        // mMney Plugins
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'convert', array($this,'convert_modifier'));
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'price_format', array($this, 'price_format_modifier'));

        // User Plugins
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'user_access', array($this, 'user_access_modifier'));

        // В новой версии Smarty php модификаторы необходимо регестрировать
        // Look Smarty_Security $php_functions
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, "json_encode", "json_encode");
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, "join", "join");
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, "strtotime", "strtotime");

        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, "urlencode", "urlencode");
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, "ceil", "ceil");
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, "floor", "floor");
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, "max", "max");
        $this->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, "min", "min");

        if ($this->Config->smarty_html_minify) {
            //$this->smarty->loadFilter('output', 'trimwhitespace');
        }
    }


    /**
     * Add varuables to the template
     * @param Array|String $var
     * @param String $value
     */
    public function assign($var, $value = null)
    {
        return $this->smarty->assign($var, $value);
    }


    /**
     * Get the Smarty template
     * @param $template
     * @param $template_dir
     */
    public function fetch($template, $template_dir = null)
    {

        if (!empty($template_dir)) {
            $this->smarty->setTemplateDir($template_dir);
        }

        // Передаем в дизайн то, что может понадобиться в нем
        $this->Design->assign('config', $this->Config);
        $this->Design->assign('settings', $this->Settings);

        return $this->smarty->fetch($template);
    }


    /**
     * Set Tempalte dir
     * @param String @dir
     */
    public function setTemplateDir(String $dir)
    {
        $this->smarty->setTemplateDir($dir);
    }


    /**
     * Set Compiled dir
     * @param String @dir
     */
    public function setCompiledDir(String $dir)
    {
        $this->smarty->setCompileDir($dir);
    }


    /**
     * Set Cache dir
     */
    public function setCacheDir($dir)
    {
        $this->smarty->setCacheDir($dir);
    }


    /**
     *  Get var from Smarty template
     */
    public function getVar($name)
    {
        return $this->smarty->getTemplateVars($name);
    }


    /**
     * CSS JS Combine
     */
    public function smarty_function_combine($params)
    {
        return Combine::init($params);
    }


    /**
     * Making URL with params
     * @param Array $params
     */
    public function smarty_function_url(array $params)
    {
        $clear = false;
        if (isset($params['clear'])) {
            if ($params['clear'] === true) {
                $clear = true;
            }
            unset($params['clear']);
        }

        if (is_array(reset($params))) {
            return $this->Request->url(reset($params), $clear);
        } else {
            return $this->Request->url($params, $clear);
        }
    }


    /**
     * Making Image URL
     */
    public function resize_modifier($filename, $width = 0, $height = 0, $set_watermark = false)
    {
        $resized_filename = $this->Images->addResizeParams($filename, $width, $height, $set_watermark);
        $resized_filename_encoded = $resized_filename;

        if (substr($resized_filename_encoded, 0, 7) == 'http://' || substr($resized_filename_encoded, 0, 8) == 'https://') {
            $resized_filename_encoded = rawurlencode($resized_filename_encoded);
        }

        $resized_filename_encoded = rawurlencode($resized_filename_encoded);

        return $this->Config->root_url . '/' . $this->Config->images_resized_dir . $resized_filename_encoded . '?' . $this->Misc->getToken($resized_filename);
    }


    /**
     * Plural
     */
    public function plural_modifier($number, $singular, $plural1, $plural2 = null)
    {
        $number = abs($number);
        if (!empty($plural2)) {
            $p1 = $number % 10;
            $p2 = $number % 100;
            if ($number == 0) {
                return $plural1;
            }
            if ($p1 == 1 && !($p2 >= 11 && $p2 <= 19)) {
                return $singular;
            } elseif ($p1 >= 2 && $p1 <= 4 && !($p2 >= 11 && $p2 <= 19)) {
                return $plural2;
            } else {
                return $plural1;
            }
        } else {
            if ($number == 1) {
                return $singular;
            } else {
                return $plural1;
            }
        }
    }


    /**
     * Take fist value of array
     */
    public function first_modifier($params = array())
    {
        if (!is_array($params)) {
            return false;
        }
        return reset($params);
    }


    /**
     * Cut Array
     */
    public function cut_modifier($array, $num = 1)
    {
        if ($num >= 0) {
            return array_slice($array, $num, count($array) - $num, true);
        } else {
            return array_slice($array, 0, count($array) + $num, true);
        }
    }


    /**
     * Date to DATE format
     */
    public function date_modifier($date, $format = null)
    {
        if (empty($date)) {
            $date = date("Y-m-d");
        }
        return date(empty($format) ? $this->Settings->date_format : $format, strtotime($date));
    }


    /**
     * Date to TIME format
     */
    public function time_modifier($date, $format = null)
    {
        return date(empty($format) ? 'H:i' : $format, strtotime($date));
    }


    /**
     * Check user access
     * @param Object $user
     * @param Array|String $access_type
     */
    public function user_access_modifier($user, $access_type)
    {
        return $this->Users->checkUserAccess($user, $access_type);
    }


    /**
     * Price currency converter
     * @param Number $amount
     * @param Integer $currency_to_id
     * @param Boolean $format
     * @param Integer $currency_from_id
     *
     */
    public function convert_modifier($amount, $currency_to_id = null, $format = true, $currency_from_id = null)
    {
        return $this->Money->priceConvert($amount, $currency_to_id, $format, $currency_from_id);
    }


    public function price_format_modifier($price, $precision = 2, $style = null, $decimals_point = null, $thousands_separator = null)
    {
        return $this->Money->priceFormat($price, $precision, $style, $decimals_point, $thousands_separator);
    }


    /**
     * Check if mobile browser
     */
    private function is_mobile_browser()
    {

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $http_accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';

        if (preg_match('iPad', $user_agent)) {
            return false;
        }

        if (stristr($user_agent, 'windows') && !stristr($user_agent, 'windows ce')) {
            return false;
        }

        if (preg_match('windows ce|iemobile|mobile|symbian|mini|wap|pda|psp|up.browser|up.link|mmp|midp|phone|pocket', $user_agent)) {
            return true;
        }

        if (stristr($http_accept, 'text/vnd.wap.wml') || stristr($http_accept, 'application/vnd.wap.xhtml+xml')) {
            return true;
        }

        if (!empty($_SERVER['HTTP_X_WAP_PROFILE']) || !empty($_SERVER['HTTP_PROFILE']) || !empty($_SERVER['X-OperaMini-Features']) || !empty($_SERVER['UA-pixels'])) {
            return true;
        }

        $agents = array(
            'acs-' => 'acs-',
            'alav' => 'alav',
            'alca' => 'alca',
            'amoi' => 'amoi',
            'audi' => 'audi',
            'aste' => 'aste',
            'avan' => 'avan',
            'benq' => 'benq',
            'bird' => 'bird',
            'blac' => 'blac',
            'blaz' => 'blaz',
            'brew' => 'brew',
            'cell' => 'cell',
            'cldc' => 'cldc',
            'cmd-' => 'cmd-',
            'dang' => 'dang',
            'doco' => 'doco',
            'eric' => 'eric',
            'hipt' => 'hipt',
            'inno' => 'inno',
            'ipaq' => 'ipaq',
            'java' => 'java',
            'jigs' => 'jigs',
            'kddi' => 'kddi',
            'keji' => 'keji',
            'leno' => 'leno',
            'lg-c' => 'lg-c',
            'lg-d' => 'lg-d',
            'lg-g' => 'lg-g',
            'lge-' => 'lge-',
            'maui' => 'maui',
            'maxo' => 'maxo',
            'midp' => 'midp',
            'mits' => 'mits',
            'mmef' => 'mmef',
            'mobi' => 'mobi',
            'mot-' => 'mot-',
            'moto' => 'moto',
            'mwbp' => 'mwbp',
            'nec-' => 'nec-',
            'newt' => 'newt',
            'noki' => 'noki',
            'opwv' => 'opwv',
            'palm' => 'palm',
            'pana' => 'pana',
            'pant' => 'pant',
            'pdxg' => 'pdxg',
            'phil' => 'phil',
            'play' => 'play',
            'pluc' => 'pluc',
            'port' => 'port',
            'prox' => 'prox',
            'qtek' => 'qtek',
            'qwap' => 'qwap',
            'sage' => 'sage',
            'sams' => 'sams',
            'sany' => 'sany',
            'sch-' => 'sch-',
            'sec-' => 'sec-',
            'send' => 'send',
            'seri' => 'seri',
            'sgh-' => 'sgh-',
            'shar' => 'shar',
            'sie-' => 'sie-',
            'siem' => 'siem',
            'smal' => 'smal',
            'smar' => 'smar',
            'sony' => 'sony',
            'sph-' => 'sph-',
            'symb' => 'symb',
            't-mo' => 't-mo',
            'teli' => 'teli',
            'tim-' => 'tim-',
            'tosh' => 'tosh',
            'treo' => 'treo',
            'tsm-' => 'tsm-',
            'upg1' => 'upg1',
            'upsi' => 'upsi',
            'vk-v' => 'vk-v',
            'voda' => 'voda',
            'wap-' => 'wap-',
            'wapa' => 'wapa',
            'wapi' => 'wapi',
            'wapp' => 'wapp',
            'wapr' => 'wapr',
            'webc' => 'webc',
            'winw' => 'winw',
            'winw' => 'winw',
            'xda-' => 'xda-'
        );

        if (!empty($agents[substr($_SERVER['HTTP_USER_AGENT'], 0, 4)])) {
            return true;
        }
    }

}
