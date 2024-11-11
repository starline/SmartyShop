<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 *
 */

use GoodGin\GoodGin;
use TurboSmsUa\TurboSmsUa;

class Turbosms extends GoodGin
{
    /**
     * Send Message via Turbosms
     * @param string $message
     * @param array $params
     *
     */
    public function send(String $message, array $params)
    {

        // Phone number fromm User
        if (!empty($params['user']->phone)) {
            $params['phone'] = $params['user']->phone;
        }

        // Phone number from order
        if (!empty($params['order']->phone)) {
            $params['phone'] = $params['order']->phone;
        }

        if (empty($params['phone']) || empty($message)) {
            return false;
        }

        $SMS = new TurboSmsUa(
            $params['login'],
            $params['password'],
            $params['name']
        );

        // Отправляем СМС
        return $sms_result = $SMS->sendSms($params['phone'], $message);
    }
}
