<?php

/**
 * GoodGin CMS - The Best of gins
 *
 * @author Andi Huga
 * @version 3.2
 *
 */

namespace GoodGin;

class UsersNotify extends GoodGin
{
    public $message_types = [
        'users' => [
            'newOrderToUser' => 'О новом заказе',
            'deliveryTrackNumber' => 'Трэк номер доставка',
            'paymentInfo' => 'Реквизиты об оплате'
        ],
        'admmin' => [
            'commentToAdmin' => 'Новый Комментарий',
            'feedbackToAdmin' => 'Новый Отзыв',
            'newOrderToAdmin' => 'Новый Заказ',
        ],
        'requared' => [
            'userPasswordRemind'
        ]
    ];


    /**
     * Send notification via Module
     *
     * @param string $module_name
     * @param string $message_type
     * @param array $message_params
     */
    public function sendNotify(String $module_name, String $message_type, array $message_params)
    {
        $current_module_dir = $this->Config->notify_dir . "$module_name/";
        $current_module_tpl_path = $current_module_dir . "templates/$message_type.tpl";
        $current_module_path = $current_module_dir . "$module_name.php";

        // Select Smarty template, module file, module name
        if (empty($module_name) || !is_file($current_module_path) || !is_file($current_module_tpl_path)) {
            return false;
        }

        // Проверить есть ли такой method (message function)
        if (!method_exists($this, $message_type)) {
            return false;
        }

        // Get module settings
        $notify_settings = $this->getNotifySettings($module_name);
        $message_params = array_merge((array) $notify_settings, $message_params);

        // Fetch template
        $message_content = $this->$message_type($current_module_tpl_path, $message_params);

        include_once($current_module_path);
        $notifyModule = new $module_name();

        // Run
        return $notifyModule->send($message_content, $message_params);
    }


    /**
     * Send Notify manager. Select avaliable notify Method
     * Send notification anly to Managers
     * @param string $message_type
     * @param array $message_params
     */
    public function sendNotifyToManager(string $message_type, array $message_params)
    {
        if (empty($message_type)) {
            return false;
        }

        // User List to notify
        $users_manager = $this->Users->getUsers(['manager' => 1]);
        foreach ($users_manager as $user) {
            $message_params['user'] = $user;

            // Get avaliable notify modules for User
            $user_notify_types = $this->UsersNotify->getUserNotifyTypes($user->id, $message_type);
            foreach ($user_notify_types as $notify_id => $types) {
                $notify = $this->getNotify($notify_id);
                $this->sendNotify($notify->module, $message_type, $message_params);
            }
        }
        return true;
    }


    /**
     * Send Comment to Admin
     * @param string $template_path
     * @param array $message_params
     */
    public function commentToAdmin(string $template_path, array &$message_params)
    {

        if (empty($message_params['comment_id']) || empty($comment = $this->Comments->getComment(intval($message_params['comment_id'])))) {
            return false;
        }

        if ($comment->type == 'product') {
            $comment->product = $this->Products->get_product(intval($comment->entity_id));
        }
        if ($comment->type == 'blog') {
            $comment->post = $this->Blog->getPost(intval($comment->entity_id));
        }

        $this->Design->assign([
            'comment' => $comment
        ]);

        // Image template
        $template = $this->Design->fetch($template_path);
        $message_params['subject'] = $this->Design->getVar('subject');

        return $template;
    }


    /**
     * Send Feedback to Admin
     * @param string $template_path
     * @param array $message_params
     */
    public function feedbackToAdmin(string $template_path, array &$message_params)
    {
        if (empty($message_params['feedback_id']) || empty($feedback = $this->Feedbacks->getFeedback(intval($message_params['feedback_id'])))) {
            return false;
        }

        $this->Design->assign([
            'feedback' => $feedback
        ]);

        // Image template
        $template = $this->Design->fetch($template_path);
        $message_params['subject'] = $this->Design->getVar('subject');

        return $template;
    }


