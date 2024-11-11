{include file='orders/orders_menu_part.tpl'}

{if $order->id}
	{$meta_title = "Заказ №`$order->id`" scope=global}
{else}
	{$meta_title = 'Новый заказ' scope=global}
{/if}

{if $message_error}
	<!-- Системное сообщение -->
	<div class="message message_error">
		<span class="text">
			{if $message_error == 'error_closing'}
				Нехватка товара на складе
			{elseif $message_error == 'error_paid'}
				Выберите способ оплаты
			{else}
				{$message_error|escape}
			{/if}
		</span>
	</div>
{elseif $message_success}
	<div class="message message_success">
		<span class="text">
			{if $message_success=='updated'}Заказ обновлен
			{elseif $message_success=='added'}Заказ
			добавлен{else}{$message_success}
			{/if}
		</span>
	</div>
{/if}


<form method="post" id="order" enctype="multipart/form-data" class="form_css two_columns_order">
	<input type="hidden" name="session_id" value="{$smarty.session.id}" />

	<div class="header_top">
		<input name="id" type="hidden" value="{$order->id}" />
		<input name="manager_id" type="hidden" value="{$order->manager_id}" />

		<h1>{$meta_title}
			<select class="status" name="status" {if !$can_edit}disabled{/if}>
				<option value="0" {if $order->status == 0}selected{/if}>Новый</option>
				<option value="1" {if $order->status == 1}selected{/if}>Принят</option>
				<option value="4" {if $order->status == 4}selected{/if}>Отгружен</option>
				<option value="2" {if $order->status == 2}selected{/if}
					{if !$user|user_access:orders_edit}disabled{/if}>Выполнен</option>
				<option value="3" {if $order->status == 3}selected{/if}>Отмена</option>
			</select>

			{if !$prev_order->id|empty}
				<a class="out_link" href="{url view=OrderAdmin id=$prev_order->id}" title="№{$prev_order->id}">Перейти к
					следуюшему заказу</a>
			{/if}
		</h1>

		<a class="print_icon fl_r" href="/order/{$order->url}?type=print" target="_blank">
			<img src="/{$config->templates_subdir}images/printer.png" title="Печать заказа">
		</a>
	</div>


	<div id="order_details">
		<h2>Детали заказа
			{if $can_edit}
				<a href="#" class="edit_order_details">
					<img src="/{$config->templates_subdir}images/pencil.png" alt="Редактировать" title="Редактировать">
				</a>
			{/if}
		</h2>

		<div class="order_date_time">

			{if !$order_manager->id|empty}
				<div class="order_manager">
					Менеджер: <a href="{url view=UserAdmin id=$order_manager->id clear=true}"
						target="_blank">{$order_manager->name|escape}</a>
					{if $order_manager->interest_price AND ($user->id == $order_manager->id OR $user|user_access:orders_finance)}
						<span class="manager_profit" title="Комиссия менеджера с продажи">
							{if $order_manager->interest_price>0}+{/if}{$order_manager->interest_price|convert}<span
								class="price_sign">{$currency->sign}</span>
							{if $order_manager->interest_discount>0}
								<span class="profit_dicount" title="% за заказ">
									{$order_manager->interest_discount|price_format:1}%
								</span>
							{/if}
						</span>
					{/if}
				</div>
			{/if}

			{if !$order->date|empty}
				<div>
					Создан {$order->date|date} в {$order->date|time} <span class="fl_r">Изменён {$order->modified|date} в
						{$order->modified|time}</span>
				</div>
			{/if}
		</div>

		<div id="user">
			<ul>
				<li>
					<label class="property_name" for="id">Имя</label>
					<div class="edit_order_detail" style="display:none;">
						<input name="name" id="id" type="text" autocomplete="off" value="{$order->name|escape}" />
					</div>
					<div class="view_order_detail">
						{$order->name|escape}
					</div>
				</li>
				<li>
					<label class="property_name" for="email">Email</label>
					<div class="edit_order_detail" style="display:none;">
						<input name="email" id="email" type="text" autocomplete="off" value="{$order->email|escape}" />
					</div>
					<div class="view_order_detail">
						<a href="mailto:{$order->email|escape}?subject=Заказ%20№{$order->id}">{$order->email|escape}</a>
					</div>
				</li>
				<li>
					<label class="property_name" for="phone">Телефон</label>
					<div class="edit_order_detail" style="display:none;">
						<input name="phone" id="phone" type="text" autocomplete="off" value="{$order->phone|escape}" />
					</div>
					<div class="view_order_detail">
						{if $order->phone}
							<a class="ip_call" data-phone="{$order->phone|escape}" target="_blank"
								href="tel:{$order->phone|escape}">{$order->phone|escape}</a>
						{else}
							{$order->phone|escape}
						{/if}
					</div>
				</li>
				<li>
					<label class="property_name" for="address">Город</label>
					<div class="edit_order_detail" style='display:none;'>
						<input name="address" id="address" type="text" autocomplete="off"
							value="{$order->address|escape}" />
					</div>
					<div class="view_order_detail">
						{$order->address|escape}
					</div>
				</li>
				<li>
					<label class="property_name" for=comment>Комментарий пользователя</label>
					<div class="edit_order_detail edit_comment" style='display:none;'>
						<textarea name="comment" id=comment>{$order->comment|escape}</textarea>
					</div>
					<div class="view_order_detail edit_comment">
						{$order->comment|escape|nl2br}
					</div>
				</li>
			</ul>
		</div>

		<div class="note_wrap mt_20">
			<h2>Примечание
				{if $can_edit}
					<a href="#" class="edit_note"><img src="/{$config->templates_subdir}images/pencil.png"
							alt='Редактировать' title='Редактировать'></a>
				{/if}
			</h2>
			<ul class="note_block">
				<li>
					<div class="edit_note" style="display:none;">
						<div class="property_name">Ваше примечание (не видно клиенту)</div>
						<textarea name="note">{$order->note|escape}</textarea>
					</div>
					<div class="view_note" {if !$order->note}style="display:none;" {/if}>
						<div class="property_name">Ваше примечание (не видно клиенту)</div>
						<div class="note_text">{$order->note|escape}</div>
					</div>
				</li>
			</ul>
		</div>


		{if $labels}
			<div class='layer'>
				<h2>Метка</h2>
				<ul class="menu_list">
					{foreach $labels as $l}
						{if ($l->enabled OR $user|user_access:orders_edit)}
							<li class="label {if !$l->enabled}disabled{/if}">
								<label for="label_{$l->id}" style="background-color:#{$l->color};"
									class="{if !$l->enabled}disabled{/if}">
									<input id="label_{$l->id}" type="checkbox" name="order_labels[]" value="{$l->id}"
										{if in_array($l->id, $order_labels)}checked{/if} {if !$can_edit}disabled{/if} />
									<span>{$l->name}</span>
								</label>
							</li>
						{/if}
					{/foreach}
				</ul>
			</div>
		{/if}


		<div class="layer mt_20">
			<h2>Покупатель
				{if $can_edit}
					<a href="#" class="edit_user"><img src="/{$config->templates_subdir}images/pencil.png"
							alt="Редактировать" title="Редактировать"></a>
					{if $order_user}
						<a href="#" class="delete_user"><img src="/{$config->templates_subdir}images/delete.png" alt="Удалить"
								title="Удалить"></a>
					{/if}
				{/if}
			</h2>

			<div class="view_user">
				{if !$order_user}
					Не зарегистрирован
				{else}
					<a href="{url view=UserAdmin id=$order_user->id clear=true}">{$order_user->name|escape}</a>
					{if $order_user->group_name}
						<div>{$order_user->group_name}</div>
					{/if}
				{/if}
			</div>

			<div class="edit_user" style="display:none;">
				<input type="hidden" name="user_id" value="{$order_user->id}" />
				<input type="text" id="user_id" class="input_autocomplete" placeholder="Выберите пользователя" />
			</div>

			{if !$order->ip|empty}
				<div class="order_ip">IP: {$order->ip} (<a href='https://www.ipaddress.com/ipv4/{$order->ip}'
						target="_blank">где это?</a>)
				</div>
			{/if}

		</div>
	</div>


	<!-- Товары заказа -->
	<div id="purchases">
		<div class="list purchases">

			{foreach $purchases as $purchase}
				<div class="row">

					<div class="move">
						<div class="move_zone"></div>
					</div>

					<div class="image">
						<input type="hidden" name="purchases[id][]" value="{$purchase->id}" />
						{$image = $purchase->product->images|first}
						<img class="product_icon"
							src="{if $image}{$image->filename|resize:50:50}{else}{$config->templates_subdir}images/cargo.png{/if}"
							title="{$purchase->variant_name}" />
					</div>

					<div class="name product_name">
						{if $purchase->product}
							<a
								href="{url view=ProductPriceAdmin id=$purchase->product->id return=$smarty.server.REQUEST_URI}">{$purchase->product_name}</a>
							<div class="icons">
								<a class="external_link" title="Предпросмотр в новом окне"
									href="../product/{$purchase->product->id}" target="_blank"></a>
							</div>
						{else}
							{$purchase->product_name}
						{/if}
					</div>

					<div class="purchase_variant">
						<div class="edit_purchase" style="display:none;">
							<select name="purchases[variant_id][]"
								{if $purchase->product->variants|count == 1 && $purchase->variant_name == '' && $purchase->variant->sku == ''}
								style="display:none;" {/if}>

								{* Если вариант удален, показываем сохраненный в заказе *}
								{if !$purchase->variant}
									<option price="{$purchase->price}" cost_price="{$purchase->cost_price}"
										amount="{$purchase->amount}" value="">
										{$purchase->variant_name|escape} {if $purchase->sku}(арт. {$purchase->sku}){/if}
									</option>
								{/if}

								{foreach $purchase->product->variants as $v}
									{if $v->stock > 0 || $v->id == $purchase->variant->id}
										<option price="{$v->price}" cost_price="{$v->cost_price}" amount="{$v->stock}"
											weight="{$v->weight}" value="{$v->id}"
											{if $v->id == $purchase->variant_id}selected{/if}>
											{$v->name} {if $v->sku}(арт. {$v->sku}){/if}
										</option>
									{/if}
								{/foreach}
							</select>
						</div>

						<div class="view_purchase">
							<i
								title="{$purchase->variant_name|escape}">{$purchase->variant_name|escape|truncate:20:'…':true:false}</i>
							{if $purchase->sku}
								<div class="sku">{$purchase->sku}</div>
							{/if}
						</div>
					</div>

					<div class="price">

						{if $user|user_access:orders_finance}
							<div class="cost_price">
								<span class="js_change">{$purchase->cost_price}</span>
								<span class="price_sign">{$currency->sign}</span>
							</div>
						{/if}

						<div>
							<div class="view_purchase">
								{$purchase->price|convert}<span class="price_sign">{$currency->sign}</span>
							</div>
							<div class="edit_purchase" style="display:none;">
								<input type="text" name="purchases[price][]" value="{$purchase->price}" size="5"
									{if !$user|user_access:orders_finance} disabled {/if} /> {$currency->sign}
							</div>
						</div>
					</div>


					<div class="amount">
						<div class="amount_waight">
							<span class="js_change">{$purchase->variant->weight * $purchase->amount}</span>
							<span class="price_sign">{$settings->weight_units}</span>
						</div>

						<div>
							<div class="view_purchase">
								{$purchase->amount} {$settings->units}
							</div>

							<div class="edit_purchase" style="display:none;">

								{if $purchase->variant}
									{math equation="min(max(x,y),z)" x=$purchase->variant->stock+$purchase->amount*($order->closed) y=$purchase->amount z=$settings->max_order_amount assign="loop"}
								{else}
									{math equation="x" x=$purchase->amount assign="loop"}
								{/if}

								<select name="purchases[amount][]">
									{section name=amounts start=1 loop=$loop+1 step=1}
										<option value="{$smarty.section.amounts.index}"
											{if $purchase->amount==$smarty.section.amounts.index}selected{/if}>
											{$smarty.section.amounts.index} {$settings->units}</option>
									{/section}
								</select>
							</div>
						</div>
					</div>

					<div class="stock">
						{if $purchase->variant->movements_amount}
							<div class="wmovements"
								title="{foreach $purchase->variant->movements as $mov}Поставка №{$mov->movement_id} | {$mov->awaiting_date} | +{$mov->amount}&#013;{/foreach}">
								+{$purchase->variant->movements_amount}</div>
						{/if}

						<div class="variant_stock">остаток: <span class="js_change">{$purchase->variant->stock}</span></div>
					</div>

					<div class="icons">

						{if !$order->closed}
							{if !$purchase->product}
								<img src="/{$config->templates_subdir}images/error.png" alt="Товар был удалён"
									title="Товар был удалён">
							{elseif !$purchase->variant}
								<img src="/{$config->templates_subdir}images/error.png" alt="Вариант товара был удалён"
									title="Вариант товара был удалён">
							{elseif $purchase->variant->stock < $purchase->amount}
								<img src="/{$config->templates_subdir}images/error.png"
									alt='На складе остал{$purchase->variant->stock|plural:'ся':'ось'} {$purchase->variant->stock} товар{$purchase->variant->stock|plural:'':'ов':'а'}'
									title='На складе остал{$purchase->variant->stock|plural:'ся':'ось'} {$purchase->variant->stock} товар{$purchase->variant->stock|plural:'':'ов':'а'}'>
							{/if}
						{/if}

						{if $can_edit}
							<a href="#" class="delete" title="Удалить"></a>
						{/if}

					</div>
				</div>
			{/foreach}


			<div id="new_purchase" class="row" style="display:none;">
				<div class="move">
					<div class="move_zone"></div>
				</div>

				<div class="image">
					<input type="hidden" name="purchases[id][]" value="">
					<img class="product_icon" src="">
				</div>

				<div class="name product_name">
					<a class="add_name" href=""></a>
				</div>

				<div class="purchase_variant">
					<select name="purchases[variant_id][]" style="display:none;"></select>
				</div>

				<div class="price">
					{if $user|user_access:orders_finance}
						<div class="cost_price">
							<span class="js_change"></span>
							<span class="price_sign">{$currency->sign}</span>
						</div>
					{/if}
					<div>
						<input type="text" name="purchases[price][]" value="" size="5"
							{if !$user|user_access:orders_finance} disabled {/if} /> {$currency->sign}
					</div>
				</div>

				<div class="amount">
					<div class="amount_waight">
						<span class="js_change"></span>
						<span class="price_sign">{$settings->weight_units}</span>
					</div>
					<select name="purchases[amount][]"></select>
				</div>

				<div class="stock">
					<div class="variant_stock">остаток: <span class="js_change"></span></div>
				</div>

				<div class="icons">
					<a href="#" class="delete" title="Удалить"></a>
				</div>
			</div>

		</div>

		<div id="add_purchase" {if $purchases}style='display:none;' {/if}>
			<input type="text" id="add_purchase" class="input_autocomplete"
				placeholder="Выберите товар чтобы добавить его" />
		</div>

		{if $purchases AND $can_edit}
			<span class="dash_link edit_purchases">редактировать покупки</span>
		{/if}

		{if $purchases}
			<div class="subtotal">
				<div class="over_line">{$total->purchases} {$settings->units} {$total->weight}
					{$settings->weight_units}
				</div>
				<div class="main_line">
					Всего: <b>{$total->purchases_price|convert} {$currency->sign}</b>
				</div>
			</div>
		{/if}


		<!-- Скидка и купоны -->
		<div class="discount_block">
			<div class="discount mt_20">
				<span class="disciount_name">Скидка</span>
				<input {if !$can_edit}disabled{/if} type="text" name="discount" value="{$order->discount}"
					autocomplete='off' /> <span class="currency">%</span>

				{if $order->discount>0}
					<span class="discount_price">
						<b class="disount_amount"> —{($total->purchases_price*($order->discount/100))|convert}
							<span class="currency">{$currency->sign}</span></b>
					</span>
				{/if}
			</div>

			<div class="discount">
				<span class="disciount_name">Купон {if !$order->coupon_code|empty}({$order->coupon_code}){/if}</span>
				<input {if !$can_edit}disabled{/if} type="text" name="coupon_discount" value="{$order->coupon_discount}"
					autocomplete='off' />
				<span class="currency">{$currency->sign}</span>
			</div>

			{if $order->discount > 0 || $order->coupon_discount > 0}
				<div class="subtotal discount_total">
					<div class="over_line">Скидка:<span>
							-{$total->purchases_price * ($order->discount / 100) + $order->coupon_discount}
							{$currency->sign}</span>
					</div>
					<div class="main_line">
						Итого: <b>
							{($total->purchases_price - $total->purchases_price * ($order->discount / 100) - $order->coupon_discount)|convert}
							{$currency->sign}</b>
					</div>
				</div>
			{/if}
		</div>


		<!-- Доставка -->
		<div class="delivery layer">
			<h2>Доставка</h2>
			<select name="delivery_id" {if !$can_edit}disabled{/if}>
				<option value="">Не выбрана</option>
				{foreach $deliveries as $d}
					{if $d->enabled || $d->id == $delivery->id}
						<option value="{$d->id}" {if !$delivery->id|empty and $d->id == $delivery->id}selected{/if}>
							{$d->name|escape}</option>
					{/if}
				{/foreach}
			</select>

			<input {if !$can_edit}disabled{/if} type="text" name="delivery_price" value="{$order->delivery_price}"
				autocomplete='off' />
			<span class="currency">{$currency->sign}</span>

			<div class="checkbox_item separate_delivery">
				<input {if !$can_edit}disabled{/if} type=checkbox id="separate_delivery" name="separate_delivery"
					value="1" {if $order->separate_delivery}checked{/if} />
				<label for="separate_delivery">Оплачивается отдельно</label>
			</div>

			<!-- Модуль доставки -->
			{if !$delivery->module|empty}
				<div class="delivery_method_module">
					{get_delivery_module_html order_id=$order->id module=$delivery->module view_type='admin'}
				</div>
			{/if}
		</div>


		<!-- Оплата -->
		<div class="payment layer">
			<h2>Оплата</h2>
			<select name="payment_method_id" {if !$can_edit}disabled{/if}>
				<option value="">Не выбрана</option>
				{if !$payment_methods|empty}
					{foreach $payment_methods as $pm}
						{if $pm->enabled || $pm->enabled_public || $pm->id == $payment_method->id}
							<option class="{if !$pm->enabled_public}disabled{/if}" value="{$pm->id}"
								{if !$payment_method->id|empty and $pm->id == $payment_method->id}selected{/if}>{$pm->name}</option>
						{/if}
					{/foreach}
				{/if}
			</select>

			<div class="checkbox_item {if $order->paid}paid{/if}">
				<input type="checkbox" name="paid" id="paid" value="1" {if !$can_edit}disabled{/if}
					{if $order->paid}checked{/if} />
				<label for="paid">Заказ оплачен</label>
			</div>

			<!-- Модуль оплаты -->
			{if !$payment_method->module|empty}
				<div class="payment_method_module">
					{get_payment_module_html order_id=$order->id module=$payment_method->module view_type='admin'}
				</div>
			{/if}
		</div>


		<div class="total">

			{if $user|user_access:orders_finance}
				{if $order->profit_price|isset}
					<div class="over_line">
						<span class="profit_price {if $order->profit_price < 0}minus{/if}" title="Чистая прибыль">
							{if $order->profit_price > 0}+{/if}
							{$order->profit_price|convert}
							<span class="price_sign">{$currency->sign}</span>
						</span>
					</div>
				{/if}

				{if $order->total_price > 0}
					<div class="over_line">
						<span class="percent" title="Маржа = % прибыли в выручке">Маржа:
							{($order->profit_price / $order->total_price * 100)|price_format:2}%</span>
						<span class="percent" title="ROI = % возврата инвестиций">ROI:
							{($order->profit_price / ($order->total_price - $order->profit_price)*100)|price_format:2}%</span>
					</div>
				{/if}
			{/if}

			{if !$order->payment_price|empty}
				<div class="main_line">
					К оплате:
					<b>
						{$order->payment_price|convert:$payment_currency->id}

						{if !$payment_currency->sign|empty}
							<span class="price_sign">{$payment_currency->sign}</span>
						{else}
							<span class="price_sign">{$currency->sign}</span>
						{/if}
					</b>
				</div>
			{/if}
		</div>


		{if $can_edit}
			<div class="btn_row_add">
				<div class="checkbox_item">
					<input type="checkbox" value="1" id="notify_user" name="notify_user" />
					<label for="notify_user">Уведомить покупателя о состоянии заказа по email</label>
				</div>

				<input class="button_green" type="submit" name="" value="Сохранить" />
			</div>
		{/if}

	</div>


	<!-- Финансы -->
	{if  $user|user_access:orders_finance AND $order->id}
		<div class='layer mt_20'>
			<h2>Финансы
				{if payments}
					<span class="sum_total"> всего: {$total->payments|convert}
						{$currency->sign}</span>
				{/if}
			</h2>

			<div id="payments" class="list">
				{foreach $payments as $p}
					<div class="row {if !$p->verified}verified_off{else}verified_on{/if}" item_id="{$p->id}">
						<div class="payment_amount {if $p->type == 0}minus{/if} {if $p->related_payment_id}transfer{/if}">
							<a href="{url view=FinancePaymentAdmin id=$p->id clear=true}">{if $p->type == 0}-{else}+{/if}{$p->amount|convert}
								{$p->currency_sign}</a>
							{if $p->currency_rate!=1 AND !$p->related_payment_id}
								<div class="notice">{$p->currency_amount|convert} {$currency->sign}</div>
							{/if}
						</div>

						<div class="order_date">
							<div class="date">{$p->date|date}</div>
							<div class="time">{$p->date|time}</div>
						</div>

						<div class="user_name">
							{if $p->category_name}
								{$p->category_name}
							{else}
								Премещение между кошельками
							{/if} <div class="notice">{$p->comment}</div>
						</div>

						<div class="user_email">
							{$p->purse_name}
							<div class="notice">
								<a
									href="{url view=$p->contractor->view_name id=$p->contractor->entity_id clear=true}">{$p->contractor->entity->name}</a>
							</div>
						</div>

						<div class="icons">
							<a class="verified" title="Cверка с бухгалтерией"></a>
						</div>
					</div>
				{/foreach}
			</div>

			<div class="btn_row">
				<a class="button"
					href="{url view=FinancePaymentAdmin cur_type=1 contractor_entity_name=order contractor_entity_id=$order->id clear=true}"
					target="_blank">Добавить платеж</a>
			</div>
		</div>
	{/if}
