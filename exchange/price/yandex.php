<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 * Яндекс фид YXM
 *
 */

// Composer
require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
use GoodGin\GoodGin;

$GoodGin = new GoodGin();

header("Content-type: text/xml; charset=UTF-8");
print(pack('CCC', 0xef, 0xbb, 0xbf));

// Заголовок
print
    "<?xml version='1.0' encoding='UTF-8'?>
<!DOCTYPE yml_catalog SYSTEM 'shops.dtd'>
<yml_catalog date='" . date('Y-m-d H:i') . "'>
<shop>
<name>" . $GoodGin->Settings->site_name . "</name>
<company>" . $GoodGin->Settings->company_name . "</company>
<url>" . $GoodGin->Config->root_url . "</url>
";

// Валюты
// Не показывааем все, у нас одна валюта.
$currencies = $GoodGin->Money->getCurrencies(array('enabled' => 1));
$main_currency = reset($currencies);
$currency_code = $main_currency->code;

print "<currencies>";
print "<currency id='" . $main_currency->code . "' rate='" . $main_currency->rate_to / $main_currency->rate_from * $main_currency->rate_from / $main_currency->rate_to . "'/>";
print "</currencies>";


// Категории
$categories = $GoodGin->ProductsCategories->get_categories();

print "<categories>";

foreach ($categories as $c) {
    print "<category id='$c->id'";
    if ($c->parent_id > 0) {
        print " parentId='$c->parent_id'";
    }
    print ">" . htmlspecialchars($c->name) . "</category>";
}
print "</categories>";

// Товары
// Выбираемм только определенные прайсы.
$filter = array();
if (isset($_GET['merchant_id'])) {
    $filter = array('merchant_id' => $_GET['merchant_id']);
}

// Товары
$products = $GoodGin->ProductsVariants->get_all_products_variants($filter, array('image', 'merchant'));

print "<offers>";

// В цикле мы используем не results(), a result(), то есть выбираем из базы товары по одному,
// так они нам одновременно не нужны - мы всё равно сразу же отправляем товар на вывод.
// Таким образом используется памяти только под один товар
$prev_product_id = null;
foreach ($products as $p) {

    $variant_url = '';
    if ($prev_product_id === $p->product_id) {
        $variant_url = '?variant=' . $p->variant_id;
    }
    $prev_product_id = $p->product_id;

    $price = round($GoodGin->Money->priceConvert($p->price, $main_currency->id, false), 2);
    print
        "
        <offer id='$p->variant_id' available='true'>
            <url>" . $GoodGin->Config->root_url . '/tovar-' . $p->url . $variant_url . "</url>
            <price>$price</price>
            <currencyId>$currency_code</currencyId>
            <categoryId>$p->category_id</categoryId>
        ";


    // Берем размер большой картинки
    if ($p->image) {
        print "<picture>" . $GoodGin->Design->resize_modifier($p->image, 1080, 1080) . "</picture>";
    }

    print
            "<name>" . htmlspecialchars($p->product_name) . ($p->variant_name ? ' ' . htmlspecialchars($p->variant_name) : '') . "</name>
            <description>" . htmlspecialchars(strip_tags($p->annotation)) . "</description>
        </offer>";
}

print "</offers>";
print "</shop>";
print "</yml_catalog>";
