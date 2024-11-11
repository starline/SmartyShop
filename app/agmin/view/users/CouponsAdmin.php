<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class CouponsAdmin extends Auth
{
    public function fetch()
    {

        // Обработка действий
        if($this->Request->method('post')) {

            // Действия с выбранными
            $ids = $this->Request->post('check');
            if(is_array($ids) && count($ids) > 0) {
                switch($this->Request->post('action')) {
                    case 'delete':
                        {
                            foreach($ids as $id) {
                                $this->UsersCoupons->deleteCoupon($id);
                            }
                            break;
                        }
                }
            }
        }

        $filter = array();
        $filter['page'] = max(1, $this->Request->get('page', 'integer'));
        $filter['limit'] = 20;

        // Поиск
        $keyword = $this->Request->get('keyword', 'string');
        if(!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->Design->assign('keyword', $keyword);
        }

        $coupons_count = $this->UsersCoupons->countCoupons($filter);

        $pages_count = ceil($coupons_count / $filter['limit']);
        $filter['page'] = min($filter['page'], $pages_count);
        $this->Design->assign('coupons_count', $coupons_count);
        $this->Design->assign('pages_count', $pages_count);
        $this->Design->assign('current_page', $filter['page']);


        $coupons = $this->UsersCoupons->getCoupons($filter);
        $this->Design->assign('coupons', $coupons);

        return $this->Design->fetch('users/coupons.tpl');
    }
}