</form>


<script src="/{$config->templates_subdir}js/autocomplete/jquery.autocomplete-min.js"></script>
<script>
	const order_status = '{$order->status}';
	const currency = '{$currency->sign}';
	const max_order_amount = '{$settings->max_order_amount}';
	const units = '{$settings->units}';
	const session_id = '{$smarty.session.id}';

	{literal}

		$(function() {

			// Сортировка вариантов
			$("#purchases").sortable({
				items: '.row',
				handle: ".move_zone",
				tolerance: "pointer",
				opacity: 0.90,
				axis: 'y',
				update: function(event, ui) {
					colorize();
				}
			});

			// Удаление товара
			$(".purchases").on('click', 'a.delete', function() {
				$(this).closest(".row").fadeOut(200, function() {
					$(this).remove();
				});
				return false;
			});


			// Добавление товара. Клонируем срочку товара.
			const new_purchase = $('.purchases #new_purchase').clone(true);
			$('.purchases #new_purchase').remove().removeAttr('id');

			$("input#add_purchase").autocomplete({
				serviceUrl: '/app/agmin/ajax/search_products.php',
				minChars: 0,
				noCache: false,
				onSelect: function(suggestion) {
					const new_item = new_purchase.clone().appendTo('.purchases');
					new_item.removeAttr('id');
					new_item.find('a.add_name').html(suggestion.data.name);
					new_item.find('a.add_name').attr('href', '?view=ProductPriceAdmin&id=' +
						suggestion.data.id);

					// Добавляем варианты нового товара
					const variants_select = new_item.find('select[name*=purchases][name*=variant_id]');
					for (let i in suggestion.data.variants) {
						let variant = suggestion.data.variants[i];
						let sku = variant.sku == '' ? '' : ' (арт. ' + variant.sku + ')';
						variants_select.append("<option value='" + variant.id +
							"' price='" + variant.price + "' cost_price='" +
							variant.cost_price + "' amount='" + variant.stock + "' weight='" + variant
							.weight + "'>" + variant
							.name + sku +
							"</option>");
					}

					if (suggestion.data.variants.length > 1 || suggestion.data.variants[0].name != '')
						variants_select.show();

					change_variant(variants_select);

					if (suggestion.data.image)
						new_item.find('img.product_icon').attr("src", suggestion.data.image);
					else
						new_item.find('img.product_icon').remove();

					$("input#add_purchase").val('').focus().blur();
					new_item.show();
					colorize();
				},
				formatResult: function(suggestion, currentValue) {
					let reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g');
					let pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
					let stock_txt = ' - <span class="color_grey">нет в наличии</span>';
					let movement = '';
					let movement_count = 0;
					let stock_count = 0;
					for (let i in suggestion.data.variants) {
						stock_count += suggestion.data.variants[i].stock;

						if (suggestion.data.variants[i].movements_amount) {
							movement_count += suggestion.data.variants[i].movements_amount;
						}
					}

					if (stock_count > 0)
						stock_txt = ' - <span class="color_green">остаток ' + suggestion.data.variants[0]
						.stock + ' ' + units + '</span>';

					if (movement_count > 0)
						movement = ' <span class="color_grey">(+' + movement_count + ')</span>'

					return (suggestion.data.image ? "<img align='absmiddle' src='" + suggestion.data
							.image +
							"'> " : '') + suggestion.value.replace(new RegExp(pattern, 'gi'),
							'<strong>$1<\/strong>') + ' - ' + '<span class="color_red"><b>' + suggestion
						.data.variants[0].price + ' ' + currency + '</b><span>' + stock_txt + movement;
				}
			});


			// Изменение цены и кол-ва при смене варианта
			$('.purchases').on('change', 'select[name*=purchases][name*=variant_id]', function() {
				change_variant($(this));
			});


			// Изменение цены и макс количества при изменении варианта
			function change_variant(element) {
				let cost_price = element.find('option:selected').attr('cost_price');
				let price = element.find('option:selected').attr('price');
				let weight = element.find('option:selected').attr('weight');

				// Выбираем доступное кол-во товара (по складу)
				let amount = element.find('option:selected').attr('amount');

				element.closest('.row').find('.cost_price .js_change').text(cost_price);
				element.closest('.row').find('input[name*=purchases\\[price\\]]').val(price);
				element.closest('.row').find('.variant_stock .js_change').text(amount);
				element.closest('.row').find('.amount_waight .js_change').text(weight);

				let amount_select_el = element.closest('.row').find('select[name*=purchases][name*=amount]');
				let selected_amount = (amount_select_el.val() || 1);
				amount_select_el.html('');

				if (amount < 0)
					amount = 0;

				for (let i = 1; i <= amount; i++)
					amount_select_el.append("<option value='" + i + "'>" + i + " " + units + "</option>");

				// Дополнительное кол-во для новых заказов (0)
				if (order_status == 0) {
					for (let ai = (Number(amount) + 1); ai <= max_order_amount; ai++)
						amount_select_el.append("<option class='disabled' value='" + ai + "'>" + ai + " " + units +
							"</option>");
				}

				amount_select_el.val(selected_amount);
				return false;
			}


			// Редактировать покупки
			$(".edit_purchases").click(function() {
				$(".purchases div.view_purchase").hide();
				$(".purchases div.edit_purchase").show();
				$("div.edit_purchases").hide();
				$("div#add_purchase").show();
				return false;
			});


			// Редактировать получателя
			$("#order_details a.edit_order_details").click(function() {
				if ($("div.view_comment").height() > 10)
					$("div.edit_comment textarea").height($("div.view_comment").height() + 5);

				$("#user .view_order_detail").hide();
				$("#user .edit_order_detail").show();
				return false;
			});


			// Редактировать примечание (universal)
			$(".note_wrap a.edit_note").click(function() {
				let layer = $(this).closest('div.note_wrap');
				let text_height = layer.find("div.view_note").height() + 5;
				layer.find("div.edit_note textarea").height(text_height);
				layer.find("div.view_note").hide();
				layer.find("div.edit_note").show();
				return false;
			});


			// Редактировать пользователя
			$("#order_details a.edit_user").click(function() {
				$("div.view_user").hide();
				$("div.edit_user").show();
				return false;
			});


			$("input#user_id").autocomplete({
				serviceUrl: '/app/agmin/ajax/search_users.php',
				minChars: 0,
				noCache: false,
				onSelect: function(suggestion) {
					$('input[name="user_id"]').val(suggestion.data.id);
				}
			});


			// Удалить пользователя
			$("#order_details").on('click', 'a.delete_user', function() {
				$('input[name="user_id"]').val(0);
				$('div.view_user').hide();
				$('div.edit_user').hide();
				return false;
			});
		});
	{/literal}
</script>