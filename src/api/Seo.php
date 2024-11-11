<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 * @version 2.4
 *
 * SEO
 *
 */

namespace GoodGin;

class Seo extends GoodGin
{
    /**
     * Выбираем все ключевые словаа сущнности
     * @param $entity_id
     * @param $entity_name - product|category|post
     */
    public function getKeywords($entity_id, $entity_name)
    {
        $query = $this->Database->placehold(
            "SELECT 
                kw.id,
				kw.name,
                kw.entity_name,
                kw.entity_id,
                kw.position
			FROM 
                __seo_keywords as kw
            WHERE
                kw.entity_id  = $entity_id AND kw.entity_name  = '$entity_name'
			ORDER BY 
				kw.position"
        );

        $this->Database->query($query);
        return $this->Database->results('name');
    }


    public function updateKeywords($keywords, $entity_id, $entity_name)
    {
        $this->deleteKeywords($entity_id, $entity_name);

        if (is_array($keywords)) {
            $values = array();
            foreach ($keywords as $index => $keyword) {
                if (!empty($keyword)) {
                    $values[] = "($entity_id, '$entity_name', '$keyword', $index)";
                }
            }

            if (!empty($values)) {
                $query = $this->Database->placehold("INSERT INTO __seo_keywords (entity_id, entity_name, name, position) VALUES " . join(', ', $values));
                $this->Database->query($query);
            } else {
                return false;
            }
        }
    }


    /**
     * Delete Keywords
     */
    public function deleteKeywords($entity_id, $entity_name)
    {
        $query = $this->Database->placehold("DELETE FROM __seo_keywords WHERE entity_id=? AND entity_name=?", $entity_id, $entity_name);
        $this->Database->query($query);
    }


    public function getFAQs($entity_id, $entity_name)
    {
        $query = $this->Database->placehold(
            "SELECT 
				*
			FROM 
                __seo_faqs as fq
            WHERE
                fq.entity_id  = $entity_id AND fq.entity_name  = '$entity_name'
			ORDER BY 
                fq.position"
        );

        $this->Database->query($query);
        return $this->Database->results('name');
    }


    public function updateFAQs($faqs, $entity_id, $entity_name)
    {
        $this->deleteFAQs($entity_id, $entity_name);

        if (is_array($faqs)) {
            $values = array();
            foreach ($faqs as $index => $fq) {
                if (!empty($fq)) {
                    $values[] = "($entity_id, '$entity_name', '$fq', $index)";
                }
            }

            if (!empty($values)) {
                $query = $this->Database->placehold("INSERT INTO __seo_faqs (entity_id, entity_name, name, position) VALUES " . join(', ', $values));
                $this->Database->query($query);
            } else {
                return false;
            }
        }
    }


    /**
     * Delete FAQs
     */
    public function deleteFAQs($entity_id, $entity_name)
    {
        $query = $this->Database->placehold("DELETE FROM __seo_faqs WHERE entity_id=? AND entity_name=?", $entity_id, $entity_name);
        $this->Database->query($query);
    }
}
