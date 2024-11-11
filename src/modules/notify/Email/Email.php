<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 *
 */

use GoodGin\GoodGin;

class Email extends GoodGin
{
    /**
     * Send Email
     *
     * @param string $message
     * @param array $params
     *
     * $params[key]:
     * from_email - required
     * to_email - required
     * from_name - required
     * subject - required
     * reply_to
     *
     */
    public function send(string $message, array $params)
    {

        // Defaul params
        if (empty($params['from_email'])) {
            $params['from_email'] = 'info@' . $this->Settings->site_name;
        }
        if (empty($params['from_name'])) {
            $params['from_name'] = $this->Settings->company_name;
        }

        if (!empty($params['user']->email)) {
            $params['to_email'] = $params['user']->email;
        }

        $params_name = [
            'from_email',
            'to_email',
            'from_name',
            'subject',
            'reply_to'
        ];

        foreach ($params as $name => $value) {
            if (in_array($name, $params_name) and !empty($value)) {
                $$name = $value;
            }
        }

        if (empty($to_email) || empty($from_email) || empty($message) || empty($subject)) {
            return false;
        }


        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/html; charset=utf-8;";

        // Задаем имя отправителя
        if (!empty($from_name)) {
            $headers[] = "From: $from_name <$from_email>";
        } else {
            $headers[] = "From: $from_email <$from_email>";
        }

        if (!empty($reply_to)) {
            $headers[] = "reply-to: $reply_to";
        }

        $headers_string = join("\r\n", $headers);
        $subject = "=?utf-8?B?" . base64_encode($subject) . "?=";

        // Отправка через PHP -> exim
        return mail($to_email, $subject, $message, $headers_string);
    }
}
