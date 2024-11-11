{include file='warehouse/warehouse_menu_part.tpl'}

{if $status == 3}
	{$meta_title='Списание товаров' scope=global}
{else}
	{$meta_title='Поставки товаров' scope=global}
{/if}

<div class="two_columns_list">

	<div id="header">
		<div class=header_top>
			<h1>
				{if $status == 3}
					{if $movements_count}{$movements_count}{else}Нет{/if} списан{$movements_count|plural:'ие':'ий':'ия'}
				{elseif $status == 4}
					{if $movements_count}{$movements_count}{else}Нет{/if} отмененн{$movements_count|plural:'ое':'ых':'ых'}
				{elseif $status == 2}
					{if $movements_count}Поступило {$movements_count}{else}Нет{/if}
					постав{$movements_count|plural:'ка':'ок':'ки'}
				{else}
					{if $movements_count}
						{if $status == 1}Ожидаем {elseif $status === 0}Новыe {else}Всего {/if}
						{$movements_count}
					{else}Нет
					{/if}
					постав{$movements_count|plural:'ка':'ок':'ки'}

					{if $user|user_access:finance AND $total AND $status !== null }
						<span class="sum_total">на сумму: {$total->sum_wholesale_price|price_format:2:true} {$currency->sign}
							<span class="sum_profit_price">
								+{($total->sum_price - $total->sum_wholesale_price)|price_format:2:true} {$currency->sign}
							</span>
						</span>

						<span class="sum_total"> ({$total->sum_stock|price_format:0} единиц товара)</span>
					{/if}
				{/if}
			</h1>
			{if $user|user_access:warehouse_edit OR $user|user_access:warehouse_add}
				<a class="add" href="{url view=WarehouseMovementAdmin clear=true}">Добавить перемещение товара</a>
			{/if}
		</div>


		{if $message_error}
			<div class="message message_error">
				<span class="text">{if $message_error=='error_closing'}Нехватка некоторых товаров на
					складе{else}{$message_error|escape}
					{/if}</span>
			</div>
		{/if}
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
			<ul class="menu_list">
				<li {if $status === null}class="selected" {/if}><a href='{url status=null}'>Все поставки</a></li>
				<li {if $status === 0}class="selected" {/if}><a href='{url status='0'}'>Новые</a></li>
				<li {if $status == 1}class="selected" {/if}><a href='{url status=1}'>Ожидаем</a></li>
				<li {if $status == 2}class="selected" {/if}><a href='{url status=2}'>Поступило</a></li>
			</ul>

			<ul class='menu_list layer'>
				<li {if $status == 3}class="selected" {/if}><a href='{url status=3}'>Списано</a></li>
				<li {if $status == 4}class="selected" {/if}><a href='{url status=4}'>Отмена</a></li>
			</ul>
		</div>
	</div>


	<div id="main_list">
		{if $movements}

			{include file='parts/pagination.tpl'}

			<form id="form_list" method="post">
				<input type="hidden" name="session_id" value="{$smarty.session.id}">

				<div id="movements" class="list">

					{foreach $movements as $movement}
						<div class="row {if $movement->status==2}highlight{/if}" item_id="{$movement->id}">

							{if $user|user_access:warehouse_edit && $status === 4}
								<div class="checkbox">
									<input type="checkbox" name="check[]" value="{$movement->id}" />
								</div>
							{/if}

							<div class="order_date">
								<a class="order_id"
									href="{url view=WarehouseMovementAdmin id=$movement->id status=null}">№<span>{$movement->id}</span></a>
								<div class="date">{$movement->date|date}</div>
							</div>

							<div class="movement_name">
								<div class="purchases">
									{foreach $movement->purchases as $purchase}
										<div class="image">
											<div class="amount">{$purchase->amount}</div>
											<img title="{$purchase->product_name}{if $purchase->variant_name}- {$purchase->variant_name}{/if}"
												src='{if $purchase->image_filename}{$purchase->image_filename|resize:50:50}{else}{$config->templates_subdir}images/cargo.png{/if}' />
										</div>
									{/foreach}
								</div>
							</div>

							<div class="movement_info">
								{if $movement->status != 0}
									<div class="order_price">{{$movement->awaiting_date|date}}</div>
								{/if}
								<div class="order_address">
									{$movement->note|escape|nl2br}
								</div>

								{if $user|user_access:warehouse_edit AND $movement->note_logist}
									<div class="notice_block">
										<div class="notice_block_text">{$movement->note_logist|escape|nl2br}</div>
										<div class="show_link_block">
											<a class="show_link" href="#">раскрыть ↓</a>
										</div>
									</div>
								{/if}
							</div>

							<div class="movement_status">
								{if $movement->status == 0}
									<img src="/{$config->templates_subdir}images/new.png" alt='Новый' title='Новый'>
								{/if}
								{if $movement->status == 1}
									<img src="/{$config->templates_subdir}images/time.png" alt='Ожидаем' title='Ожидаем'>
								{/if}
								{if $movement->status == 2}
									<img src="/{$config->templates_subdir}images/tick.png" alt='Принят' title='Принят'>
								{/if}
								{if $movement->status == 3  || $movement->status == 4}
									<img src="/{$config->templates_subdir}images/cross.png" alt='Списан' title='Списан'>
								{/if}

								{if $movement->images}
									<img src="/{$config->templates_subdir}images/clipboard.png" alt="Фотоотчет" title="Фотоотчет">
								{/if}
							</div>

						</div>
					{/foreach}
				</div>

				{if $user|user_access:warehouse_edit && $status === 4}
					<div id="action">
						<span id='check_all' class="dash_link">Выбрать все</span>
						<span id="select">
							<select name="action">
								<option value="">Выбрать действие</option>
								<option value="delete">Удалить выбранные поставки</option>
							</select>
						</span>
						<input id="apply_action" class="button_green" type="submit" value="Применить">
					</div>
				{/if}

			</form>

			{include file='parts/pagination.tpl'}

		{/if}
	</div>
</div>


<script>
	{literal}
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