<?php

/**
 * GoodGin CMS
 *
 * @author Andi Huga
 *
 */

require_once(__DIR__ . '/Rest.php');

class RestProducts extends Rest
{
    public function __construct()
    {
        parent::__construct();

        // if (!$this->Users->access('prodcuts')){
        // 	header('HTTP/1.1 401 Unauthorized');
        // 	exit();
        // }
    }

    public function get()
    {
        $products = array();
        $filter = array();

        // id
        $filter['id'] = array();
        foreach (explode(',', $this->Request->get('id')) as $id) {
            if (($id = intval($id)) > 0) {
                $filter['id'][] = $id;
            }
        }

        // Сортировка
        $filter['sort'] = $this->Request->get('sort');
        // Категория
        $filter['category_id'] = $this->Request->get('category');
        // Дата последнего изменения
        $filter['modified_since'] = $this->Request->get('modified_since');
        // Бренд
        $filter['brand_id'] = $this->Request->get('brand');
        // Страница
        $filter['page'] = $this->Request->get('page');
        // Количество элементов на странице
        $filter['limit'] = $this->Request->get('limit');

        // Какие поля отдавать
        if ($fields = $this->Request->get('fields')) {
            $fields = explode(',', $fields);
        }

        // Выбираем
        foreach ($this->Products->get_products($filter) as $product) {
            $products[$product->id] = new stdClass();

            // Преобразоуем ссылку на изображение
            if ($product->image) {
                $fullURL = $this->Design->resize_modifier($product->image, 200, 200);
                $product->image = $fullURL;
            }

            if ($fields) {
                foreach ($fields as $field) {
                    if (isset($product->$field)) {
                        $products[$product->id]->$field = $product->$field;
                    }
                }
            } else {
                $products[$product->id] = $product;
            }
        }
        if (empty($products)) {
            return false;
        }

        // Выбранные id
        $products_ids = array_keys($products);

        // Присоединяемые данные
        if ($join = $this->Request->get('join')) {
            $join = explode(',', $join);

            // Изображения
            if (in_array('images', $join)) {
                foreach ($this->Images->getImages($products_ids, 'product') as $i) {
                    if (isset($products[$i->entity_id])) {
                        $fullURL = $this->Design->resize_modifier($i->filename, 200, 200);
                        $products[$i->entity_id]->images[] = $fullURL;
                    }
                }
            }


            // Варианты
            if (in_array('variants', $join)) {
                foreach ($this->ProductsVariants->getVariants(array('product_id' => $products_ids)) as $v) {
                    if (isset($products[$v->product_id])) {
                        $products[$v->product_id]->variants[] = $v;
                    }
                }
            }


            // Категории
            $categories_ids = array();
            if (in_array('categories', $join)) {
                foreach ($this->ProductsCategories->get_categories(array('product_id' => $products_ids)) as $pc) {
                    if (isset($products[$pc->product_id])) {
                        $c = $pc;
                        $c = $this->ProductsCategories->get_category(intval($pc->category_id));
                        unset($c->path);
                        unset($c->subcategories);
                        unset($c->children);
                        $products[$pc->product_id]->categories[] = $c;
                        $categories_ids[] = $pc->category_id;
                    }
                }
            }


            // Свойства
            if (in_array('features', $join)) {
                $features_ids = array();
                foreach ($this->ProductsFeatures->getOptions(array('product_id' => $products_ids)) as $o) {
                    if (isset($products[$o->product_id])) {
                        $options[$o->feature_id] = $o;
                        $features_ids[] = $o->feature_id;
                    }
                }
                foreach ($this->ProductsFeatures->get_features(array('id' => $features_ids)) as $f) {
                    if (isset($options[$f->id])) {
                        $f->value = $o->value;
                        $products[$o->product_id]->features[] = $f;
                    }
                }
            }
        }
        return array_values($products);
    }


    public function post()
    {
        $product = json_decode($this->Request->post());
        print_r($product);
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
        } else {
            return $id;
        }

        header("Content-type: application/json");
        header("Location: " . $this->Config->root_url . "/exchange/rest/products/" . $id, true, 201);
    }

    public function put()
    {
        $id = intval($this->Request->get('id'));
        if (empty($id) || !$this->Products->get_product($id)) {
            header("HTTP/1.0 404 Not Found");
            exit();
        }

        $product = json_decode($this->Request->post());
        $variants = $product->variants;
        unset($product->variants);

        $id = $this->Products->update_product($id, $product);

        if (!empty($variants)) {
            $variants_ids = array();
            foreach ($variants as $v) {
                $v->product_id = $id;

                if ($v->stock == '∞' || $v->stock == '') {
                    $v->stock = null;
                }

                if ($v->id) {
                    $this->ProductsVariants->update_variant($v->id, $v);
                } else {
                    $v->product_id = $id;
                    $v->id = $this->ProductsVariants->add_variant($v);
                }
                $variants_ids[] = $v->id;

                // Удалить непереданные варианты
                $current_variants = $this->ProductsVariants->getVariants(array('product_id' => $id));
                foreach ($current_variants as $current_variant) {
                    if (!in_array($current_variant->id, $variants_ids)) {
                        $this->ProductsVariants->delete_variant($current_variant->id);
                    }
                }
            }
        }
        if (!$id) {
            return false;
        } else {
            return $id;
        }

        header("Content-type: application/json");
        header("Location: " . $this->Config->root_url . "/exchange/rest/products/" . $id, true, 201);
    }
}
