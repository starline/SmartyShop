<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 *
 * Google фид
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
    "<?xml version='1.0'?>
    <rss xmlns:g='http://base.google.com/ns/1.0' version='2.0'>
        <channel>
            <title>" . $GoodGin->Settings->company_name . "</title>
            <description>" . $GoodGin->Settings->company_description . "</description>
            <link>" . $GoodGin->Config->root_url . "</link>
            ";

// Валюты
$currencies = $GoodGin->Money->getCurrencies(array('enabled' => 1));
$main_currency = reset($currencies);
$currency_code = $main_currency->code;


// Товары
// Выбираемм только определенные прайсы.
$filter = array();
if (isset($_GET['merchant_id'])) {
    $filter = array('merchant_id' => $_GET['merchant_id']);
}

$products = $GoodGin->ProductsVariants->get_all_products_variants($filter, array('image', 'merchant'));



// В цикле мы используем не results(), a result(), то есть выбираем из базы товары по одному,
// так они нам одновременно не нужны - мы всё равно сразу же отправляем товар на вывод.
// Таким образом используется памяти только под один товар
// В качестве id используется артикул
$prev_product_id = null;
foreach ($products as $p) {

    $variant_url = '';
    if ($prev_product_id === $p->product_id) {
        $variant_url = '?variant=' . $p->variant_id;
    }

    $prev_product_id = $p->product_id;


    //  Форммируем название + вариант
    $name = $p->product_name . ($p->variant_name ? ' - ' . $p->variant_name : '');


    // Преобразуем  описание
    $description = "";
    // Если использовать основное описание товара -  Слишком много нерелевантных слов.

    if (!empty($p->annotation)) {
        $description = strip_tags($p->annotation);
    }

    // + характеристики
    $options = $GoodGin->ProductsFeatures->get_product_options($p->product_id);
    $array_options = [];
    $string_options = "";
    foreach ($options as $item) {
        $array_options[] = $item->name . ': ' . $item->value;
    }
    if (!empty($array_options)) {
        $description .= ' | ' . join(', ', $array_options);
    }


    // Обработка дополнительных фотографий
    $additional_image_link = "";
    $images = $GoodGin->Images->getImages($p->product_id, 'product');
    array_shift($images); // Удаляем первую фотку

    if (!empty($images)) {
        foreach ($images as $image) {
            $additional_image_link .= "<g:additional_image_link>" . $GoodGin->Design->resize_modifier($image->filename, 1080, 1080) . "</g:additional_image_link>";
        }
    }


    // Цена товара со скидкой
    $sale_price = "";
    $price = round($GoodGin->Money->priceConvert($p->price, $main_currency->id, false), 2);
    if (!is_null($p->old_price) && $p->old_price > $p->price) {
        $sale_price = "<g:sale_price>" . $price . " " . $currency_code . "</g:sale_price>";
        $price = round($GoodGin->Money->priceConvert($p->old_price, $main_currency->id, false), 2);
    }


    // Пути к категории товара
    $product_type = "";
    $categories = $GoodGin->ProductsCategories->get_categories(array('product_id' => $p->product_id));
    if (!empty($categories)) {
        $categories_array = [];
        $categories = reset($categories);
        foreach ($categories->path as $category) {
            $categories_array[] = $category->name;
        }

        $product_type = "<g:product_type>" . join(" > ", $categories_array) . "</g:product_type>";
    }


    // Бренд
    $brand = "";
    if (!is_null($p->brand_id)) {
        $brandItem = $GoodGin->ProductsBrands->get_brand((int)$p->brand_id);
        if (!is_null($brandItem)) {
            $brand = "<g:brand>" . $brandItem->name . "</g:brand>";
        }
    }


    // Наличия товара
    $availability = "";
    if (!is_null($p->stock)) {
        if ($p->stock > 0) {
            $availability = "<g:availability>in_stock</g:availability>";
        } else {
            $availability = "<g:availability>out_of_stock</g:availability>";
        }
    }


    // Добавляем метку
    $custom_label_0 = '';
    if (isset($filter['merchant_id'])) {
        $custom_label_0 = "<g:custom_label_0>#" . $filter['merchant_id'] . "</g:custom_label_0>";
    }


    // Формируем выдачу
    if (!empty($p->sku)) {
        print
            "<item>
                <g:id>" . $p->sku . "</g:id>
                <g:title>" . htmlspecialchars($name) . "</g:title>
                <g:description>" . htmlspecialchars($description) . "</g:description>
                <g:link>" . $GoodGin->Config->root_url . '/tovar-' . $p->url . "</g:link>
                <g:image_link>" . $GoodGin->Design->resize_modifier($p->image, 1080, 1080) . "</g:image_link>
                <g:price>" . $price . " " . $currency_code . "</g:price>" .
                $custom_label_0 .
                $additional_image_link .
                $sale_price .
                $product_type .
                $brand .
                $availability .
            "</item>";
    }
}

print "</channel>
</rss>
";
