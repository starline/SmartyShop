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

class FeaturesAdmin extends Auth
{
    public function fetch()
    {

        if ($this->Request->method('post')) {
            // Действия с выбранными
            $ids = $this->Request->post('check');
            if (is_array($ids)) {
                switch ($this->Request->post('action')) {
                    case 'set_in_filter': {
                        $this->ProductsFeatures->updateFeature($ids, array('in_filter' => 1));
                        break;
                    }
                    case 'unset_in_filter': {
                        $this->ProductsFeatures->updateFeature($ids, array('in_filter' => 0));
                        break;
                    }
                    case 'delete': {
                        $current_cat = $this->Request->get('category_id', 'integer');
                        foreach ($ids as $id) {
                            // текущие категории
                            $cats = $this->ProductsFeatures->getFeatureCategories($id);

                            // В каких категориях оставлять
                            $diff = array_diff($cats, (array)$current_cat);
                            if (!empty($current_cat) && !empty($diff)) {
                                $this->ProductsFeatures->updateFeatureCategories($id, $diff);
                            } else {
                                $this->ProductsFeatures->deleteFeature($id);
                            }
                        }
                        break;
                    }
                }
            }

            // Сортировка
            $positions = $this->Request->post('positions');
            $positions_ids = array_keys($positions);
            sort($positions);
            foreach ($positions as $i => $position) {
                $this->ProductsFeatures->updateFeature($positions_ids[$i], array('position' => $position));
            }
        }

        $categories = $this->ProductsCategories->getCategoriesTree();
        $category = null;

        $filter = array();
        $category_id = $this->Request->get('category_id', 'integer');
        if ($category_id) {
            $category = $this->ProductsCategories->get_category($category_id);
            $filter['category_id'] = $category->id;
        }

        $features = $this->ProductsFeatures->get_features($filter);

        $this->Design->assign('categories', $categories);
        $this->Design->assign('category', $category);
        $this->Design->assign('features', $features);

        return $this->Design->fetch('products/features.tpl');
    }
}
