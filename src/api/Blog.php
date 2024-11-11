<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 1.3
 *
 */

namespace GoodGin;

class Blog extends GoodGin
{
    /**
     * Функция возвращает пост по его id или url
     * (в зависимости от типа аргумента, int - id, string - url)
     * @param $id - id или url поста
     */
    public function getPost($id)
    {
        if (is_int($id)) {
            $where = $this->Database->placehold(' AND b.id=? ', intval($id));
        } else {
            $where = $this->Database->placehold(' AND b.url=? ', $id);
        }

        $query = $this->Database->placehold(
            "SELECT 
                b.id, 
                b.url, 
                b.name, 
                b.annotation, 
                b.body, 
                b.meta_title,
		        b.meta_description, 
                b.visible, 
                b.date
		    FROM 
                __content_blog b
            Where 
                1 
                $where 
            LIMIT 
                1"
        );

        if ($this->Database->query($query)) {
            return $this->Database->result();
        } else {
            return false;
        }
    }


    /**
     * Функция возвращает массив постов, удовлетворяющих фильтру
     * @param $filter
     * @param $count
     */
    public function getPosts($filter = array(), $count = false)
    {

        // Pages view
        $sql_limit = "";
        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
            $page = 1;
            if (isset($filter['page'])) {
                $page = max(1, intval($filter['page']));
            }
            $sql_limit = $this->Database->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);
        }

        $post_id_filter = '';
        if (!empty($filter['id'])) {
            $post_id_filter = $this->Database->placehold('AND b.id in(?@)', (array)$filter['id']);
        }

        $visible_filter = '';
        if (isset($filter['visible'])) {
            $visible_filter = $this->Database->placehold('AND b.visible = ?', intval($filter['visible']));
        }

        $keyword_filter = '';
        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= $this->Database->placehold('AND (b.name LIKE "%' . $this->Database->escape(trim($keyword)) . '%" OR b.meta_description LIKE "%' . $this->Database->escape(trim($keyword)) . '%") ');
            }
        }

        $order_filter = "";
        if (isset($filter['random']) and $filter['random'] == 1) {
            $order_filter =  " rand() ";
        } else {
            $order_filter = " date DESC, id DESC ";
        }

        // Выбираем посты
        if ($count === false) {
            $query = $this->Database->placehold(
                "SELECT 
                    b.id, 
                    b.url, 
                    b.name, 
                    b.annotation, 
                    b.body,
                    b.meta_title, 
                    b.meta_description, 
                    b.visible,
                    b.date
                FROM 
                    __content_blog b
                WHERE 
                    1 
                    $post_id_filter 
                    $visible_filter 
                    $keyword_filter
                ORDER BY 
                    $order_filter
                    $sql_limit"
            );

            $this->Database->query($query);
            return $this->Database->results();
        }

        // Выбираем кол-во
        else {
            $query = $this->Database->placehold(
                "SELECT 
                    COUNT(distinct b.id) as count
		        FROM 
                    __content_blog b 
                WHERE 
                    1 
                    $post_id_filter 
                    $visible_filter 
                    $keyword_filter"
            );

            if ($this->Database->query($query)) {
                return $this->Database->result('count');
            } else {
                return false;
            }
        }
    }


    /**
     * Функция вычисляет количество постов, удовлетворяющих фильтру
     * @param $filter
     */
    public function countPosts($filter = array())
    {
        return $this->getPosts($filter, true);
    }


    /**
     * Создание поста
     * @param $post
     * @return $id
     */
    public function addPost($post)
    {
        $post = $this->Misc->cleanEntityId($post);

        if (!empty($post->url)) {
            $post->url = $this->Misc->transliteration_ru_en($post->url);
        }

        $date_query = "";
        if (empty($post->date)) {
            $date_query = ", date=NOW()";
        }

        $query = $this->Database->placehold("INSERT INTO __content_blog SET ?% $date_query", $post);

        if ($this->Database->query($query)) {
            return $this->Database->getInsertId();
        } else {
            return false;
        }
    }


    /**
     * Обновить пост(ы)
     * @param $id
     * @param $post
     */
    public function updatePost($id, $post)
    {
        if (!is_array($post) and !empty($post->url)) {
            $post->url = $this->Misc->transliteration_ru_en($post->url);
        }

        $query = $this->Database->placehold(
            "UPDATE 
                __content_blog 
            SET 
                ?% 
            WHERE 
                id in(?@) 
            LIMIT 
                ?",
            $post,
            (array)$id,
            count((array)$id)
        );

        $this->Database->query($query);
        return $id;
    }


    /**
     * Удалить пост
     * Delete also comments, images
     * @param $id
     */
    public function deletePost($id)
    {
        if (empty($id)) {
            return false;
        }

        // Delete post
        $query = $this->Database->placehold("DELETE FROM __content_blog WHERE id=? LIMIT 1", intval($id));
        if ($this->Database->query($query)) {

            // Delete comments
            $query = $this->Database->placehold("DELETE FROM __content_comments WHERE type='blog' AND entity_id=?", intval($id));
            if ($this->Database->query($query)) {

                // Select all post images
                $images = $this->Images->getImages($id, 'post');
                foreach ($images as $i) {

                    // Delete images
                    $this->Images->deleteImage($i->id);
                }
                return true;
            }
        }
        return false;
    }


    /**
     * Следующий пост
     * @param $id
     *
     */
    public function getNextPost($id)
    {
        $this->Database->query("SELECT date FROM __content_blog WHERE id=? LIMIT 1", $id);
        $date = $this->Database->result('date');

        $this->Database->query(
            "(SELECT id FROM __content_blog WHERE date=? AND id>? AND visible  ORDER BY id limit 1)
                UNION
                (SELECT id FROM __content_blog WHERE date>? AND visible ORDER BY date, id limit 1)",
            $date,
            $id,
            $date
        );

        $next_id = $this->Database->result('id');
        if ($next_id) {
            return $this->getPost(intval($next_id));
        } else {
            return false;
        }
    }


    /**
     * Предыдущий пост
     * @param $id
     *
     */
    public function getPrevPost($id)
    {
        $this->Database->query("SELECT date FROM __content_blog WHERE id=? LIMIT 1", $id);
        $date = $this->Database->result('date');

        $this->Database->query(
            "(SELECT id FROM __content_blog WHERE date=? AND id<? AND visible ORDER BY id DESC limit 1)
                UNION
                (SELECT id FROM __content_blog WHERE date<? AND visible ORDER BY date DESC, id DESC limit 1)",
            $date,
            $id,
            $date
        );
        $prev_id = $this->Database->result('id');
        if ($prev_id) {
            return $this->getPost(intval($prev_id));
        } else {
            return false;
        }
    }
}
