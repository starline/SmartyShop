<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 * @version 1.5
 * 
 */

namespace GoodGin;

class Comments extends GoodGin
{
    /**
     * Возвращает комментарий по id
     * @param $id
     */
    public function getComment($id)
    {
        $query = $this->Database->placehold(
            "SELECT 
                c.id, 
                c.entity_id, 
                c.name, 
                c.ip, 
                c.type, 
                c.text, 
                c.date, 
                c.approved 
            FROM 
                __content_comments c 
            WHERE 
                c.id=? 
            LIMIT 
                1",
            intval($id)
        );

        if ($this->Database->query($query)) {
            return $this->Database->result();
        } else {
            return false;
        }
    }


    /**
     * Возвращает комментарии, удовлетворяющие фильтру
     * @param $filter
     */
    public function getComments($filter = array(), $count = false)
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

        // Показывать коменнтарии с IP
        $where_approved = '';
        if (isset($filter['approved'])) {

            $where_ip = '';
            if (!empty($filter['ip'])) {
                $where_ip = $this->Database->placehold("AND c.ip=?", $filter['ip']);
            }

            $where_approved = $this->Database->placehold("AND (c.approved=? $where_ip)", intval($filter['approved']));
        }

        $where_entity_id = '';
        if (!empty($filter['entity_id'])) {
            $where_entity_id = $this->Database->placehold('AND c.entity_id in(?@)', (array)$filter['entity_id']);
        }

        $where_type = '';
        if (!empty($filter['type'])) {
            $where_type = $this->Database->placehold('AND c.type=?', $filter['type']);
        }

        $where_keyword = '';
        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $where_keyword .= $this->Database->placehold('AND c.name LIKE "%' . $this->Database->escape(trim($keyword)) . '%" OR c.text LIKE "%' . $this->Database->escape(trim($keyword)) . '%" ');
            }
        }

        $sort = 'DESC';
        if (!empty($filter['sort'])) {
            $sort = $filter['sort'];
        }


        // Выбираем комментарии
        if (!$count) {
            $query = $this->Database->placehold(
                "SELECT 
                    c.id, 
                    c.entity_id, 
                    c.ip, 
                    c.name, 
                    c.text, 
                    c.type, 
                    c.date, 
                    c.approved, 
                    c.related_id 
			    FROM 
                    __content_comments c 
                WHERE 
                    1 
                    $where_entity_id 
                    $where_type 
                    $where_keyword 
                    $where_approved 
                ORDER BY 
                    id 
                    $sort 
                $sql_limit"
            );

            $this->Database->query($query);

            if (isset($filter['answer'])) {
                $comments = $this->Database->results();
                $id_to_key = [];
                $answers = [];

                foreach ($comments as $key => $comment) {
                    if ($comment->related_id > 0) {
                        $answers[] = $comment;
                        unset($comments[$key]);
                    } else {
                        $id_to_key[$comment->id] = $key;
                    }
                }

                foreach ($answers as $answer) {
                    if (key_exists($answer->related_id, $id_to_key)) {
                        $comments[$id_to_key[$answer->related_id]]->answer[] = $answer;
                    }
                }

                return $comments;
            } else {
                return $this->Database->results();
            }


            // Выбираем кол-во
        } else {
            $query = $this->Database->placehold(
                "SELECT 
                    count(distinct c.id) as count
			    FROM 
                    __content_comments c 
                WHERE 
                    1 
                    $where_entity_id 
                    $where_type 
                    $where_keyword 
                    $where_approved"
            );

            $this->Database->query($query);
            return $count = $this->Database->result('count');
        }
    }


    /**
     * Количество комментариев, удовлетворяющих фильтру
     * @param $filter
     */
    public function getCommentsCount($filter = array())
    {
        return $this->getComments($filter, true);
    }


    /**
     * Добавление комментария
     * @param $comment
     */
    public function addComment($comment)
    {
        $comment = $this->Misc->cleanEntityId($comment);

        $query = $this->Database->placehold(
            "INSERT INTO 
				__content_comments
			SET 
				?%,
			    date = NOW()",
            $comment
        );

        if (!$this->Database->query($query)) {
            return false;
        }

        $id = $this->Database->getInsertId();
        return $id;
    }


    /**
     * Изменение комментария
     * @param $id
     * @param $comment
     */
    public function updateComment($id, $comment)
    {
        $query = $this->Database->placehold("UPDATE __content_comments SET ?% WHERE id in(?@) LIMIT 1", $comment, (array)$id);
        $this->Database->query($query);
        return $id;
    }


    /**
     * Удаление комментария
     * @param $id
     */
    public function deleteComment($id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("DELETE FROM __content_comments WHERE id=? LIMIT 1", intval($id));
        return $this->Database->query($query);
    }
}
