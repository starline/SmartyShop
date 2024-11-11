<?php

/**
 * GoodGin CMS - The Best of gins
 * @author Andi Huga
 *
 * Этот класс использует шаблон product.tpl
 *
 */

if (!defined('secure')) {
    exit('Access denied');
}

use Recaptcha\Recaptcha;

class ProductView extends View
{
    public function fetch()
    {

        $product_id = $this->Request->get('product_id');
        $do_redirect = $this->Request->get('redirect');
        $keyword = $this->Request->get('keyword');

        // Если задано ключевое слово
        if (!empty($keyword)) {
            $products = $this->Products->get_products(array('keyword' => $keyword, 'visible' => true));
            if (!empty($products)) {
                $product_id = reset($products)->id;
            } else {
                $this->Misc->makeRedirect($this->Config->root_url, '301');
            }
        }

        if (empty($product_id)) {
            return false;
        }

        // Выбираем товар из базы
        $product = $this->Products->get_product($product_id);
        if (empty($product)) {
            return false;
        }


        // Категория товара
        $categories = $this->ProductsCategories->get_categories(array('product_id' => $product->id));
        $product->category = reset($categories);
        $this->Design->assign('category', reset($categories));


        // Бренд товара
        $brand = $this->ProductsBrands->get_brand(intval($product->brand_id));
        $this->Design->assign('brand', $brand);


        $canonical_url = 'tovar-' . $product->url;
        $this->Design->assign('canonical', '/' . $canonical_url);

        // Редиректим на посадочный url со всех временных страниц
        // В nginx: rewrite ^/product/([0-9]+)$  index.php?view=ProductView&product_id=$1&redirect=true;
        if ($do_redirect) {
            $this->Misc->makeRedirect($this->Config->root_url . '/' . $canonical_url, '301');
        }


        // Изображение товара
        $product->images = $this->Images->getImages($product->id, 'product');
        $product->image = reset($product->images);

        $variants = $this->ProductsVariants->getVariants(array('product_id' => $product->id));
        $product->variants = $variants;

        // Вариант по умолчанию
        if (($v_id = $this->Request->get('variant', 'integer')) > 0 && isset($variants[$v_id])) {
            $product->variant = $variants[$v_id];
        } else {
            $product->variant = reset($variants);
        }

        $product->features = $this->ProductsFeatures->get_product_options(array('product_id' => $product->id));


        //====== Комментарии
        // Автозаполнение имени для формы комментария
        if (!empty($this->user->name)) {
            $this->Design->assign('comment_name', $this->user->name);
        }

        // Есть ли ссылка в тексте (http www)
        $have_url = preg_match("/.*(www|http).*/i", $this->Request->post('text'));

        // Принимаем комментарий. Нажали кнопку comment
        if ($this->Request->post('comment')) {

            $comment = new stdClass();
            $comment->name = $this->Request->post('comment_name');
            $comment->text = $this->Request->post('comment_text');
            $comment->related_id = (int) $this->Request->post('comment_related_id');

            if ($this->Request->post('comment_email')) {
                $comment->email = $this->Request->post('comment_email');
            }

            // Передадим комментарий обратно в шаблон - при ошибке нужно будет заполнить форму
            $this->Design->assign('comment_text', $comment->text);
            $this->Design->assign('comment_name', $comment->name);

            // Проверяем заполнение формы
            if (!empty($comment->email)) { // Защита от бота (боты обычно заполняют все формы)
                $this->Design->assign('error', 'email');
            } elseif (empty($comment->name)) {
                $this->Design->assign('error', 'empty_name');
            } elseif (empty($comment->text)) {
                $this->Design->assign('error', 'empty_comment');

                // Chack google recaptchia POST
            } elseif (empty($this->Request->post('g-recaptcha-response'))) {
                $this->Design->assign('error', 'captcha');
            } else {

                // Verify google recaptchia
                $googleResp = Recaptcha::recaptchaCheckAnswer(
                    $this->Config->rc_private_key,
                    $_SERVER["REMOTE_ADDR"],
                    $_POST["g-recaptcha-response"]
                );

                if ($googleResp->success) {

                    // Создаем комментарий
                    $comment->entity_id = $product->id;
                    $comment->type      = 'product';
                    $comment->ip        = $_SERVER['REMOTE_ADDR'];

                    // Если были одобренные комментарии от текущего ip, одобряем сразу
                    if ($this->Comments->getCommentsCount(array('approved' => 1, 'ip' => $comment->ip)) > 0) {
                        $comment->approved = 1;
                    }

                    // Добавляем комментарий в базу
                    $comment_id = $this->Comments->addComment($comment);

                    // Отправляем email
                    $this->UsersNotify->sendNotifyToManager('commentToAdmin', ['comment_id' => $comment_id]);

                    header('location: ' . $_SERVER['REQUEST_URI'] . '#comment_' . $comment_id);
                } else {
                    $this->Design->assign('error', 'captcha');
                }
            }
        }



        // Связанные товары
        $related_ids = array();
        $related_products = array();
        foreach ($this->Products->get_related_products($product->id) as $p) {
            $related_ids[] = $p->related_id;
            $related_products[$p->related_id] = null;
        }

        if (!empty($related_ids)) {
            foreach ($this->Products->get_products(array('id' => $related_ids, 'in_stock' => 1, 'visible' => 1, 'limit' => $this->Settings->rel_products_num)) as $p) {
                $related_products[$p->id] = $p;
            }

            $related_products_images = $this->Images->getImages(array_keys($related_products), 'product');
            foreach ($related_products_images as $related_product_image) {
                if (isset($related_products[$related_product_image->entity_id])) {
                    $related_products[$related_product_image->entity_id]->images[] = $related_product_image;
                }
            }

            $related_products_variants = $this->ProductsVariants->getVariants(array('product_id' => array_keys($related_products), 'in_stock' => 1));
            foreach ($related_products_variants as $related_product_variant) {
                if (isset($related_products[$related_product_variant->product_id])) {
                    $related_products[$related_product_variant->product_id]->variants[] = $related_product_variant;
                }
            }
            foreach ($related_products as $id => $r) {
                if (is_object($r)) {
                    $r->image = &$r->images[0];
                    $r->variant = &$r->variants[0];
                } else {
                    unset($related_products[$id]);
                }
            }
            $this->Design->assign('related_products', $related_products);
        }

        // Отзывы о товаре
        $comments = $this->Comments->getComments(array('type' => 'product', 'entity_id' => $product->id, 'approved' => 1, 'ip' => $_SERVER['REMOTE_ADDR'], 'sort' => "ASC", 'answer' => true));
        $comments_count = $this->Comments->getCommentsCount(array('type' => 'product', 'entity_id' => $product->id, 'approved' => 1));

        // И передаем его в шаблон
        $this->Design->assign('product', $product);
        $this->Design->assign('comments', $comments);
        $this->Design->assign('comments_count', $comments_count);


        // Добавление в историю просмотренных товаров
        $max_visited_products = 100; # Максимальное число хранимых товаров в истории
        if (!empty($cookie_bp = $this->Misc->getCookie('BP'))) {
            $browsed_products = explode(',', $cookie_bp);

            // Удалим текущий товар, если он был
            if (($exists = array_search($product->id, $browsed_products)) !== false) {
                unset($browsed_products[$exists]);
            }
        }

        // Добавим текущий товар
        $browsed_products[] = $product->id;
        $cookie_data = join(',', array_slice($browsed_products, -$max_visited_products, $max_visited_products));

        $this->Misc->setCookie("BP", $cookie_data, 30); # Время жизни - 30 дней


        // SEO metateg
        if (empty($product->meta_title)) {
            $product->meta_title = $product->name;
        }

        if (empty($product->meta_description)) {
            $product->meta_description = $product->meta_title . ' ' . $this->Settings->product_meta_description;
        }

        $this->Design->assign('meta_title', $product->meta_title);
        $this->Design->assign('meta_description', $product->meta_description);


        // OpenGraph
        $openGraph = array(
            array('property' => 'og:type', 'content' => 'product'),
            array('property' => 'og:url', 'content' => $this->Config->root_url . '/' . $canonical_url),
            array('property' => 'og:site_name', 'content' => $this->Settings->company_name),
            array('property' => 'og:title', 'content' => $product->name),
            array('property' => 'og:description', 'content' => $product->annotation),
            array('property' => 'product:price:currency', 'content' => $this->currency->code)
        );

        if (isset($product->variant->price)) {
            $openGraph[] = array('property' => 'product:price:amount', 'content' => $product->variant->price);
        }
        if (isset($product->variant->pretax_price)) {
            $openGraph[] = array('property' => 'product:pretax_price:amount', 'content' => $product->variant->pretax_price);
            $openGraph[] = array('property' => 'product:pretax_price:currency', 'content' => $this->currency->code);
        }
        if (isset($product->image->filename)) {
            $openGraph[] = array('property' => 'og:image', 'content' => $this->Design->resize_modifier($product->image->filename, 720, 720, true));
            $openGraph[] = array('property' => 'og:image:type', 'content' => 'image/png');
            $openGraph[] = array('property' => 'og:image:alt', 'content' => $product->name);
        }
        $this->Design->assign('openGraph', $openGraph);


        return $this->Design->fetch('product.tpl');
    }
}
