<?php

/**
 * GoodGin - The Best of gins
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class ProviderAdmin extends Auth
{
    public function fetch()
    {
        $provider = new stdClass();


        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $provider->id = $this->Request->post('id', 'integer');
            $provider->name = $this->Request->post('name');
            $provider->description = $this->Request->post('description');
            $provider->no_restore_price = $this->Request->post('no_restore_price', 'boolean');


            if (empty($provider->id)) {
                $provider->id = $this->Providers->add_provider($provider);
                $this->Design->assign('message_success', 'added');
            } else {
                $this->Providers->update_provider($provider->id, $provider);
                $this->Design->assign('message_success', 'updated');
            }
        }


        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($provider))) {
            $provider = $this->Providers->get_provider($id);

            if (empty($provider->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }
        }

        $this->Design->assign('provider', $provider);

        return  $this->Design->fetch('warehouse/provider.tpl');
    }
}
