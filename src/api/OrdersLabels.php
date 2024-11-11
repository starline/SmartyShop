<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 * @version 2.5
 *
 */

namespace GoodGin;

class OrdersLabels extends GoodGin
{
    public function get_label($id)
    {
        $query = $this->Database->placehold(
            "SELECT
                * 
            FROM 
                __orders_labels 
            WHERE 
                id=? 
            LIMIT 
                1",
            intval($id)
        );

        $this->Database->query($query);
        return $this->Database->result();
    }


    public function get_labels()
    {
        $query = $this->Database->placehold("SELECT * FROM __orders_labels ORDER BY position");
        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Создание метки заказов
     * @param $label
     */
    public function add_label($label)
    {
        $label = $this->Misc->cleanEntityId($label);

        $query = $this->Database->placehold('INSERT INTO __orders_labels SET ?%', $label);
        if (!$this->Database->query($query)) {
            return false;
        }

        $id = $this->Database->getInsertId();
        $this->Database->query("UPDATE __orders_labels SET position=id WHERE id=? LIMIT 1", $id);
        return $id;
    }


    /**
     * Обновить метку
     * @param $id
     * @param $label
     */
    public function update_label($id, $label)
    {
        $label = $this->Misc->cleanEntityId($label);

        $query = $this->Database->placehold("UPDATE __orders_labels SET ?% WHERE id=? LIMIT 1", $label, $id);
        $this->Database->query($query);
        return $id;
    }


    /**
     * Удалить метку
     * @param $id
     */
    public function delete_label($id)
    {
        if (!empty($id)) {

            // Удаляем сязи с заказами
            $query = $this->Database->placehold("DELETE FROM __orders_labels_related WHERE label_id=?", intval($id));

            if ($this->Database->query($query)) {

                // Удаляем метку
                $query = $this->Database->placehold("DELETE FROM __orders_labels WHERE id=? LIMIT 1", intval($id));
                return $this->Database->query($query);
            }
        }

        return false;
    }


    public function get_order_labels($order_id = array())
    {
        if (empty($order_id)) {
            return array();
        }

        $label_id_filter = $this->Database->placehold('AND orl.order_id in(?@)', (array)$order_id);

        $query = $this->Database->placehold(
            "SELECT 
                orl.order_id, 
                ol.id, 
                ol.name, 
                ol.color, 
                ol.in_filter,
                ol.position
			FROM 
                __orders_labels ol 
            LEFT JOIN 
                __orders_labels_related orl ON orl.label_id = ol.id
			WHERE 
				1
				$label_id_filter   
			ORDER BY 
                position"
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    public function update_order_labels($id, $labels_ids)
    {
        $labels_ids = (array)$labels_ids;
        $query = $this->Database->placehold("DELETE FROM __orders_labels_related WHERE order_id=?", intval($id));
        $this->Database->query($query);
        if (is_array($labels_ids)) {
            foreach ($labels_ids as $l_id) {
                $this->Database->query("INSERT INTO __orders_labels_related SET order_id=?, label_id=?", $id, $l_id);
            }
        }
    }


    public function add_order_labels($id, $labels_ids)
    {
        $labels_ids = (array)$labels_ids;
        if (is_array($labels_ids)) {
            foreach ($labels_ids as $l_id) {
                $this->Database->query("INSERT IGNORE INTO __orders_labels_related SET order_id=?, label_id=?", $id, $l_id);
            }
        }
    }


    public function delete_order_labels($id, $labels_ids)
    {
        $labels_ids = (array)$labels_ids;
        if (is_array($labels_ids)) {
            foreach ($labels_ids as $l_id) {
                $this->Database->query("DELETE FROM __orders_labels_related WHERE order_id=? AND label_id=?", $id, $l_id);
            }
        }
    }
}
