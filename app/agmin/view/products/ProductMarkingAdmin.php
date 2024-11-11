<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 *
 * ProductMaarkingAdmin
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class ProductMarkingAdmin extends Auth
{
    public function fetch()
    {

        $variant = new stdClass();

        if (!empty($variant_id = $this->Request->getVar('variant_id', 'integer'))) {
            $variant = $this->ProductsVariants->getVariant(intval($variant_id));

            $product = $this->Products->get_product(intval($variant->product_id));
            $variant->product_name = $product->name;
        }

        $count = $this->Request->getVar('count', 'integer');
        if (empty($count)) {
            $count = 1;
        }

        $this->Design->assign('variant', $variant);
        $this->Design->assign('count', $count);

        // Выводим на экран
        return $this->Design->fetch('products/product_marking_print.tpl');
    }
}
