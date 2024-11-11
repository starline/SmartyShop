<?php

/**
 * Для оператора turbosms.ua
 *
 * @author Andi Huga
 * @author mkuzmych/laravel-turbosms
 *
 */

namespace TurboSmsUa;

class TurboSmsUa
{
    private $client;

    private $options = [];
    private $auth_param = [];

    private $sender;
    protected $wsdl = 'http://turbosms.in.ua/api/wsdl.html';

    public function __construct(string $login, string $password, string $sender)
    {

        // Данные авторизации
        $this->auth_param = array(
            'login' => $login,
            'password' => $password
        );

        $this->sender = $sender;
    }


    public function sendSms(array|string $phones, string $text)
    {

        // get SOAP client
        $client = $this->getClient();

        // check for single phone send
        if (is_array($phones)) {
            $destinations = implode(",", $phones);
        } else {
            $destinations = $this->fixPnoneUa($phones);
        }

        // Данные для отправки
        $sms_params = array(
            'sender' => $this->sender,
            'destination' => $destinations,
            'text' => $text
        );

        $result = $client->SendSMS($sms_params);

        // Ответ
        if (is_array($result->SendSMSResult->ResultArray)) {
            $result = [
                 'status' => $result->SendSMSResult->ResultArray[0], # Сообщения успешно отправлены
                 'message_id' => $result->SendSMSResult->ResultArray[1] # Id сообщения
            ];
            return $result;
        }

        return false;
    }


    /**
     * Get Soap client
     * @return SoapClient
     */
    protected function getClient()
    {
        if (empty($this->client)) {
            return $this->connect();
        }
        return $this->client;
    }


    /**
     * Connect to Turbosms by Soap
     * @return SoapClient
     */
    protected function connect()
    {

        // check for soap module for php
        if (class_exists('SOAPClient')) {

            // create soap client object
            $client = new \SoapClient($this->wsdl, $this->options);

            // check for entered login and password
            if (!empty($this->auth_param['login']) and !empty($this->auth_param['password'])) {

                // make request for auth
                $result = $client->Auth($this->auth_param);

                // check for authentification result
                if ((string) $result->AuthResult == 'Вы успешно авторизировались') {
                    return $this->client = $client;
                }
            }
        }
        return false;
    }


    /**
     * Get balance
     */
    public function getBalance()
    {

        // get SOAP client
        $client = $this->getClient();

        // we have successful client soap created
        if (is_a($client, 'SoapClient')) {
            return $balance = intval($client->GetCreditBalance()->GetCreditBalanceResult);
        }

        return $false;
    }


    /**
     * Get message status
     * @param $messageId
     * @return string
     */
    public function getMessageStatus(string $messageId)
    {

        // get SOAP client
        $client = $this->getClient();

        // fi we have successful client soap created
        if (is_a($client, 'SoapClient')) {

            $result = $client->GetMessageStatus(['MessageId' => $messageId])->GetMessageStatusResult;

            //default
            $status = '';

            // work with statuses
            $all_statuses = [
                '0' => 'не найдено',
                '1' => 'Отправлено',
                '2' => 'В очереди',
                '3' => 'Сообщение передано в мобильную сеть',
                '4' => 'Сообщение доставлено получателю',
                '5' => 'Истек срок сообщения',
                '6' => 'Удалено оператором',
                '7' => 'Не доставлено',
                '8' => 'Сообщение доставлено на сервер',
                '9' => 'Отклонено оператором',
                '10' => 'Неизвестный статус',
                '11' => 'Ошибка, сообщение не отправлено',
                '12' => 'Не достаточно кредитов на счете',
                '13' => 'Отправка отменена',
                '14' => 'Отправка приостановлена',
                '15' => 'Удалено пользователем',
            ];

            foreach ($all_statuses as $key => $value) {
                if (strpos($result, $value) !== false) {
                    $status = $key;
                    break;
                }
            }

            return [
                'status' => $status,
                'status_description' => $result,
            ];
        }

        return false;
    }


    /**
     * Fix UA phone
     * From 066 123 45 67 -> +380661234567
     */
    private function fixPnoneUa(string $phone)
    {
        // Delete characters
        $phone = str_replace([' ', '-', '(', ')'], '', $phone);

        // Исправляем номер телефонна, добавляем +38
        if (strlen($phone) == 10 and !stripos($phone, "+38")) {
            $phone = "+38" . $phone;
        }
        return $phone;
    }
}
