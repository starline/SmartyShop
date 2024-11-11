<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 * @version 1.4
 *
 */

namespace GoodGin;

class ProductsCategories extends GoodGin
{
    private $all_categories;			// Список указателей на категории в дереве категорий (ключ = id категории)
    private $categories_tree;			// Дерево категорий


    /**
     * Функция возвращает массив категорий
     * @param $filter
     */
    public function get_categories($filter = array())
    {
        if (!isset($this->categories_tree)) {
            $this->initCategories();
        }

        if (!empty($filter['product_id'])) {
            $this->Database->query("SELECT category_id FROM __products WHERE id in(?@) ORDER BY position", (array)$filter['product_id']);
            $categories_ids = $this->Database->results('category_id');

            $result = array();
            foreach ($categories_ids as $id) {
                if (isset($this->all_categories[$id])) {
                    $result[$id] = $this->all_categories[$id];
                }
            }
            return $result;
        }

        // Выбираем категории для показа на главной. Выбираем только level=1
        if (!empty($filter['main'])) {

            $result = array();
            foreach ($this->all_categories as $category) {
                if ($category->main == 1 and $category->level == 1) {
                    $result[] = $this->get_category((int)$category->id);
                }
            }
            return $result;
        }

        return $this->all_categories;
    }


    /**
     * Функция возвращает id категорий для заданного товара
     * @param $product_id
     */
    public function get_product_categories($product_id)
    {
        $this->Database->query("SELECT id, category_id, position FROM __products WHERE id in(?@) ORDER BY position", (array)$product_id);
        return $this->Database->results();
    }


    /**
     * Функция возвращает дерево категорий
     * @param $only_visible
     */
    public function getCategoriesTree($only_visible = false)
    {
        if (!isset($this->categories_tree)) {
            $this->initCategories($only_visible);
        }

        return $this->categories_tree;
    }


    /**
     * Функция возвращает заданную категорию
     * @param $id
     */
    public function get_category($id)
    {
        if (!isset($this->all_categories)) {
            $this->initCategories();
        }

        // Выбираем по ID (Integer)
        if (is_int($id) && array_key_exists(intval($id), $this->all_categories)) {
            return $category = $this->all_categories[intval($id)];
        }

        // Выбираем по URL (String)
        elseif (is_string($id)) {
            foreach ($this->all_categories as $category) {
                if ($category->url == $id) {
                    return $this->get_category((int)$category->id);
                }
            }
        }

        return false;
    }


    /**
     * Добавление категории
     * @param $category
     */
    public function add_category($category)
    {
        $category = $this->Misc->cleanEntityId($category);
        $category = $this->checkCategoryURL($category);

        $this->Database->query("INSERT INTO __products_categories SET ?%", $category);
        $id = $this->Database->getInsertId();

        $this->Database->query("UPDATE __products_categories SET position=id WHERE id=?", $id);

        unset($this->categories_tree);
        unset($this->all_categories);

        return $id;
    }


    /**
     * Изменение категории
     * @param $id
     * @param $category
     */
    public function update_category($id, $category)
    {
        $category = $this->checkCategoryURL($category);
        $this->Database->query("UPDATE __products_categories SET ?% WHERE id=? LIMIT 1", $category, intval($id));
        unset($this->categories_tree);
        unset($this->all_categories);
        return intval($id);
    }


    /**
     * Удаление категории
     * @param Array $ids
     */
    public function deleteCategory($ids)
    {
        $ids = (array)$ids;
        foreach ($ids as $id) {
            $category = $this->get_category(intval($id));
            if (!empty($category->children)) { // Array with current category id and children id

                foreach ($category->children as $cat_id) {

                    // Удаляем основные изображения
                    $images = $this->Images->getImages($cat_id, "category");
                    foreach ($images as $image) {
                        $this->Images->deleteImage($image->id);
                    }

                    // Удаление синонимов
                    $this->deleteCategorySynonyms($cat_id);

                    // Удаление seo_keywords
                    $this->Seo->deleteKeywords($cat_id, "category");

                    // Удаление seo_faqs
                    $this->Seo->deleteFAQs($cat_id, "category");
                }

                // Удаление категорий
                if ($this->Database->query("DELETE FROM __products_categories WHERE id in(?@)", (array)$category->children)) {
                    $this->Database->query("UPDATE __products SET category_id = NULL WHERE category_id in(?@)", (array)$category->children);
                } else {
                    return false;
                }
            }
        }

        unset($this->categories_tree);
        unset($this->all_categories);

        return true;
    }