    /**
     * Notification adout New Order for Admmin
     * @param string $template_path
     * @param array $message_params
     */
    private function newOrderToAdmin(string $template_path, array &$message_params)
    {
        if (empty($message_params['order_id']) || empty($order = $this->Orders->getOrder(intval($message_params['order_id'])))) {
            return false;
        }

        $purchases = $this->Orders->getPurchases(array('order_id' => $order->id));

        $products_ids = array();
        $variants_ids = array();

        foreach ($purchases as $purchase) {
            $products_ids[] = $purchase->product_id;
            $variants_ids[] = $purchase->variant_id;
        }

        $products = $this->Products->get_products(array('id' => $products_ids));

        $images = $this->Images->getImages($products_ids, 'product');
        foreach ($images as $image) {
            $products[$image->entity_id]->images[] = $image;
        }

        $variants = array();
        foreach ($this->ProductsVariants->getVariants(array('id' => $variants_ids)) as $v) {
            $variants[$v->id] = $v;
            $products[$v->product_id]->variants[] = $v;
        }

        foreach ($purchases as &$purchase) {
            if (!empty($products[$purchase->product_id])) {
                $purchase->product = $products[$purchase->product_id];
            }
            if (!empty($variants[$purchase->variant_id])) {
                $purchase->variant = $variants[$purchase->variant_id];
            }
        }

        // Get Delivery and Payment methods
        $delivery_method = $this->OrdersDelivery->getDeliveryMethod($order->delivery_id);
        $payment_method = $this->OrdersPayment->getPaymentMethod($order->payment_method_id);

        $this->Design->assign([
            'order' => $order,
            'purchases' => $purchases,
            'payment_method' => $payment_method,
            'delivery_method' => $delivery_method
        ]);

        // Image template
        $template = $this->Design->fetch($template_path);
        $message_params['subject'] = $this->Design->getVar('subject');

        // Link in Button
        $message_params['url_text'] = $this->Design->getVar('url_text');
        $message_params['url'] = $this->Design->getVar('url');

        return $template;
    }


    /**
     * Notification adout New Order for User
     * @param string $template_path
     * @param array $message_params
     */
    public function newOrderToUser(string $template_path, array &$message_params)
    {

        if (empty($message_params['order_id']) || empty($order = $this->Orders->getOrder(intval($message_params['order_id']))) || empty($order->email)) {
            return false;
        }

        $purchases = $this->Orders->getPurchases(array('order_id' => $order->id));

        $products_ids = array();
        $variants_ids = array();

        foreach ($purchases as $purchase) {
            $products_ids[] = $purchase->product_id;
            $variants_ids[] = $purchase->variant_id;
        }

        $products = $this->Products->get_products(array('id' => $products_ids));

        $images = $this->Images->getImages($products_ids, 'product');
        foreach ($images as $image) {
            $products[$image->entity_id]->images[] = $image;
        }

        $variants = array();
        foreach ($this->ProductsVariants->getVariants(array('id' => $variants_ids)) as $v) {
            $variants[$v->id] = $v;
            $products[$v->product_id]->variants[] = $v;
        }

        foreach ($purchases as &$purchase) {
            if (!empty($products[$purchase->product_id])) {
                $purchase->product = $products[$purchase->product_id];
            }
            if (!empty($variants[$purchase->variant_id])) {
                $purchase->variant = $variants[$purchase->variant_id];
            }
        }

        // Get Delivery and Payment methods
        $payment_method = $this->OrdersPayment->getPaymentMethod($order->payment_method_id);
        $delivery_method = $this->OrdersDelivery->getDeliveryMethod($order->delivery_id);


        $this->Design->assign([
            'order' => $order,
            'purchases' => $purchases,
            'payment_method' => $payment_method,
            'payment_method' => $delivery_method
        ]);

        // Image template
        $template = $this->Design->fetch($template_path);
        $message_params['subject'] = $this->Design->getVar('subject');

        return $template;
    }


    /**
     * Send code for Passwords Remind
     * @param string $template_path
     * @param array $message_params
     */
    public function userPasswordRemind(string $template_path, array &$message_params)
    {

        if (empty($message_params['user_id']) || empty($user = $this->Users->getUser($message_params['user_id']))) {
            return false;
        }

        $this->Design->assign([
            'user' => $user,
            'code' => $message_params['code']
        ]);

        // Image template
        $template = $this->Design->fetch($template_path);
        $message_params['subject'] = $this->Design->getVar('subject');

        $this->Design->smarty->clearAssign('user');
        $this->Design->smarty->clearAssign('code');

        return $template;
    }


