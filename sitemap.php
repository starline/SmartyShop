<?php

// Composer
require __DIR__ . '/vendor/autoload.php';
use GoodGin\GoodGin;

$GoodGin = new GoodGin();

header("Content-type: text/xml; charset=UTF-8");
print '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
print '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";


// Главная страница
$url = $GoodGin->Config->root_url;

$lastmod = date("Y-m-d");
print "\t<url>" . "\n";
print "\t\t<loc>$url</loc>" . "\n";
print "\t\t<lastmod>$lastmod</lastmod>" . "\n";
print "\t</url>" . "\n";


// Страницы
/*foreach($GoodGin->Pages->getPages() as $p) {
    if($p->visible && $p->menu_id == 1) {
        $url = $GoodGin->Config->root_url.'/'.esc($p->url);
        print "\t<url>"."\n";
        print "\t\t<loc>$url</loc>"."\n";
        print "\t</url>"."\n";
    }
}*/


// Блог
foreach ($GoodGin->Blog->getPosts(array('visible' => 1)) as $p) {
    $url = $GoodGin->Config->root_url . '/blog/' . esc($p->url);
    print "\t<url>" . "\n";
    print "\t\t<loc>$url</loc>" . "\n";
    print "\t</url>" . "\n";
}


// Категории
foreach ($GoodGin->ProductsCategories->get_categories() as $c) {
    if ($c->visible) {
        $url = $GoodGin->Config->root_url . '/' . esc($c->url);
        print "\t<url>" . "\n";
        print "\t\t<loc>$url</loc>" . "\n";
        print "\t</url>" . "\n";
    }
}


// Бренды
/*foreach($GoodGin->ProductsBrands->get_brands() as $b) {
    $url = $GoodGin->Config->root_url.'/brands/'.esc($b->url);
    print "\t<url>"."\n";
    print "\t\t<loc>$url</loc>"."\n";
    print "\t</url>"."\n";
}*/


// Товары
foreach ($GoodGin->Products->get_products(array("visible" => 1)) as $product) {
    $product_url = $GoodGin->Config->root_url . '/tovar-' . esc($product->url);
    print "\t<url>" . "\n";
    print "\t\t<loc>$product_url</loc>" . "\n";
    print "\t</url>" . "\n";
}

print '</urlset>' . "\n";

function esc($s)
{
    return (htmlspecialchars($s, ENT_QUOTES, 'UTF-8'));
}
