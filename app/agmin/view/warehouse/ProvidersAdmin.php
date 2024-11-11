<?php

/**
 * GoodGin - The Best of gins
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class ProvidersAdmin extends Auth
{
    public function fetch()
    {

        // Обработка действий
        if($this->Request->method('post')) {

            // Действия с выбранными
            $ids = $this->Request->post('check');

            if(is_array($ids)) {
                switch($this->Request->post('action')) {
                    case 'delete': {
                        foreach($ids as $id) {
                            $this->Providers->delete_provider($id);
                        }
                        break;
                    }
                }
            }
        }

        $providers = $this->Providers->get_providers();
        $this->Design->assign('providers', $providers);

        return $this->body = $this->Design->fetch('warehouse/providers.tpl');
    }
}
