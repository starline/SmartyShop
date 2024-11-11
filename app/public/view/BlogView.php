<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 * Этот класс использует шаблоны blog.tpl и post.tpl
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

use Recaptcha\Recaptcha;

class BlogView extends View
{
    public function fetch()
    {
        $url = $this->Request->get('url', 'string');

        // Если указан адрес поста,
        if (!empty($url)) {

            // Выводим пост
            return $this->fetch_post($url);
        } else {

            // Иначе выводим ленту блога
            return $this->fetch_blog();
        }
    }


    private function fetch_post($url)
    {

        // Выбираем пост из базы
        $post = $this->Blog->getPost($url);

        // Если не найден - ошибка
        if (!$post || (!$post->visible && empty($_SESSION['admin']))) {
            return false;
        }

        // Автозаполнение имени для формы комментария
        if (!empty($this->user->name)) {
            $this->Design->assign('comment_name', $this->user->name);
        }

        // Есть ли ссылка в тексте (http www)
        $have_url = preg_match("/.*(www|http).*/i", $this->Request->post('text'));

        // Принимаем комментарий
        if ($this->Request->method('post') && $this->Request->post('comment')) {

            $comment = new stdClass();
            $comment->name = $this->Request->post('comment_name');
            $comment->text = $this->Request->post('comment_text');
            $comment->related_id = (int)$this->Request->post('comment_related_id');

            if ($this->Request->post('comment_email')) {
                $comment->email = $this->Request->post('comment_email');
            }

            // Передадим комментарий обратно в шаблон - при ошибке нужно будет заполнить форму
            $this->Design->assign('comment_text', $comment->text);
            $this->Design->assign('comment_name', $comment->name);

            // Проверяем заполнение формы
            if (!empty($comment->email)) {
                $this->Design->assign('error', 'email');
            } elseif (empty($comment->name)) {
                $this->Design->assign('error', 'empty_name');
            } elseif (empty($comment->text)) {
                $this->Design->assign('error', 'empty_comment');

                // Chack google recaptchia POST
            } elseif (empty($this->Request->post('g-recaptcha-response'))) {
                $this->Design->assign('error', 'captcha');
            } else {

                // Verify google recaptchia
                $googleResp = Recaptcha::recaptchaCheckAnswer(
                    $this->Config->rc_private_key,
                    $_SERVER["REMOTE_ADDR"],
                    $_POST["g-recaptcha-response"]
                );

                if ($googleResp->success) {

                    // Создаем комментарий
                    $comment->entity_id = $post->id;
                    $comment->type      = 'blog';
                    $comment->ip        = $_SERVER['REMOTE_ADDR'];

                    // Если были одобренные комментарии от текущего ip, одобряем сразу
                    if ($this->Comments->getCommentsCount(array('approved' => 1, 'ip' => $comment->ip)) > 0) {
                        $comment->approved = 1;
                    }

                    // Добавляем комментарий в базу
                    $comment_id = $this->Comments->addComment($comment);

                    // Отправляем email
                    $this->UsersNotify->sendNotifyToManager('commentToAdmin', [
                        'comment_id' => $comment_id
                    ]);

                    header('location: ' . $_SERVER['REQUEST_URI'] . '#comment_' . $comment_id);
                } else {
                    $this->Design->assign('error', 'captcha');
                }
            }
        }

        // Комментарии к посту
        $comments = $this->Comments->getComments(array('type' => 'blog', 'entity_id' => $post->id, 'approved' => 1, 'ip' => $_SERVER['REMOTE_ADDR'], 'answer' => true));
        $this->Design->assign('comments', $comments);
        $this->Design->assign('post', $post);

        // Соседние записи
        $this->Design->assign('next_post', $this->Blog->getNextPost($post->id));
        $this->Design->assign('prev_post', $this->Blog->getPrevPost($post->id));


        // Устанавливаем meta-теги
        if (empty($post->meta_description)) {
            $post->meta_description = $post->meta_title;
        }

        $this->Design->assign('meta_title', $post->meta_title);
        $this->Design->assign('meta_description', $post->meta_description);
        $this->Design->assign('canonical', "/blog/". $post->url);


        return $this->Design->fetch('post.tpl');
    }



    // Отображение списка постов
    private function fetch_blog()
    {

        // Количество постов на 1 странице
        $items_per_page = 20;

        $filter = array();

        // Выбираем только видимые посты
        $filter['visible'] = 1;

        // Текущая страница в постраничном выводе
        $current_page = $this->Request->get('page', 'integer');

        // Если не задана, то равна 1
        $current_page = max(1, $current_page);
        $this->Design->assign('current_page_num', $current_page);

        // Вычисляем количество страниц
        $posts_count = $this->Blog->countPosts($filter);
        $pages_num = ceil($posts_count / $items_per_page);
        $this->Design->assign('total_pages_num', $pages_num);

        $filter['page'] = $current_page;
        $filter['limit'] = $items_per_page;

        // Выбираем статьи из базы
        $posts = $this->Blog->getPosts($filter);

        // Передаем в шаблон
        $this->Design->assign('posts', $posts);
        $this->Design->assign('canonical', '/blog');

        return $this->Design->fetch('blog.tpl');
    }
}
