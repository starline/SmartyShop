<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 * Отображение статей на сайте
 * Этот класс использует шаблоны articles.tpl и article.tpl
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

class FeedbackView extends View
{
    public function fetch()
    {

        $feedback = new stdClass();

        if ($this->Request->method('post') && $this->Request->post('feedback')) {

            $feedback->name         = $this->Request->post('name');
            $feedback->email        = $this->Request->post('email');
            $feedback->message      = $this->Request->post('message');

            $this->Design->assign('name', $feedback->name);
            $this->Design->assign('email', $feedback->email);
            $this->Design->assign('message', $feedback->message);

            if (empty($feedback->name)) {
                $this->Design->assign('error', 'empty_name');
            } elseif (empty($feedback->email)) {
                $this->Design->assign('error', 'empty_email');
            } elseif (empty($feedback->message)) {
                $this->Design->assign('error', 'empty_text');
            } else {
                $this->Design->assign('message_sent', true);

                $feedback->ip = $_SERVER['REMOTE_ADDR'];
                $feedback_id = $this->Feedbacks->addFeedback($feedback);

                // Отправляем email
                $this->UsersNotify->sendNotifyToMabager('feedbackToAdmin', [
                    'feedback_id' => $feedback_id
                ]);
            }
        }

        return $this->Design->fetch('feedback.tpl');
    }
}
