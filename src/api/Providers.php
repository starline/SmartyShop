<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 * @version 1.4
 *
 * Работаем с провайдерами (поставщиками)
 *
 */

namespace GoodGin;

class Providers extends GoodGin
{
    /**
     * Функция возвращает массив поставищиков, удовлетворяющих фильтру
     * @param $filter
     */
    public function get_providers($filter = array())
    {
        $providers = array();

        // Выбираем всеx поставщиков
        $query = $this->Database->placehold(
            "SELECT DISTINCT
				p.*
			FROM 
				__products_providers p 
			ORDER BY 
				p.name"
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Функция возвращает поставщика по его id
     * @param int $id
     */
    public function get_provider(int $id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold(
            "SELECT 
				p.*
			FROM 
				__products_providers p
			WHERE 
                p.id=? 
			LIMIT 
				1",
            $id
        );

        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Добавление провайдера
     * @param $provider
     */
    public function add_provider($provider)
    {
        $provider = $this->Misc->cleanEntityId($provider);

        $this->Database->query("INSERT INTO __products_providers SET ?%", (array)$provider);
        return $this->Database->getInsertId();
    }


    /**
     * Обновление провайдера(ов)
     * @param $provider
     */
    public function update_provider($id, $provider)
    {
        $query = $this->Database->placehold("UPDATE __products_providers SET ?% WHERE id=? LIMIT 1", $provider, intval($id));
        $this->Database->query($query);
        return $id;
    }


    /**
     * Удаление провайдера
     * @param $id
     */
    public function delete_provider($id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("DELETE FROM __products_providers WHERE id=? LIMIT 1", $id);
        return $this->Database->query($query);
    }
}