    /**
     * Инициализация категорий, после которой категории будем выбирать из локальной переменной
     * @param $only_visible
     * @param $get_images
     */
    private function initCategories($only_visible = false, $get_images = false)
    {

        // Дерево категорий
        $tree = new \stdClass();
        $tree->subcategories = array();

        // Указатели на узлы дерева
        $pointers = array();
        $pointers[0] = &$tree;
        $pointers[0]->path = array();
        $pointers[0]->level = 0;

        $select_images  = "";
        $from_images	= "";
        $group_images	= "";

        // Выбираем картинки категорий
        if ($get_images) {
            $select_images = $this->Database->placehold(", img.filename as filename");
            $from_images = $this->Database->placehold(" LEFT JOIN (SELECT * FROM __content_images WHERE entity_name='category' ORDER BY position) img ON img.entity_id = c.id ");
            $group_images = $this->Database->placehold(" GROUP BY c.id");
        }

        // По-умолчанию
        $where_visible = '';
        if ($only_visible) {
            $where_visible = $this->Database->placehold('AND c.visible = 1');
        }

        // Выбираем все категории
        $query = $this->Database->placehold(
            "SELECT
				c.id, 
				c.parent_id, 
				c.name, 
				c.annotation, 
				c.description, 
				c.url, 
				c.meta_title, 
				c.h1, 
				c.meta_description, 
				c.image, 
				c.visible, 
				c.position, 
                c.main 
				$select_images 
			FROM 
				__products_categories c 
				$from_images
				$group_images
			WHERE
				1 
				$where_visible
			ORDER BY 
				c.parent_id, 
				c.position"
        );

        // Выбор категорий с подсчетом количества товаров для каждой. Может тормозить при большом количестве товаров.
        // $query = $this->Database->placehold("SELECT c.id, c.parent_id, c.name, c.description, c.url, c.meta_title, c.h1, c.meta_description, c.image, c.visible, c.position, COUNT(p.id) as products_count
        //                               FROM __products_categories c LEFT JOIN __products_categories pc ON pc.category_id=c.id LEFT JOIN __products p ON p.id=pc.product_id AND p.visible GROUP BY c.id ORDER BY c.parent_id, c.position");


        $this->Database->query($query);
        $categories = $this->Database->results();

        $finish = false;

        // Не кончаем, пока не кончатся категории, или пока ниодну из оставшихся некуда приткнуть
        while (!empty($categories) && !$finish) {
            $flag = false;

            // Проходим все выбранные категории
            foreach ($categories as $k => $category) {
                if (isset($pointers[$category->parent_id])) {

                    // В дерево категорий (через указатель) добавляем текущую категорию
                    $pointers[$category->id] = $pointers[$category->parent_id]->subcategories[] = $category;

                    // Путь к текущей категории
                    $curr = $pointers[$category->id];
                    $pointers[$category->id]->path = array_merge((array)$pointers[$category->parent_id]->path, array($curr));

                    // Уровень вложенности категории
                    $pointers[$category->id]->level = 1 + $pointers[$category->parent_id]->level;

                    // Убираем использованную категорию из массива категорий
                    unset($categories[$k]);
                    $flag = true;
                }
            }
            if (!$flag) {
                $finish = true;
            }
        }

        // Для каждой категории id всех ее деток узнаем
        $ids = array_reverse(array_keys($pointers));
        foreach ($ids as $id) {
            if ($id > 0) {
                $pointers[$id]->children[] = $id;

                if (isset($pointers[$pointers[$id]->parent_id]->children)) {
                    $pointers[$pointers[$id]->parent_id]->children = array_merge($pointers[$id]->children, $pointers[$pointers[$id]->parent_id]->children);
                } else {
                    $pointers[$pointers[$id]->parent_id]->children = $pointers[$id]->children;
                }

                // Добавляем количество товаров к родительской категории, если текущая видима
                // if(isset($pointers[$pointers[$id]->parent_id]) && $pointers[$id]->visible)
                //		$pointers[$pointers[$id]->parent_id]->products_count += $pointers[$id]->products_count;
            }
        }

        unset($pointers[0]);
        unset($ids);

        $this->categories_tree = $tree->subcategories;
        $this->all_categories = $pointers;
    }




