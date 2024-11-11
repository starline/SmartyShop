<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @author Artem Sabelnikov
 * @version 2.5
 *
 * Тут работает с Финансами
 *
 */

namespace GoodGin;

class Finance extends GoodGin
{
    /**
     * Выбираем платеди
     * @param $filter
     * @param $count
     */
    public function get_payments($filter = array(), $count = false)
    {

        $where_keyword = '';
        $where_purse = '';
        $where_category = '';
        $where_payments_type = '';

        // Pages view
        $sql_limit = "";
        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
            $page = 1;
            if (isset($filter['page'])) {
                $page = max(1, intval($filter['page']));
            }
            $sql_limit = $this->Database->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);
        }

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);

            // Ищем по comment
            foreach ($keywords as $keyword) {
                $where_keyword .= $this->Database->placehold(' AND (p.comment LIKE "%' . $this->Database->escape(trim($keyword)) . '%")');
            }
        }

        if (isset($filter['purse_id'])) {
            $where_purse =   $this->Database->placehold(' AND p.purse_id in(?@) ', (array)$filter['purse_id']);
        }

        if (isset($filter['category_id'])) {
            $where_category = $this->Database->placehold(' AND fc.id = ? ', (int)$filter['category_id']);
        }

        if (isset($filter['payments_type'])) {
            if ($filter['payments_type'] == 'plus' || $filter['payments_type'] == 'income') {
                $where_payments_type = $this->Database->placehold(' AND p.type = 1 AND p.related_payment_id is null');
            } elseif ($filter['payments_type'] == 'minus' || $filter['payments_type'] == 'expense') {
                $where_payments_type = $this->Database->placehold(' AND p.type = 0 AND p.related_payment_id is null');
            } elseif ($filter['payments_type'] == 'transfer') {
                $where_payments_type = $this->Database->placehold(' AND p.related_payment_id is not null');
            }
        }

        // Выбираем платежи
        if ($count === false) {

            $query = $this->Database->placehold(
                "SELECT
					p.*,
					fc.name as category_name,
					fp.name as purse_name,
					c.sign as currency_sign
				FROM 
					__finance_payments p
					LEFT JOIN __finance_categories fc ON p.finance_category_id = fc.id
					LEFT JOIN __finance_purses fp ON p.purse_id = fp.id 
					LEFT JOIN __finance_currencies c ON fp.currency_id = c.id 
				WHERE 
					1
					$where_keyword 
					$where_purse 
					$where_category 
					$where_payments_type
				ORDER BY
					date  DESC  
				$sql_limit"
            );

            $this->Database->query($query);

            $payments = array();
            foreach ($this->Database->results() as $payment) {
                $payments[$payment->id] = $payment;
            }

            return $payments;


            // Выбираем кол-во
        } else {

            $query = $this->Database->placehold(
                "SELECT
					count(distinct p.id) as count
				FROM 
					__finance_payments p
					LEFT JOIN __finance_categories fc ON p.finance_category_id = fc.id 
				WHERE
					1
					$where_keyword 
					$where_purse
					$where_category 
					$where_payments_type"
            );

            $this->Database->query($query);
            return $this->Database->result('count');
        }
    }


    /**
     * Выбираем кол-во платежей
     * @param $filter
     */
    public function count_payments($filter = array())
    {
        return $this->get_payments($filter, true);
    }


    /**
     * Выбираем платеж
     * Дополнительно выбираем валюту currency_sign
     * @param $id
     */
    public function get_payment($id)
    {
        $query = $this->Database->placehold(
            "SELECT
				p.*,
                fp.name as purse_name,
                fp.id as purse_id,
				c.sign as currency_sign,
				cat.name as category_name,
				cat.comment as category_comment
			FROM 
				__finance_payments p 
				LEFT JOIN __finance_purses fp ON fp.id = p.purse_id 
				LEFT JOIN __finance_currencies c ON c.id = fp.currency_id 
				LEFT JOIN __finance_categories cat ON cat.id = p.finance_category_id 
			WHERE 
				p.id=? 
			LIMIT 
				1",
            intval($id)
        );

        $this->Database->query($query);
        $payment = $this->Database->result();

        return $payment;
    }


    /**
     * Добавляем пплатеж
     * @param $payment
     */
    public function add_payment($payment)
    {
        $payment = $this->Misc->cleanEntityId($payment);

        $set_curr_date = "";
        if (empty($payment->date)) {
            $set_curr_date = ', date=now()';
        }

        if (!empty($payment->purse_to_id)) {
            // BUG сюда нужно добавить добавление связанного платежа
            unset($payment->purse_to_id);
        }

        $this->change_purse_amount($payment, 'add');

        // Select purse amount after update
        $purse = $this->get_purse($payment->purse_id);
        $payment->purse_amount = $purse->amount;

        $query = $this->Database->placehold("INSERT INTO __finance_payments SET ?% $set_curr_date", $payment);
        $this->Database->query($query);
        return $this->Database->getInsertId();
    }


    /**
     * Обновляем платеж
     * Умеет сменять кошельки
     * @param $id
     * @param $payment
     */
    public function update_payment($id, $payment)
    {
        $payment = (object)$payment;

        // Если задана суммма платежа, корректируем остаток на кошельке
        if (isset($payment->amount)) {

            $old_payment = $this->get_payment($id);
            if (empty($old_payment)) {
                return false;
            }

            // Если не задан тип платежа (это перевод), сохраняем ранее установленый
            if (!isset($payment->type) and isset($old_payment->type)) {
                $payment->type = $old_payment->type;
            }

            // Обновляем amount
            $this->change_purse_amount($old_payment, 'back');
            $this->change_purse_amount($payment, 'add');

            // Select purse amount after update if current amount differs from previous amount
            if (!empty($payment->purse_id) and !empty($purse = $this->get_purse($payment->purse_id)) and $payment->amount != $old_payment->amount) {
                $payment->purse_amount = $purse->amount;
            }
        }

        // Если верефицируем платеж, устанавливаем дату и пользователя
        if (!empty($payment->verified) and !empty($payment->verified_user_id)) {
            $payment->verified_date = date("Y-m-d h:i:s", time());
        }

        // Обновляем платеж
        $query = $this->Database->placehold("UPDATE __finance_payments SET ?% WHERE id=? LIMIT 1", $payment, intval($id));
        return $this->Database->query($query);
    }


    /**
     * Удалить платеж
     * Пересчитываем остаток на кошельке
     * Удаление связи с контрагентом
     * @param int $id - ID payment
     */
    public function deletePayment(int $id)
    {
        if (empty($id)) {
            return false;
        }

        $payment = $this->get_payment($id);
        $this->change_purse_amount($payment, 'back');

        // Удаляем платеж
        $query = $this->Database->placehold("DELETE FROM __finance_payments WHERE id=? LIMIT 1", intval($id));
        if ($this->Database->query($query)) {

            // Удаляем связь с контрагентом
            return $this->delete_contractor($id);
        }

        return false;
    }


    /**
     * Подсчитываем сумму на кошельке
     * @param $payment
     * @param $type - add/back. back - отмена платежа. add - новый платеж
     */
    public function change_purse_amount($payment, $type = 'add')
    {
        if (!isset($payment->amount) || empty($payment->purse_id)) {
            return false;
        }

        // Знак перед числом
        $s = 1;

        // Если трата, ставим минус
        if ($payment->type == 0) {
            $s = -1;
        }

        // Если отменяет, меняем знак
        if ($type == 'back') {
            $s = -1 * $s;
        }

        // Корректируем старый кошелек
        $query = $this->Database->placehold("UPDATE __finance_purses SET amount=amount+(?) WHERE id=? AND amount IS NOT NULL LIMIT 1", $s * $payment->amount, $payment->purse_id);
        return $this->Database->query($query);
    }


    /**
     * Выбираем категории платежей
     * @param $type - plus/munis || income/expense
     */
    public function get_categories($type = null)
    {
        $where_type = "";
        if (!is_null($type)) {

            // Переводим $type строку в число
            if (is_string($type)) {
                if ($type == 'plus' || $type == 'income') {
                    $type = 1;
                } elseif ($type == 'minus' || $type == 'expense') {
                    $type = 0;
                }
            }

            $where_type = $this->Database->placehold(" AND fc.type=?", intval($type));
        }

        $query = $this->Database->placehold(
            "SELECT 
                fc.* 
            FROM 
                __finance_categories AS fc 
            WHERE
                1 
                $where_type 
            ORDER BY 
                fc.position"
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Выбираем категорию платежей
     */
    public function get_category($id)
    {
        $query = $this->Database->placehold("SELECT * FROM __finance_categories WHERE id=? LIMIT 1", intval($id));
        $this->Database->query($query);
        $ategory = $this->Database->result();

        return $ategory;
    }


    /**
     * Добавляем финансовую категорию
     * @param $category
     */
    public function add_category($category)
    {
        $category = $this->Misc->cleanEntityId($category);
        $query = $this->Database->placehold("INSERT INTO __finance_categories SET ?%", $category);

        if (!$this->Database->query($query)) {
            return false;
        }

        $id = $this->Database->getInsertId();
        $this->Database->query("UPDATE __finance_categories SET position=id WHERE id=?", intval($id));
        return $id;
    }


    /**
     * Обновляем  данные финансовой категрии
     * @param $id - число или array()
     */
    public function update_category($id, $category)
    {
        $query = $this->Database->placehold("UPDATE __finance_categories SET ?% WHERE id in(?@)", $category, (array)$id);
        $this->Database->query($query);
        return $id;
    }


    /**
     * Удялем финансовую категорию
     * @param $id - id или array(id, id, ...)
     */
    public function deleteCategory($id)
    {

        // Очищаем взаимосвязанные таблицы
        $query = $this->Database->placehold("UPDATE __finance_payments SET finance_category_id=NULL WHERE finance_category_id in(?@)", (array)$id);
        $this->Database->query($query);

        $query = $this->Database->placehold("DELETE FROM __finance_categories WHERE id in(?@)", (array)$id);
        if ($this->Database->query($query)) {
            return true;
        }

        return false;
    }


    /**
     * Выбираем кошелки
     * @param $filter
     */
    public function getPurses($filter = array())
    {
        $where_enabled = "";
        if (!empty($filter['enabled'])) {
            $where_enabled = $this->Database->placehold(' AND fp.enabled=?', intval($filter['enabled']));
        }

        $query = $this->Database->placehold(
            "SELECT 
				fp.* ,
				c.sign as currency_sign 
			FROM 
				__finance_purses AS fp 
				LEFT JOIN __finance_currencies c ON fp.currency_id = c.id 
			WHERE 
				1 
				$where_enabled 
			ORDER BY 
				fp.position"
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Выбираем данные кошелка
     * @param $id
     */
    public function get_purse($id)
    {
        $query = $this->Database->placehold("SELECT * FROM __finance_purses WHERE id=? LIMIT 1", intval($id));
        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Добавляем кошелек
     * @param $purse
     */
    public function add_purse($purse)
    {
        $purse = $this->Misc->cleanEntityId($purse);

        $query = $this->Database->placehold("INSERT INTO __finance_purses SET ?%", $purse);

        if (!$this->Database->query($query)) {
            return false;
        }

        $id = $this->Database->getInsertId();
        $this->Database->query("UPDATE __finance_purses SET position=id WHERE id=?", intval($id));
        return $id;
    }


    /**
     * Обновляем данные кошелка
     * @param $id - число или array(id, id, ...)
     */
    public function update_purse($id, $purse)
    {
        $query = $this->Database->placehold("UPDATE __finance_purses SET ?% WHERE id in(?@)", $purse, (array)$id);
        $this->Database->query($query);
        return $id;
    }


    /**
     * Удялем кошелек
     */
    public function delete_purse($id)
    {
        // BUG нельзя удалять кошелки в которых есть платежи

        //$query = $this->Database->placehold("DELETE FROM __finance_purses WHERE id in(?@)", (array)$id);
        //if ($this->Database->query($query))
        //	return true;

        return false;
    }


    /**
     * Проверяем остаток на кошельке, сложив все платежи
     * @param $purse_id
     */
    public function check_purse_amount($purse_id)
    {
        $query = $this->Database->placehold(
            "SELECT 
				(SUM(IF(fp.type=1, fp.amount, 0)) - SUM(IF(fp.type=0, fp.amount, 0))) as check_amount 
			FROM 
				__finance_payments fp 
			WHERE
				fp.purse_id=?",
            intval($purse_id)
        );

        $this->Database->query($query);
        $result = $this->Database->result();

        return $result->check_amount;
    }


    /**
     * Общий баланс на кошелках
     * @param $currency_id
     */
    public function get_total_amount($currency_id)
    {
        if (empty($currency_id)) {
            return false;
        }

        $query = $this->Database->placehold(
            "SELECT 
				SUM(fp.amount) as total_amount
			FROM 
				__finance_purses fp 
			WHERE
				fp.currency_id=?",
            intval($currency_id)
        );

        $this->Database->query($query);
        $result = $this->Database->result();

        if (empty($result->total_amount)) {
            return 0;
        }
        return $result->total_amount;
    }


    /**
     * Ищем связь с сущностями
     * @param $id - ID Payment
     */
    public function get_contractor($id)
    {
        $query = $this->Database->placehold("SELECT * FROM __finance_entity_related WHERE payment_id=? LIMIT 1", intval($id));
        $this->Database->query($query);
        if (!$contractor = $this->Database->result()) {
            return false;
        }

        return $this->set_contractor_name($contractor);
    }


    /**
     * Определяем навзание Контрагента
     * @param $contractor - Object contractor
     */
    public function set_contractor_name($contractor)
    {
        $contractor->entity = new \stdClass();

        // Выбрать данные сущности контрагента
        switch ($contractor->entity_name) {
            case 'user':
                $contractor->entity = $this->Users->getUser($contractor->entity_id);
                break;
            case 'order':
                $contractor->entity->name = 'Заказ №' . $contractor->entity_id;
                break;
            case 'wh_movement':
                $contractor->entity->name = 'Складское перемещение №' . $contractor->entity_id;
                break;
        }

        return $contractor;
    }


    /**
     * Создание связи с сущностью
     */
    public function add_contractor($payment)
    {
        $relation = $this->get_contractor($payment->payment_id);

        if ($relation) {
            if ($payment->entity_name != $relation->entity_name || $payment->entity_id != $relation->entity_id) {
                $query = $this->Database->placehold("UPDATE __finance_entity_related SET ?% WHERE payment_id=? LIMIT 1", (array)$payment, intval($payment->payment_id));
                $this->Database->query($query);
            }
        } else {
            $query = $this->Database->placehold("INSERT INTO __finance_entity_related SET ?%", $payment);

            if (!$this->Database->query($query)) {
                return false;
            }
        }
    }


    /**
     * Удаление связи с сущностью
     * @param $id - ID payment
     */
    public function delete_contractor($id)
    {
        $query = $this->Database->placehold("DELETE FROM __finance_entity_related WHERE payment_id=? LIMIT 1", intval($id));
        return $this->Database->query($query);
    }


    /**
     * Поиск связи с пользователем
     * Применяется для отображения статистики Пользователя
     * @param $id - ID user
     */
    public function get_user_payments($id)
    {
        return $this->get_contractor_payments($id, 'user');
    }


    /**
     * Поиск связи с поставкой
     * @param $id - ID warehouse
     */
    public function get_warehouse_payments($id)
    {
        return $this->get_contractor_payments($id, 'wh_movement');
    }


    /**
     * Поиск связи с заказом
     * @param $id - ID order
     */
    public function get_payments_by_order($id)
    {
        return $this->get_contractor_payments($id, 'order');
    }


    /**
     * Поиск платежа с оплатой заказа
     * fp.type = 1 - Приход (+)
     * @param $id - ID order
     * @param $payment_type - Тип платежа (расход/приход)|(expense/income)|(0/1)|(-/+)
     */
    public function get_payment_by_order($id, $payment_type = null)
    {
        $where_payment_type = "";
        if (!is_null($payment_type)) {

            // Определяем тип платежа
            if (in_array($payment_type, array("+", "income"))) {
                $payment_type = 1;
            } elseif (in_array($payment_type, array("-", "expense"))) {
                $payment_type = 0;
            }

            $where_payment_type = $this->Database->placehold(" AND fp.type=? ", $payment_type);
        }

        $query = $this->Database->placehold(
            "SELECT 
				fp.* 
			FROM 
				__finance_entity_related fre 
				LEFT JOIN __finance_payments fp ON fre.payment_id=fp.id 
			WHERE 
				fre.entity_id=? 
                AND fre.entity_name='order' 
                $where_payment_type
			LIMIT 
				1",
            intval($id)
        );

        $this->Database->query($query);
        return $this->Database->result();
    }


    /**
     * Удаление платежей заказа
     * @param $id - ID order
     */
    public function delete_payments_by_order($id)
    {
        foreach ($this->get_payments_by_order($id) as $payment) {
            if (!$this->deletePayment($payment->payment_id)) {
                return false;
            }
        }
        return true;
    }


    /**
     * Поиск связи с контрагентом
     * @param $entity_name: wh_movement, user, order
     * @return array(payment_id, entity_id, entity_name)
     */
    public function get_contractor_payments($entity_id, $entity_name)
    {

        $query = $this->Database->placehold(
            "SELECT 
                * 
            FROM 
                __finance_entity_related 
            WHERE 
                entity_id=? 
                AND entity_name=?",
            intval($entity_id),
            $entity_name
        );

        $this->Database->query($query);
        return $this->Database->results();
    }
}
