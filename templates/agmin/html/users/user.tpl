{include file='users/users_menu_part.tpl'}
{include file='users/user_submenu_part.tpl'}

{if $current_user->id}
	{$meta_title = $current_user->name|escape scope=global}
{/if}

{if $message_success}
	<div class="message message_success">
		<span class="text">{if $message_success=='updated'}Пользователь
			отредактирован{else}{$message_success|escape}
			{/if}</span>
	</div>
{/if}


{if $message_error}
	<!-- Системное сообщение -->
	<div class="message message_error">
		<span class="text">
			{if $message_error=='email_exists'}Пользователь с таким email уже зарегистрирован
			{elseif $message_error=='phone_exists'}Пользователь с таким телефоном уже зарегистрирован
			{elseif $message_error=='te_name_exists'}Пользователь с таким именем Телеграм уже зарегистрирован
			{elseif $message_error=='empty_name'}Введите имя пользователя
			{elseif $message_error=='empty_email'}Введите email пользователя
			{else}{$message_error|escape}
			{/if}</span>
	</div>
{/if}


<!-- Основная форма -->
<form method="post" class=form_css>
	<input type="hidden" name="session_id" value="{$smarty.session.id}">

	<div class="columns">

		<div class="block_flex w100">
			<div class="over_name">
				<div class="checkbox_line">
					<div class="checkbox_item">
						<input name=enabled value='1' type="checkbox" id="active_checkbox"
							{if !$user|user_access:users_edit}disabled{/if} {if $current_user->enabled}checked{/if} />
						<label for="active_checkbox">Активен</label>
					</div>
					<div class="checkbox_item">
						<input name="manager" value='1' type="checkbox" id="manager_checkbox"
							{if !$user|user_access:users_manager}disabled{/if}
							{if $current_user->manager}checked{/if} />
						<label for="manager_checkbox">Сотрудник</label>
					</div>
				</div>
			</div>
			<div class=name_row>
				<input name="id" type="hidden" value="{$current_user->id|escape}" />
				<input class="name" name="name" type="text" value="{$current_user->name|escape}" autocomplete="off" />
			</div>
		</div>

		<div class="block_flex layer">
			<h2>Данные пользователя</h2>
			<ul class="property_block">
				{if $groups}
					<li>
						<label class="property_name" for="group_id">Группа</label>
						<select id="group_id" name="group_id" {if !$user|user_access:users_groups}disabled{/if}>
							<option value="">Не входит в группу</option>
							{foreach $groups as $g}
								<option value='{$g->id}' {if $current_user->group_id == $g->id}selected{/if}>{$g->name|escape}
								</option>
							{/foreach}
						</select>
					</li>
				{/if}

				<li>
					<label for="email" class="property_name">Email</label>
					<input id="email" name="email" type="text" value="{$current_user->email|escape}" autocomplete="off"
						{if !$user|user_access:users_settings and !$current_user->email|empty}disabled{/if} />
				</li>
				<li>
					<label for="phone" class="property_name">Телефон</label>
					<input id="phone" name="phone" type="text" value="{$current_user->phone|escape}"
						autocomplete="off" />
				</li>
				<li>
					<label for="token" class="property_name">Токен</label>
					<input id="token" name="token" type="text" value="{$current_user->token|escape}" autocomplete="off"
						disabled />
				</li>
				<li>
					<label for="created" class="property_name">Дата регистрации</label>
					<input id="created" type="text" disabled value="{$current_user->created|date}" />
				</li>
				<li>
					<label for="ip" class="property_name">Последний IP</label>
					<input id="ip" type="text" disabled value="{$current_user->last_ip|escape}" />
				</li>
				<li>
					<label for="comment" class="property_name">Заметки</label>
					<textarea id="comment" name="comment">{$current_user->comment|escape}</textarea>
				</li>
			</ul>

			<div class="btn_row">
				<input class="button_green" type="submit" value="Сохранить" />
			</div>

		</div>
	</div>
</form>


<!-- Статистика продаж менеджера -->
{if $user|user_access:users_manager OR $current_user->id == $user->id}
	<div class="product_stats">
		<div id='product_stats'></div>
	</div>
{/if}



{if $orders}
	<div class="block_flex">
		<form method="post">
			<input type="hidden" name="session_id" value="{$smarty.session.id}">

			<div class="header_top mt_40">
				<h1>
					{if $orders_count}{$orders_count}{else}Нет{/if} заказ{$orders_count|plural:'':'ов':'а'}
					{if $user|user_access:orders_labels AND $orders_price->sum_total_price}
						<span class="sum_total">на сумму: {$orders_price->sum_total_price|convert} {$currency->sign}
							<span class="sum_profit_price">+{$orders_price->sum_profit_price|convert} {$currency->sign}</span>
						</span>
					{/if}
				</h1>
			</div>

			<div class="list">
				{foreach $orders as $order}
					<div class="{if $order->paid}highlight{/if} row" id="$order->id">

						<div class="order_date">
							<a class="order_id" href="{url view=OrderAdmin id=$order->id}">№<span>{$order->id}</span></a>
							<div class="date">{$order->date|date}</div>
							<div class="time">{$order->date|time}</div>
						</div>

						<div class="order_name">

							<a href="{url view=OrderAdmin id=$order->id}">
								<span>{$order->name|escape}</span>
							</a>

							{if $order->note}
								<div class="notice">{$order->note|escape}</div>
							{/if}

							<div class="purchases">
								{foreach $order->purchases as $purchase}
									<div class="image">
										<div class="amount">{$purchase->amount}</div>
										<img title="{$purchase->product_name} {if $purchase->variant_name} - {$purchase->variant_name}
										{/if}" src='{if $purchase->image_filename}{$purchase->image_filename|resize:50:50}{else}{$config->templates_subdir}images/cargo.png{/if}' />
									</div>
								{/foreach}
							</div>
						</div>

						<div class="order_info">
							<div class="order_price">
								{$order->payment_price|convert} {$currency->sign}
							</div>
							<div class="order_phone">
								{$order->phone|escape}
							</div>
							<div class="order_address">
								{$order->address|escape} {$order->comment|escape|nl2br}
							</div>
						</div>

						<div class="order_status">
							{foreach $order->labels as $l}
								<span class="order_label_text" style="background-color:#{$l->color};"
									title="{$l->name}">{$l->name}</span>
							{/foreach}
						</div>

						<div class="icons">
							{if $order->paid}
								<img src='/{$config->templates_subdir}images/cash_stack.png' alt='Оплачен' title='Оплачен'>
							{else}
								<img src='/{$config->templates_subdir}images/cash_stack_gray.png' alt='Не оплачен'
									title='Не оплачен'>
							{/if}

							{if $order->status == 3}
								<img src='/{$config->templates_subdir}images/cross.png' alt='Отменен' title='Отменен'>
							{/if}
						</div>

					</div>
				{/foreach}
			</div>

		</form>
	</div>
{/if}

{include file='parts/charts_init.tpl'}

<script>
	const php_manager_id = '{$current_user->id}';
	const php_currency_name = '{$currency->name}';
	const php_currency_sign = '{$currency->sign}';

	{literal}
		$(function() {

			// Выводим график
			let my_options = {
				title: {
					text: 'Статистика продаж менеджера'
				},
				subtitle: {
					text: 'Доход по месяцам'
				},
				yAxis: {
					title: {
						text: ''
					}
				}
			}

			show_stat_graphic(
				'product_stats',
				{manager_id: php_manager_id, filter: 'byMonth'},
				['totalPrice', 'amount', 'totalPayments'],
				my_options,
				php_currency_sign
			);

		});
	{/literal}
</script>