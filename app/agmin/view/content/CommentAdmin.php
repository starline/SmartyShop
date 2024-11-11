<?php

if (!defined('secure')) {
    exit('Access denied');
}

class CommentAdmin extends Auth
{
    public function fetch()
    {
        $comment = new stdClass();

        if ($this->Request->method('post')) {

            $comment->id = $this->Request->post('id', 'integer');
            $comment->name = $this->Request->post('name', 'string');
            $comment->text = $this->Request->post('text', 'string');
            $comment->date = date('Y-m-d h:i:s', strtotime($this->Request->post('date') . ' ' . $this->Request->post('time')));

            $this->Comments->updateComment($comment->id, $comment);
            $this->Design->assign('message_success', 'updated');

            $comment = $this->Comments->getComment(intval($comment->id));
        } else {
            $comment->id = $this->Request->get('id', 'integer');
            $comment = $this->Comments->getComment(intval($comment->id));
        }

        if ($comment->type == 'product') {
            $comment->entity = $this->Products->get_product($comment->entity_id);
        }
        if ($comment->type == 'blog') {
            $comment->entity = $this->Blog->getPost(intval($comment->entity_id));
        }

        // Меню
        $menus = $this->Pages->get_menus();
        $this->Design->assign('menus', $menus);

        $this->Design->assign('comment', $comment);

        return $this->Design->fetch('content/comment.tpl');
    }
}