    /**
     * Send Delivey Track Number to User
     * @param string $template_path
     * @param array $message_params
     */
    public function deliveryTrackNumber(string $template_path, array &$message_params)
    {

        if (empty($message_params['order_id']) || empty($order = $this->Orders->getOrder(intval($message_params['order_id'])))) {
            return false;
        }

        $message_params['order'] = $order;

        $this->Design->assign([
            'order' => $order
        ]);

        // Image template
        $template = $this->Design->fetch($template_path);

        return $template;
    }


    /**
     * Send Payment Details to User
     * @param string $template_path
     * @param array $message_params
     */
    public function paymentDetails(string $template_path, array &$message_params)
    {

        if (empty($message_params['order_id']) || empty($order = $this->Orders->getOrder(intval($message_params['order_id'])))) {
            return false;
        }

        $message_params['order'] = $order;

        // Выбираем указаный способ оплаты
        $payment_method = $this->OrdersPayment->getPaymentMethod($order->payment_method_id);
        $payment_currency = $this->Money->getCurrency(intval($payment_method->currency_id));
        $payment_settings = $this->OrdersPayment->getPaymentMethodSettings($order->payment_method_id);

        $this->Design->assign([
            'order' => $order,
            'payment_method' => $payment_method,
            'payment_currency' => $payment_currency,
            'payment_settings' => $payment_settings
        ]);

        // Image template
        $template = $this->Design->fetch($template_path);

        return $template;
    }



    //--------------------------------------------------------------------------
    //--------------------------------------------------------- Database
    //--------------------------------------------------------------------------

    /**
     * Выбираем информацию о способе оплаты
     * @param int $id
     */
    public function getNotify(int $id = null): object|bool
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold(
            "SELECT 
                n.id, 
                n.name, 
                n.comment, 
                n.module, # Module (Email, Telegram)
                n.settings, 
                n.position, 
                n.enabled
            FROM 
                __users_notify as n 
            WHERE 
                n.id=? 
            LIMIT 
                1",
            intval($id)
        );

        $this->Database->query($query);
        $payment_method = $this->Database->result();

        // Преобразовывем Settings json в object
        if (!empty($payment_method->settings)) {
            $payment_method->settings = (object) unserialize($payment_method->settings);
        }

