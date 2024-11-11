{include file="orders/orders_menu_part.tpl"}

{$meta_title='Заказы' scope=global}

{if $message_error}
	<div class="message message_error">
		<span class="text">{if $message_error == 'error_closing'}Нехватка некоторых товаров на
			складе{else}{$message_error|escape}
			{/if}</span>
	</div>
{/if}

<div class="two_columns_list">

	<div id="header" class="header_top">
		<h1>{if $orders_count}{$orders_count}{else}Нет{/if} заказ{$orders_count|plural:'':'ов':'а'}

			{if $user|user_access:orders_finance AND $orders_price->sum_total_price}
				<span class="sum_total">на сумму: {$orders_price->sum_total_price|price_format:2:true} {$currency->sign}
					<span
						class="sum_profit_price {if $orders_price->sum_profit_price < 0}color_red{/if}">{if $orders_price->sum_profit_price > 0}+{/if}{$orders_price->sum_profit_price|price_format:2:true}
						{$currency->sign}</span>
				</span>
			{/if}
		</h1>

		<a class="add" href="{url view=OrderAdmin clear=true}">Добавить заказ</a>

		{if $orders_count > 0 and $user|user_access:export}
			<form class="export_btn" method="post" action="{url view=ExportEntityAdmin entity=orders}" target="_blank">
				<input type="hidden" name="session_id" value="{$smarty.session.id}">
				<input type="image" src="/{$config->templates_subdir}images/export_excel.png" name="export"
					title="Экспортировать выбранные заказы">
			</form>
		{/if}

		<div id="search">
			<form method="get">
				<input type="hidden" name="view" value="OrdersAdmin">
				<input class="search" type="text" name="keyword" value="{$keyword|escape}"
					placeholder="№, телефон, город, имя" />
				<input class="search_button" type="submit" value="" />
			</form>
		</div>
	</div>

	<div id="right_menu">
		<div class="popup_menu_btn">
			<span class="popup_btn_sign">
				<li></li>
				<li></li>
				<li></li>
			</span>
			<span class="popup_btn_text">Фильтр</span>
		</div>
		<div id="popup_menu_block">

			<!-- Метки -->
			{if !$labels|empty}
				<ul class="menu_list">
					<li class="{if !$label and !$paid}selected{/if}">
						<a href="{url label=null paid=null}">Все заказы</a>
					</li>

					{foreach $labels as $lab}
						{if ($lab->enabled OR $user|user_access:orders_edit) AND $lab->in_filter}
							<li
								class="label {if $label->id|isset AND $label->id == $lab->id}selected{/if} {if !$lab->enabled}disabled{/if}">
								<a style="background-color:#{$lab->color};" href="{url label=$lab->id paid=null}">{$lab->name}</a>
							</li>
						{/if}
					{/foreach}

				</ul>
			{/if}

			<!-- Фильтры -->
			<ul class="menu_list layer">
				<li class="{if $paid === 1}selected{/if}">
					<a href="{url paid=1}">Оплачены</a>
				</li>
				<li class="{if $paid === 0}selected{/if}">
					<a href="{url paid=0}">Не оплачены</a>
				</li>
			</ul>
		</div>
	</div>


	<!-- Список заказов -->
	<div id="main_list">
		{if $orders}

			{if !$pagination_hide}
				{include file='parts/pagination.tpl'}
			{elseif ($page_limit<$orders_count)}
				<div id="pagination">Показано только первые {$page_limit} заказа</div>
			{/if}

			<form id="form_list" method="post">
				<input type="hidden" name="session_id" value="{$smarty.session.id}">

				<div id="orders" class="list">
					{foreach $orders as $order}
						<div class="row {if $order->paid}highlight{/if}" item_id="{$order->id}">

							{if $user|user_access:orders_edit and in_array($status, [2, 3])}
								<div class="checkbox">
									<input type="checkbox" name="check[]" value="{$order->id}" />
								</div>
							{/if}

							<div class="order_date">
								<a class="order_id"
									href="{url view=OrderAdmin id=$order->id clear=true}">№<span>{$order->id}</span></a>
								<div class="date">{$order->date|date}</div>
								<div class="time">{$order->date|time}</div>
							</div>

							<div class="order_name">

								<a href="{url view=OrderAdmin id=$order->id clear=true}"><span>{$order->name|escape}</span></a>

								{if !$order->purchases|empty}
									<div class="purchases">
										{foreach $order->purchases as $purchase}
											<div class="image">
												<div class="amount">{$purchase->amount}</div>
												<img title="{$purchase->product_name} {if $purchase->variant_name} - {$purchase->variant_name}
											{/if}" src="{if $purchase->image_filename}{$purchase->image_filename|resize:50:50}{else}{$config->templates_subdir}images/cargo.png{/if}" />
											</div>
										{/foreach}
									</div>
								{/if}
							</div>

							<div class="order_info">
								<div class="order_price">
									{$order->payment_price|convert} {$currency->sign}
									{if $user|user_access:orders_finance}
										<span
											class="profit_price {if $order->profit_price < 0}color_red{/if}">{if $order->profit_price > 0}+{/if}{$order->profit_price|convert}
											{$currency->sign}</span>
									{/if}
								</div>

								{if $order->payment_method_name}
									<div class="round_box payment_method_name">{$order->payment_method_name}</div>
								{/if}

								{if $order->delivery_method_name}
									<div class="round_box delivery_method_name">{$order->delivery_method_name}</div>
								{/if}

								<div class="order_phone">
									{$order->phone|escape}
								</div>

								<div class="order_address">
									{$order->address|escape} {$order->comment|escape|nl2br}
								</div>

								{if $order->note}
									<div class="notice_block">
										<div class="notice_block_text">{$order->note|escape|nl2br}</div>
										<div class="show_link_block">
											<a class="show_link" href="#">раскрыть ↓</a>
										</div>
									</div>
								{/if}
							</div>

							<div class="order_status">

								{if !$order->labels|empty}
									{foreach $order->labels as $lab}
										<span class="order_label_text" style="background-color:#{$lab->color};">{$lab->name}</span>
									{/foreach}
								{/if}

								{if $keyword}
									{if $order->status == 0}
										<img src='/{$config->templates_subdir}images/new.png' alt='Новый' title='Новый'>
									{elseif $order->status == 1}
										<img src='/{$config->templates_subdir}images/time.png' alt='Принят' title='Принят'>
									{elseif $order->status == 4}
										<img src='/{$config->templates_subdir}images/time.png' alt='Принят' title='Отгружен'>
									{elseif $order->status == 2}
										<img src='/{$config->templates_subdir}images/tick.png' alt='Выполнен' title='Выполнен'>
									{elseif $order->status == 3}
										<img src='/{$config->templates_subdir}images/cross.png' alt='Отменен' title='Отменен'>
									{/if}
								{/if}
							</div>

							{if $user|user_access:orders_delete AND ($status == 3 OR !$keyword|empty)}
								<div class="icons">
									<a href='#' class="delete" title="Удалить"></a>
								</div>
							{/if}
						</div>
					{/foreach}
				</div>


				{if $user|user_access:orders_edit and in_array($status, [2, 3])}
					<div id="action">
						<span id="check_all" class="dash_link">Выбрать все</span>

						<span id="select">
							<select name="action">
								<option value="">Выбрать действие</option>
								{foreach $labels as $l}
									{if $l->enabled}
										<option value="set_label_{$l->id}">Отметить &laquo;{$l->name}&raquo;</option>
									{/if}
								{/foreach}

								{foreach $labels as $l}
									{if $l->enabled}
										<option value="unset_label_{$l->id}">Снять &laquo;{$l->name}&raquo;</option>
									{/if}
								{/foreach}

								{if $status !== 0}<option value="set_status_0">В новые</option>{/if}
								{if $status !== 1}<option value="set_status_1">В принятые</option>{/if}
								{if $status !== 4}<option value="set_status_4">В отгруженые</option>{/if}
								{if $status !== 2}<option value="set_status_2">В выполненные</option>{/if}

								{if $user|user_access:orders_delete}
									<option value="delete">
										{if $status !== 3 and $keyword}
											Отменить выбранные заказы
										{else}
											Удалить выбранные заказы
										{/if}
									</option>
								{/if}
							</select>
						</span>

						<input id="apply_action" class="button_green" type="submit" value="Применить">

					</div>
				{/if}

			</form>

			{if !$pagination_hide}
				{include file='parts/pagination.tpl'}
			{/if}

		{/if}
	</div>

</div>



<script>
	{literal}

		// On document load 
		$(function() {

			// Минимизировать комментарии менеджера
			$(".notice_block").each(function(index) {
				let height = $(this).height();
				let minimize_height = 60;
				if (height > minimize_height & (height - minimize_height) > 40) {
					$(this).addClass("minimizeble minimize");
				}
			});

			$(".show_link_block a").click(function() {
				if ($(this).closest("div.notice_block").hasClass("minimize")) {
					$(this).closest("div.notice_block").removeClass("minimize");
					$(this).text("скрыть ↑");
				} else {
					$(this).closest("div.notice_block").addClass("minimize");
					$(this).text("раскрыть ↓");
				}
				return false;
			});

		});

	{/literal}
</script>