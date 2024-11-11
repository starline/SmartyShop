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

class FeatureAdmin extends Auth
{
    private $FeatureProps = array(
        'id' => 'integer',
        'name' => 'string',
        'in_filter' => 'boolean'
    );

    public function fetch()
    {

        $Feature = new stdClass();
        $feature_categories = array();
        $feature_variants = array();
        $options = array();

        $data_permissions = array(
            'products_features' => $this->FeatureProps
        );


        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $Feature = $this->postDataAcces($data_permissions);

            $feature_categories = $this->Request->post('feature_categories');
            $feature_variants = $this->Request->post('feature_variants');

            if (empty($Feature->id)) {
                $Feature->id = $this->ProductsFeatures->addFeature($Feature);
                $this->Design->assign('message_success', 'added');
            } else {
                $this->ProductsFeatures->updateFeature($Feature->id, $Feature);
                $this->Design->assign('message_success', 'updated');
            }

            $this->ProductsFeatures->updateFeatureCategories($Feature->id, $feature_categories);
            $this->ProductsFeatures->updateFeatureVariants($Feature->id, $feature_variants);
        }


        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($Feature))) {
            $Feature = $this->ProductsFeatures->getFeature($id);

            if (empty($Feature->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }

            $feature_categories = $this->ProductsFeatures->getFeatureCategories($Feature->id);
            $feature_variants = $this->ProductsFeatures->getFeatureVariants($Feature->id);

            // Используемые значения характеристики
            $filter['feature_id'] = $Feature->id;
            $options = $this->ProductsFeatures->getOptions($filter);
        }


        $categories = $this->ProductsCategories->getCategoriesTree();

        $this->Design->assign('feature', $Feature);
        $this->Design->assign('options', $options);
        $this->Design->assign('categories', $categories);
        $this->Design->assign('feature_categories', $feature_categories);
        $this->Design->assign('feature_variants', $feature_variants);

        return $this->Design->fetch('products/feature.tpl');
    }
}
