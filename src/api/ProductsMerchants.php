<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @author Artem Sabelnikov
 * @version 1.4
 *
 * Тут работает с Прайсами
 *
 */

namespace GoodGin;

class ProductsMerchants extends GoodGin
{
    /**
     * Выбрать прайслисты
     */
    public function getPriceLists()
    {
        $query = $this->Database->placehold(
            "SELECT 
				*
			FROM 
				__products_merchants as m
			ORDER BY 
				m.sort"
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Выбираем прайслист
     * @param $id
     */
    public function getPriceList($id)
    {
        $query = $this->Database->placehold("SELECT * FROM __products_merchants WHERE id=? LIMIT 1", intval($id));
        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Добавлям прайс лист
     * @param $pricelist
     */
    public function addPriceList($pricelist)
    {
        $pricelist = $this->Misc->cleanEntityId($pricelist);

        $query = $this->Database->placehold("INSERT INTO __products_merchants SET ?%", $pricelist);

        if (!$this->Database->query($query)) {
            return false;
        }

        $id = $this->Database->getInsertId();
        $this->Database->query("UPDATE __products_merchants SET sort=id WHERE id=?", intval($id));
        return $id;
    }


    /**
     * Обновление прйслистов
     * @param $ids
     * @param $pricelist
     */
    public function updatePriceLists($ids, $pricelist)
    {
        $ids = join(',', array_map('intval', $ids));

        $query = $this->Database->placehold("UPDATE __products_merchants SET ?% WHERE id IN (" . $ids . ")", $pricelist);
        $this->Database->query($query);
        return $ids;
    }


    /**
     * Обновляем прайслист
     * @param int $id
     * @param $purse
     */
    public function updatePriceList(int $id, $purse)
    {
        $query = $this->Database->placehold("UPDATE __products_merchants SET ?% WHERE id=? LIMIT 1", $purse, intval($id));
        $this->Database->query($query);
        return $id;
    }


    /**
     * Удаление прайслиста и связей с вариантами  товаров
     * @param int $id
     */
    public function deletePriceList(int $id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("DELETE FROM __products_merchants WHERE id=? LIMIT 1", intval($id));

        // Удаляеем все связи с товарами
        if ($this->Database->query($query)) {
            $queryDeleteRelations = $this->Database->placehold("DELETE FROM __products_merchants_variants WHERE merchant_id=? LIMIT 1", intval($id));
            return $this->Database->query($queryDeleteRelations);
        }
        return true;
    }
}
