<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 2.2
 *
 */

namespace GoodGin;

class UsersGroups extends GoodGin
{
    /**
     * Get users groups
     */
    public function getGroups()
    {
        $query = $this->Database->placehold("
            SELECT 
                g.id, 
                g.name, 
                g.discount, 
                g.position 
            FROM 
                __users_groups AS g 
            ORDER BY 
                g.position
        ");
        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Get group
     * @param int $id
     */
    public function getGroup(int $id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("SELECT * FROM __users_groups WHERE id=? LIMIT 1", intval($id));
        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Add new group
     * @param $group
     */
    public function addGroup($group)
    {
        $group = $this->Misc->cleanEntityId($group);

        $query = $this->Database->placehold("INSERT INTO __users_groups SET ?%", $group);
        $this->Database->query($query);

        return $this->Database->getInsertId();
    }


    /**
     * Update group
     */
    public function updateGroup(int $id, $group)
    {
        $query = $this->Database->placehold("UPDATE __users_groups SET ?% WHERE id=? LIMIT 1", $group, intval($id));
        return $this->Database->query($query);
    }


    /**
     * Delete group
     */
    public function deleteGroup(int $id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("UPDATE __users SET group_id=NULL WHERE group_id=? LIMIT 1", intval($id));
        if ($this->Database->query($query)) {
            $query = $this->Database->placehold("DELETE FROM __users_groups WHERE id=? LIMIT 1", intval($id));
            return $this->Database->query($query);
        }
        return false;
    }
}
