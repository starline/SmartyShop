<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 * @version 1.4
 *
 */

namespace GoodGin;

class ProductsBrands extends GoodGin
{
    /**
     * Функция возвращает массив брендов, удовлетворяющих фильтру
     * @param $filter
     *
     */
    public function get_brands($filter = array())
    {

        $visible_products = "";
        if (isset($filter['visible'])) {
            $visible_products = $this->Database->placehold(' AND p.visible=?', intval($filter['visible']));
        }

        $where_category = "";
        $join_product = "";
        if (!empty($filter['category_id'])) {
            $join_product = $this->Database->placehold(" LEFT JOIN __products p ON p.brand_id = b.id");
            $where_category = $this->Database->placehold(" AND p.category_id in(?@) $visible_products", (array)$filter['category_id']);
        }

        $where_featured = "";
        if (isset($filter['featured'])) {
            $where_featured = $this->Database->placehold(' AND b.featured=?', intval($filter['featured']));
        }

        // Выбираем все бренды
        $query = $this->Database->placehold(
            "SELECT DISTINCT 
				b.id,
				b.name, 
				b.url, 
				b.meta_title, 
				b.meta_description, 
				b.description, 
				b.image
			FROM 
				__products_brands b 
				$join_product 
			WHERE 
				1 
				$where_featured 
				$where_category 
			ORDER BY 
				b.name"
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Функция возвращает бренд по его id или url
     * (в зависимости от типа аргумента, int - id, string - url)
     * @param $id id или url поста
     *
     */
    public function get_brand($id)
    {
        if (is_int($id)) {
            $filter = $this->Database->placehold('b.id = ?', intval($id));
        } else {
            $filter = $this->Database->placehold('b.url = ?', $id);
        }

        $query = "SELECT 
					b.id,
					b.name, 
					b.featured, 
					b.url, 
					b.meta_title, 
					b.meta_description, 
					b.description, 
					b.image
				FROM 
					__products_brands b 
				WHERE 
					$filter 
				LIMIT 
					1";

        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Добавление бренда
     * @param $brand
     */
    public function add_brand($brand)
    {
        $brand = $this->Misc->cleanEntityId($brand);

        if (empty($brand->url)) {
            $brand->url = preg_replace("/[\s]+/ui", '_', $brand->name);
            $brand->url = strtolower(preg_replace("/[^0-9a-zа-я_]+/ui", '', $brand->url));
        }

        $query = $this->Database->placehold("INSERT INTO __products_brands SET ?%", (array)$brand);
        $this->Database->query($query);
        return $this->Database->getInsertId();
    }


    /**
     * Обновление бренда(ов)
     * @param $id
     * @param $brand
     */
    public function update_brand($id, $brand)
    {
        $query = $this->Database->placehold("UPDATE __products_brands SET ?% WHERE id=? LIMIT 1", $brand, intval($id));
        return $this->Database->query($query);
    }


    /**
     * Удаление бренда
     * @param $id
     *
     */
    public function delete_brand($id)
    {
        if (!empty($id)) {

            // Удаляем изображение
            $this->deleteImage($id);

            $query = $this->Database->placehold("DELETE FROM __products_brands WHERE id=? LIMIT 1", intval($id));
            if ($this->Database->query($query)) {
                $query = $this->Database->placehold("UPDATE __products SET brand_id=NULL WHERE brand_id=?", intval($id));
                return $this->Database->query($query);
            } else {
                return false;
            }
        }
    }


    /**
     * Удаление изображения бренда
     * @param $id
     */
    public function deleteImage($brand_id)
    {
        $query = $this->Database->placehold("SELECT image FROM __products_brands WHERE id=?", intval($brand_id));
        $this->Database->query($query);
        $filename = $this->Database->result('image');

        if (!empty($filename)) {
            $query = $this->Database->placehold("UPDATE __products_brands SET image='' WHERE id=?", intval($brand_id));
            $this->Database->query($query);

            $query = $this->Database->placehold("SELECT count(*) as count FROM __products_brands WHERE image=? LIMIT 1", $filename);
            $this->Database->query($query);
            $count = $this->Database->result('count');

            if ($count == 0) {
                @unlink($this->Config->root_dir . $this->Config->images_brands_dir . $filename);
            }
        }
    }
}
