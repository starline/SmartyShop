<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 2.0
 *
 */

namespace GoodGin;

class Cart extends GoodGin
{
    /**
     * Get cart with purchases
     * @param int $id
     */
    public function getCart(int $id = null)
    {

        $where = '';
        if (!empty($id)) {
            $where = $this->Database->placehold(' AND c.id=? ', intval($id));
        }

        // Берем из сессии
        elseif (!empty($_SESSION['cart'])) {
            $where = $this->Database->placehold(' AND c.token=? ', $_SESSION['cart']);
        }

        // Берем из Cookie
        elseif (!empty($cookie_cart = $this->Misc->getCookie('CART'))) {
            $where = $this->Database->placehold(' AND c.token=? ', $cookie_cart);
        } else {
            return false;
        }


        // Get cart
        $query = $this->Database->placehold(
            "SELECT 
				c.*
			FROM 
				__cart c
            WHERE 
                1 
			    $where
			LIMIT 
				1"
        );

        $this->Database->query($query);
        return $this->Database->result();


        /*
            // Пользовательская скидка
            $cart->discount = 0;
            if (!empty($this->user)) {
                $cart->discount = $user->discount;
            }

            $cart->total_price *= (100 - $cart->discount) / 100;

            // Скидка по купону
            if (!empty($_SESSION['coupon_code'])) {
                $cart->coupon = $this->UsersCoupons->getCoupon($_SESSION['coupon_code']);
                if ($cart->coupon && $cart->coupon->valid && $cart->total_price >= $cart->coupon->min_order_price) {
                    if ($cart->coupon->type == 'absolute') {

                        // Абсолютная скидка не более суммы заказа
                        $cart->coupon_discount = $cart->total_price > $cart->coupon->value ? $cart->coupon->value : $cart->total_price;
                        $cart->total_price = max(0, $cart->total_price - $cart->coupon->value);
                    } else {
                        $cart->coupon_discount = $cart->total_price * ($cart->coupon->value) / 100;
                        $cart->total_price = $cart->total_price - $cart->coupon_discount;
                    }
                } else {
                    unset($_SESSION['coupon_code']);
                }
            }
        }*/
    }


    /**
     * Get cart purchases
     * @param int $cart_id
     */
    public function getCartPurchases(int $cart_id)
    {
        if (empty($cart_id)) {
            return false;
        }

        // Get cart product
        $query = $this->Database->placehold(
            "SELECT 
				cp.*
			FROM 
				__cart_products cp
            WHERE 
			    cp.cart_id=?",
            $cart_id
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Add product variant to cart
     * @param int $variant_id
     * @param int|null $amount
     */
    public function addCartProduct(int $variant_id, int|null $amount = 1)
    {

        // Выберем товар из базы, заодно убедившись в его существовании
        $variant = $this->ProductsVariants->getVariant($variant_id);

        // Если товар существует, добавим его в корзину
        if (!empty($variant) && ($variant->stock > 0 || $variant->custom)) {
            $amount = max(1, $amount);

            // Get Cart
            if (!empty($cart = $this->getCart())) {
                $cart_id = $cart->id;
                foreach ($cart->purchases as $purchase) {
                    if ($purchase->variant_id == $variant->id) {
                        $amount = max(1, $amount + $purchase->amount);
                    }
                }
            }

            // Create cart
            else {
                $cart_id = $this->addCart();

            }

            // Не дадим больше чем на складе, если не под заказ
            if (empty($variant->custom)) {
                $amount = min($amount, $variant->stock);
            }

            if (empty($cart_id)) {
                return false;
            }

            $cart_product = new \stdClass();
            $cart_product->cart_id = $cart_id;
            $cart_product->product_id = $variant->product_id;
            $cart_product->variant_id = $variant->id;
            $cart_product->amount = $amount;

            // Add product variant to cart
            $query = $this->Database->placehold(
                "INSERT INTO 
                    __cart_products 
                SET 
                    ?%",
                $cart_product
            );
            $this->Database->query($query);
            return $this->Database->getInsertId();
        }

        return false;
    }


    /**
     * Get cart
     * @param object $cart
     */
    public function addCart(object $cart = new \stdClass())
    {

        // Define Logined User
        if (empty($cart->user_id) and !empty($this->user->id)) {
            $cart->user_id = $this->user->id;
        }

        $cart->created = date("Y-m-d H:i:s"); # Example: 2021-06-09 14:17:25
        $cart->token = $this->Misc->getToken(uniqid('x', true), 10);
        $cart->ip =  $_SERVER['REMOTE_ADDR'];

        // Save in SESSION
        $_SESSION['cart'] = $cart->token;

        // Save in COOKIE
        $this->Misc->setCookie('cart', $cart->token, 360);

        // Save to DB
        $query = $this->Database->placehold(
            "INSERT INTO 
                __cart 
            SET 
                ?%",
            $cart
        );

        $this->Database->query($query);
        return $this->Database->getInsertId();
    }


    /**
     * Обновление количества товара
     * @param int $variant_id
     * @param int $amount
     */
    public function updateItem(int $variant_id, int $amount = 1)
    {

        // Выберем товар из базы, заодно убедившись в его существовании
        $variant = $this->ProductsVariants->getVariant($variant_id);

        // Если товар существует, добавим его в корзину
        if (!empty($variant) && ($variant->stock > 0 || $variant->custom)) {

            $amount = max(1, $amount);

            // Не дадим больше чем на складе, если не под заказ
            if (!$variant->custom) {
                $amount = min($amount, $variant->stock);
            }

            $_SESSION['shopping_cart'][$variant_id] = intval($amount);
        }
    }


    /**
     * Удаление товара из корзины
     * @param int $variant_id
     */
    public function deleteItem(int $variant_id)
    {
        unset($_SESSION['shopping_cart'][$variant_id]);
    }


    /**
     * Clear Cart
     */
    public function emptyCart(): void
    {
        unset($_SESSION['shopping_cart']);
        unset($_SESSION['coupon_code']);
    }


    /**
     * Apply Coupon
     * @param string $coupon_code
     */
    public function applyCoupon(string $coupon_code): void
    {
        $coupon = $this->UsersCoupons->getCoupon($coupon_code);
        if ($coupon && $coupon->valid) {
            $_SESSION['coupon_code'] = $coupon->code;
        } else {
            unset($_SESSION['coupon_code']);
        }
    }
}
