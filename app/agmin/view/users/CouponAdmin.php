<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class CouponAdmin extends Auth
{
    public function fetch()
    {

        $coupon = new stdClass();

        /////////////////
        //------- Update
        ////////////////
        if ($this->Request->method('post')) {

            $coupon->id = $this->Request->post('id', 'integer');
            $coupon->code = $this->Request->post('code', 'string');
            $coupon->value = $this->Request->post('value', 'float');
            $coupon->type = $this->Request->post('type', 'string');
            $coupon->min_order_price = $this->Request->post('min_order_price', 'float');
            $coupon->single = $this->Request->post('single', 'float');

            if ($this->Request->post('expires')) {
                $coupon->expire = date('Y-m-d', strtotime($this->Request->post('expire', 'string')));
            } else {
                $coupon->expire = null;
            }

            // Не допустить одинаковые КОДЫ купонов.
            if (($a = $this->UsersCoupons->getCoupon((string)$coupon->code)) && $a->id != $coupon->id) {
                $this->Design->assign('message_error', 'code_exists');
            } else {
                if (empty($coupon->id)) {
                    $coupon->id = $this->UsersCoupons->addCoupon($coupon);
                    $this->Design->assign('message_success', 'added');
                } else {
                    $this->UsersCoupons->updateCoupon($coupon->id, $coupon);
                    $this->Design->assign('message_success', 'updated');
                }
            }
        }


        ///////////////
        //------- View
        //////////////
        if (!empty($id = $this->Misc->getEntityId($coupon))) {
            $coupon = $this->UsersCoupons->getCoupon($id);

            if (empty($coupon->id)) {
                $this->Misc->makeRedirect($this->Request->url(array('id' => null)), '301');
            }
        }


        //		if(empty($coupon->id))
        //			$coupon->expire = date($this->Settings->date_format, time());

        $this->Design->assign('coupon', $coupon);

        return $this->Design->fetch('users/coupon.tpl');
    }
}
