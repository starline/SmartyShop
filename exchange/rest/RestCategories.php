<?php

/**
 * GoodGin CMS
 *
 * @copyright	2012
 * @author Andi Huga
 *
 */

require_once(__DIR__ . '/Rest.php');

class RestBlog extends Rest
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->Users->access('products_categories')) {
            header('HTTP/1.1 401 Unauthorized');
            exit();
        }
    }

    public function fetch()
    {
        if ($this->Request->method('GET')) {
            $result = $this->get();
        }
        if ($this->Request->method('DELETE')) {
            $result = $this->delete();
        }

        return $this->indent(json_encode($result));
    }

    public function get()
    {
        $fields = explode(',', $this->Request->get('fields'));

        $ids = array();
        foreach (explode(',', $this->Request->get('id')) as $id) {
            if (($id = intval($id)) > 0) {
                $ids[] = $id;
            }
        }

        $filter = array();
        if (!empty($ids)) {
            $filter['id'] = $ids;
        }

        $filter['sort'] = $this->Request->get('sort');
        $filter['category_id'] = $this->Request->get('category_id');
        $filter['page'] = $this->Request->get('page');
        $filter['limit'] = $this->Request->get('limit');

        $products = array();
        foreach ($this->Blog->getPosts($filter) as $p) {
            $products[$p->id] = null;
            if ($this->Request->get('fields')) {
                foreach ($fields as $field) {
                    if (isset($p->$field)) {
                        $products[$p->id]->$field = $p->$field;
                    }
                }
            } else {
                $products[$p->id] = $p;
            }
        }

        $products_ids = array_keys($products);

        if ($join = $this->Request->get('join')) {
            $join = explode(',', $join);
            if (in_array('images', $join)) {
                foreach ($this->Images->getImages($products_ids, 'product') as $i) {
                    if (isset($products[$i->product_id])) {
                        $products[$i->product_id]->images[$i->id] = $i;
                    }
                }
            }
            if (in_array('variants', $join)) {
                foreach ($this->ProductsVariants->getVariants(array('product_id' => $products_ids)) as $v) {
                    if (isset($products[$v->product_id])) {
                        $products[$v->product_id]->variants[$v->id] = $v;
                    }
                }
            }
            if (in_array('categories', $join)) {
                foreach ($this->ProductsCategories->get_categories(array('product_id' => $products_ids)) as $pc) {
                    if (isset($products[$pc->product_id])) {
                        $products[$pc->product_id]["categories"][$pc->category_id] = $pc;
                        $products[$pc->product_id]["categories"][$pc->category_id]->category = $this->ProductsCategories->get_category(intval($pc->category_id));
                    }
                }
            }
        }
        return $products;
    }


    /**
     * post_product
     */
    public function post_product()
    {

        if ($this->Request->method('POST')) {
            $product = json_decode($this->Request->post());
            $variants = $product->variants;
            unset($product->variants);

            $id = $this->Products->add_product($product);

            if (!empty($variants)) {
                foreach ($variants as $v) {
                    $v->product_id = $id;
                    $varinat_id = $this->ProductsVariants->add_variant($v);
                }
            }
            if (!$id) {
                return false;
            }
        }

        header("Content-type: application/json");
        header("Location: " . $this->Config->root_url . "/exchange/rest/products/" . $id, true, 201);
    }
}
