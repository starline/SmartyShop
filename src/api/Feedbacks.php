<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 1.6
 *
 */

namespace GoodGin;

class Feedbacks extends GoodGin
{
    /**
     * Get feedback
     * @param int @id
     */
    public function getFeedback(int $id)
    {
        $query = $this->Database->placehold(
            "SELECT 
                f.id, 
                f.name, 
                f.email, 
                f.ip, 
                f.message, 
                f.date 
            FROM 
                __content_feedbacks f 
            WHERE 
                id=? 
            LIMIT 
                1",
            intval($id)
        );

        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Get Feadback List
     * @param array $filter
     */
    public function getFeedbacks(array $filter = array(), $new_on_top = false)
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

        $keyword_filter = "";
        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= $this->Database->placehold('AND f.name LIKE "%' . $this->Database->escape(trim($keyword)) . '%" OR f.message LIKE "%' . $this->Database->escape(trim($keyword)) . '%" OR f.email LIKE "%' . $this->Database->escape(trim($keyword)) . '%" ');
            }
        }

        if ($new_on_top) {
            $sort = 'DESC';
        } else {
            $sort = 'ASC';
        }

        $query = $this->Database->placehold(
            "SELECT
                f.id, 
                f.name, 
                f.email, 
                f.ip, 
                f.message, 
                f.date
			FROM 
                __content_feedbacks f 
            WHERE 
                1 
                $keyword_filter 
            ORDER BY 
                f.id 
                $sort 
            $sql_limit"
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Count Feadback
     * @param array $filter
     */
    public function countFeedbacks(array $filter = array())
    {
        $keyword_filter = '';
        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= $this->Database->placehold('AND f.name LIKE "%' . $this->Database->escape(trim($keyword)) . '%" OR f.message LIKE "%' . $this->Database->escape(trim($keyword)) . '%" OR f.email LIKE "%' . $this->Database->escape(trim($keyword)) . '%" ');
            }
        }

        $query = $this->Database->placehold(
            "SELECT 
                count(distinct f.id) as count
			FROM 
                __content_feedbacks f 
            WHERE 
                1 
                $keyword_filter"
        );

        $this->Database->query($query);
        return $this->Database->result('count');
    }


    /**
     * Добавляем Feedback
     * @param $feedback
     */
    public function addFeedback($feedback)
    {
        $feedback = $this->Misc->cleanEntityId($feedback);

        $query = $this->Database->placehold(
            "INSERT INTO 
                __content_feedbacks
		    SET 
                ?%,
		        date = NOW()",
            $feedback
        );

        if (!$this->Database->query($query)) {
            return false;
        }

        $id = $this->Database->getInsertId();
        return $id;
    }


    /**
     * Update Feadback
     * @param int $id
     */
    public function updateFeedback(int $id, $feedback)
    {
        $date_query = '';
        if (isset($feedback->date)) {
            $date = $feedback->date;
            unset($feedback->date);
            $date_query = $this->Database->placehold(', date=STR_TO_DATE(?, ?)', $date, $this->Settings->date_format);
        }
        $query = $this->Database->placehold("UPDATE __content_feedbacks SET ?% $date_query WHERE id in(?@) LIMIT 1", $feedback, (array)$id);
        $this->Database->query($query);
        return $id;
    }


    /**
     * Delete Feadback
     * @param int $id
     */
    public function deleteFeedback(int $id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("DELETE FROM __content_feedbacks WHERE id=? LIMIT 1", intval($id));
        return $this->Database->query($query);
    }
}
