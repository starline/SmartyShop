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

class BrandsAdmin extends Auth
{
    public function fetch()
    {

        // Обработка действий
        if ($this->Request->method('post')) {

            // Действия с выбранными
            $ids = $this->Request->post('check');

            if(is_array($ids)) {
                switch($this->Request->post('action')) {
                    case 'delete': {
                        foreach($ids as $id) {
                            $this->ProductsBrands->delete_brand($id);
                        }
                        break;
                    }
                }
            }
        }

        $brands = $this->ProductsBrands->get_brands();
        $this->Design->assign('brands', $brands);

        return $this->body = $this->Design->fetch('products/brands.tpl');
    }
}