        return $payment_method;
    }


    /**
     * Get Notify Method List
     * @param array $filter
     */
    public function getNotifyList(array $filter = array())
    {

        $where_enabled = '';
        if (!empty($filter['enabled'])) {
            $where_enabled = $this->Database->placehold('AND enabled=?', intval($filter['enabled']));
        }

        $where_ids = '';
        if (!empty($filter['ids'])) {
            $where_ids = $this->Database->placehold('AND id in(?@)', (array)$filter['ids']);
        }

        $query =
            "SELECT
			 	*
			FROM 
				__users_notify 
			WHERE 
				1 
				$where_enabled 
                $where_ids
			ORDER BY 
				position
			";

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Add Notify method
     * @param object $notify
     */
    public function addNotify(object $notify)
    {
        $notify = $this->Misc->cleanEntityId($notify);

        $query = $this->Database->placehold(
            "INSERT INTO 
                __users_notify
		    SET 
                ?%",
            $notify
        );

        if (!$this->Database->query($query)) {
            return false;
        }

        $id = $this->Database->getInsertId();

        $this->Database->query("UPDATE __users_notify SET position=id WHERE id=?", $id);
        return $id;
    }


    /**
     * Update Notify Method
     */
    public function updateNotify(int $id, $notify)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("UPDATE __users_notify SET ?% WHERE id in(?@)", $notify, (array)$id);
        $this->Database->query($query);
        return $id;
    }


    /**
     * Delete Notify method
     */
    public function deleteNotify($id)
    {
        if (empty($id)) {
            return false;
        }

        $query = $this->Database->placehold("DELETE FROM __users_notify WHERE id=? LIMIT 1", intval($id));
        return $this->Database->query($query);
    }


    /**
     * Update Notify module settings
     * @return int $id
     */
    public function updateNotifySettings(int $id, $settings)
    {
        if (empty($id)) {
            return false;
        }

        if (!is_string($settings)) {
            $settings = serialize($settings);
        }
        $query = $this->Database->placehold("UPDATE __users_notify SET settings=? WHERE id in(?@) LIMIT 1", $settings, (array)$id);
        $this->Database->query($query);
        return $id;
    }


    /**
     * Выбираем модули оповещения
     * Переменные в файле settings.xml
     */
    public function getNotifyModules()
    {

        $modules_dir = $this->Config->notify_dir;

        $modules = array();
        $handler = opendir($modules_dir);

        while ($dir = readdir($handler)) {
            $dir = preg_replace("/[^A-Za-z0-9]+/", "", $dir);
            if (!empty($dir) && $dir != "." && $dir != ".." && is_dir($modules_dir . $dir)) {

                if (is_readable($modules_dir . $dir . '/settings.xml') && $xml = simplexml_load_file($modules_dir . $dir . '/settings.xml')) {

                    $module = new \stdClass();
                    $module->name = (string)$xml->name;
                    $module->settings = array();

                    foreach ($xml->settings as $setting) {
                        $module->settings[(string)$setting->variable] = new \stdClass();
                        $module->settings[(string)$setting->variable]->name = (string)$setting->name;
                        $module->settings[(string)$setting->variable]->variable = (string)$setting->variable;
                        $module->settings[(string)$setting->variable]->variable_options = array();

                        foreach ($setting->options as $option) {
                            $module->settings[(string)$setting->variable]->options[(string)$option->value] = new \stdClass();
                            $module->settings[(string)$setting->variable]->options[(string)$option->value]->name = (string)$option->name;
                            $module->settings[(string)$setting->variable]->options[(string)$option->value]->value = (string)$option->value;
                        }

                        // input file
                        if (!empty($setting->type)) {
                            $module->settings[(string)$setting->variable]->type = (string)$setting->type;
                        }
                    }
                    $modules[$dir] = $module;
                }
            }
        }
        closedir($handler);
        return $modules;
    }


    /**
     * Get Notify Method settings
     * @param int|string $method_id
     * @return object
     */
    public function getNotifySettings(int|string $id = null): object|bool
    {
        if (empty($id)) {
            false;
        }

        if (is_int($id)) {
            $where_id = $this->Database->placehold(' AND id=? ', intval($id));
        } else {
            $where_id = $this->Database->placehold(' AND module=? ', strval($id));
        }

        $query = $this->Database->placehold("SELECT settings FROM __users_notify WHERE 1 $where_id LIMIT 1");
        $this->Database->query($query);
        $settings = $this->Database->result('settings'); # json

        // преобразовывем json в object
        return (object) unserialize($settings);
    }


    /**
     * Get notify types for entity
     */
    public function getNotifyTypes(string $entity)
    {
        if (isset($this->message_types[$entity])) {
            return $this->message_types[$entity];
        }
        return false;
    }


    /**
     * Update User Notify messages Tupes
     * @param int $user_id
     * @param array|bool $notify_types
     */
    public function updateUserNotifyTypes(int $user_id, array|null $notify_types)
    {

        // Delete all notify types
        $query = $this->Database->placehold("DELETE FROM __users_notify_types WHERE user_id=?", intval($user_id));
        $this->Database->query($query);

        if (!empty($notify_types) && is_array($notify_types)) {
            $values = array();

            foreach ($notify_types as $notify_id => $types) {
                foreach ($types as $type) {
                    if (!empty($type)) {
                        $values[] = "($user_id, $notify_id, '$type')";
                    }
                }
            }

            $query = $this->Database->placehold("INSERT INTO __users_notify_types (`user_id`, `notify_id`, `type`) VALUES " . join(', ', $values));
            return $this->Database->query($query);
        }
        return true;
    }


    /**
     * Get notify type for User
     * @param int $user_id
     */
    public function getUserNotifyTypes(int $user_id, string $type = null): array
    {
        $where_type = '';
        if (!empty($type)) {
            $where_type = $this->Database->placehold(' AND unt.type=? ', $type);
        }

        $query = $this->Database->placehold(
            "SELECT 
				unt.user_id,
                unt.notify_id,
                unt.type 
			FROM 
				__users_notify_types unt
			WHERE 
				unt.user_id=?
                $where_type",
            intval($user_id)
        );

        $this->Database->query($query);
        $user_notify_types = $this->Database->results();

        // Collect by notify_id
        $user_notify_types_arr = [];
        foreach ($user_notify_types as $unt) {
            $user_notify_types_arr[$unt->notify_id][] = $unt->type;
        }

        return $user_notify_types_arr;
    }
}