    /**
     * Функция возвращает cинонимы, удовлетворяющих фильтру
     * @param $filter
     */
    public function getSynonyms($filter = array())
    {

        $where_category = '';
        if (!empty($filter['category_id'])) {
            $where_category = $this->Database->placehold(" AND s.category_id = ? ", intval($filter['category_id']));
        }

        // Выбираем synonyms
        $query = $this->Database->placehold(
            "SELECT DISTINCT 
				s.id,
				s.name,
				s.category_id,
				c.name as category_name
			FROM 
				__products_categories_synonyms s 
				LEFT JOIN __products_categories c ON s.category_id = c.id
			WHERE
				1
				$where_category
			ORDER BY 
				s.position"
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Функция возвращает синоним по его id или name
     * (в зависимости от типа аргумента, int - id, string - name)
     * @param $id id или url поста
     */
    public function getSynonym($id)
    {
        if (is_int($id)) {
            $filter = $this->Database->placehold("s.id = ?", $id);
        } else {
            $filter = $this->Database->placehold("s.name = ?", $id);
        }

        $query =
            "SELECT 
				s.id,
				s.name,
				s.category_id,
				c.name as category_name
			FROM 
				__products_categories_synonyms s
				LEFT JOIN __products_categories c ON s.category_id = c.id
			WHERE 
				$filter 
			LIMIT 
				1";

        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Добавление синоним
     * @param $synonym
     */
    public function addSynonym($synonym)
    {
        $synonym = $this->Misc->cleanEntityId($synonym);

        $this->Database->query("INSERT INTO __products_categories_synonyms SET ?%", (array)$synonym);
        return $this->Database->getInsertId();
    }


    /**
     * Обновление синонима(ов)
     * @param $id - id синонима
     * @param $synonym
     */
    public function updateSynonym($id, $synonym)
    {
        $this->Database->query("UPDATE __products_categories_synonyms SET ?% WHERE id=? LIMIT 1", $synonym, intval($id));
        return $id;
    }


    /**
     * Обновление синонимов категории
     * @param $category_id
     * @param $synonyms
     */
    public function updateCategorySynonyms($category_id, $synonyms)
    {
        $category_id = intval($category_id);
        $this->deleteCategorySynonyms($category_id);

        if (is_array($synonyms)) {
            $values = array();
            foreach ($synonyms as $index => $synonym) {
                if (!empty($synonym)) {
                    $values[] = "($category_id, '$synonym', $index)";
                }
            }

            return $this->Database->query("INSERT INTO __products_categories_synonyms (category_id, name, position) VALUES " . join(', ', $values));
        }
    }


    /**
     * Delete Category synonymms
     */
    public function deleteCategorySynonyms($category_id)
    {
        $this->Database->query("DELETE FROM __products_categories_synonyms WHERE category_id=?", $category_id);
    }


    /**
     * Удаление синонима
     * @param $id
     */
    public function deleteSynonym($id)
    {
        if (empty($id)) {
            return false;
        }
        return $this->Database->query("DELETE FROM __products_categories_synonyms WHERE id=? LIMIT 1", $id);
    }


    /**
     * Проверям совпадения URL
     * @param $category
     */
    private function checkCategoryURL($category)
    {
        if (!empty($category->name)) {

            // Если URL не задан - Создаем URL из названия
            if (empty($product->url)) {
                $category->url = $this->Misc->transliteration_ru_en($category->name);
            } else {

                // Оставляем только допустимые символы
                $category->url = $this->Misc->transliteration_ru_en($category->url);
            }

            // Если есть категория с таким URL, добавляем к нему число
            // Use "-" in URL, because that needs for SEO
            while ($temp_category = $this->get_category((string)$category->url) and $temp_category->id != $category->id) {
                if (preg_match('/(.+)-([0-9]+)$/', $category->url, $parts)) {
                    $category->url = $parts[1] . '-' . ($parts[2] + 1);
                } else {
                    $category->url = $category->url . '-2';
                }
            }
        }
        return $category;
    }
}
