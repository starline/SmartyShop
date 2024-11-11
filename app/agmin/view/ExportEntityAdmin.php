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

class ExportEntityAdmin extends Auth
{
    public function fetch()
    {


        if (empty($entity = $this->Request->get('entity', 'string'))) {
            return false;
        }

        $export_files_dir = $this->Config->root_dir . 'files/exports/';
        $export_files_url = $this->Config->root_url . '/files/exports/';

        switch ($entity) {
            case 'users':
                $entity_name = 'покупателей';
                break;
            case 'orders':
                $entity_name = 'заказы';
                break;
            case 'products':
                $entity_name = 'товары';
                break;
            case 'product_orders':
                $entity_name = 'Заказы товары';
                break;
            default:
                $entity_name = '';
                break;
        }

        $this->Design->assign('export_files_dir', $export_files_dir);
        $this->Design->assign('filter_arr', "{" . implode_for_js(",", $_GET, ":") . "}");
        $this->Design->assign('entity_name', $entity_name);
        $this->Design->assign('export_file_url', $export_files_url . $entity . '.csv');
        $this->Design->assign('entity', $entity);


        if (!is_writable($export_files_dir)) {
            $this->Design->assign('message_error', 'no_permission');
        }

        return $this->Design->fetch('export_entity.tpl');
    }
}


function implode_for_js($glue, $array, $symbol = '=')
{
    return implode(
        $glue,
        array_map(
            function ($k, $v) use ($symbol) {
                return $k . $symbol . '"'.$v.'"';
            },
            array_keys($array),
            array_values($array)
        )
    );
}
