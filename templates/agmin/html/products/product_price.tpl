{include file='products/products_menu_part.tpl'}
{include file='products/product_submenu_part.tpl'}

{if $product->id}
	{$meta_title = $product->name scope=global}
{/if}

{if $message_success}
	<div class="message message_success">
		<span class="text">{if $message_success=='updated'}Товар
			изменен{else}{$message_success|escape}
			{/if}</span>
	</div>
{/if}

<!-- Основная форма -->
<form method="post" class="form_css" enctype="multipart/form-data">
	<input type="hidden" name="session_id" value="{$smarty.session.id}">

	<div class="columns">

		<div class="block_flex w100">
			<div class="over_name">
				<div class="checkbox_line">
					<div class="checkbox_item">
						<input name="visible" value="1" type="checkbox" id="active_checkbox"
							{if $product->visible}checked{/if} />
						<label for="active_checkbox">Показывать в каталоге</label>
					</div>

					<div class="checkbox_item">
						<input name="disable" value="1" type="checkbox" id="disable_checkbox"
							{if $product->disable}checked{/if} />
						<label for="disable_checkbox">Не поставляется</label>
					</div>

					<div class="checkbox_item">
						<input name="featured" value="1" type="checkbox" id="featured_checkbox"
							{if $product->featured}checked{/if} />
						<label for="featured_checkbox"
							title="Товар выводиться на главное странице">Рекомендуемый</label>
					</div>

					<div class="checkbox_item">
						<input name="sale" value="1" type="checkbox" id="sale_checkbox"
							{if $product->sale}checked{/if} />
						<label for="sale_checkbox">Распродажа</label>
					</div>
				</div>
				<div class="link_line">
					{if $product->category_id}
						<a class='out_link'
							href="{url view=ProductsAdmin category_id=$product->category_id clear=true}">Перейти к
							товарам
							категории в админке</a>
					{/if}
					{if $product->id}
						<a class="out_link" target="_self" href="{$settings->site_url}/product/{$product->id}">Открыть товар
							на
							сайте</a>
					{/if}
				</div>
			</div>
			<div class="name_row">
				<label for="name" class="item_id">#{$product->id}</label>
				<input id="name" class="name" name="name" type="text" value="{$product->name|escape}" autocomplete="off"
					disabled />
				<input name=id type="hidden" value="{$product->id|escape}" />
			</div>
		</div>


		<!-- Варианты товара -->
		{assign var=first_variant value=$product_variants|@first}
		<div id="variants_block" class="{if $product_variants|@count == 1 && !$first_variant->name}single_variant{/if}">

			<div id="variants">
				{foreach $product_variants as $variant}
					<ul>
						<li class="variant_name_info">
							<div class="variant_name">
								<div class="move_zone"></div>
								<input name="variants[id][]" type="hidden" value="{$variant->id|escape}" />
								<input name="variants[name][]" type="text" value="{$variant->name|escape}" />
								<a class="del_variant">
									<img src="/{$config->templates_subdir}images/cross-circle-frame.png" alt="удалить" />
								</a>
							</div>
						</li>

						<li class="variant_sku_info">
							<div class="variant_sku">
								<div class="input_disabled"></div>
								<input name="variants[sku][]" type="text" value="{$variant->sku|escape}" disabled />
							</div>
							<div class="marking_print">
								<a class="print_icon"
									href="{url view=ProductMarkingAdmin variant_id=$variant->id type=print return=null}"
									target="_blank">
									<img src="/{$config->templates_subdir}images/printer.png" title="Печать маркировки">
								</a>
							</div>
						</li>

						<li class="variant_price_info">
							<div class="variant_old">
								<label for="old_price_{$variant->id}">Старая цена</label>
								<input id="old_price_{$variant->id}" name="variants[old_price][]" type="text"
									value="{$variant->old_price|escape}" />
							</div>

							{if $variant->old_price > 0 AND ($variant->old_price - $variant->price) > 0}
								<div class="discount">
									<span title="Скидка">-{$variant->old_price - $variant->price} {$currency->sign}
										{(($variant->old_price - $variant->price) / $variant->price * 100)|ceil}%</span>
								</div>
							{/if}

							<div class="variant_price">
								<label for="price_{$variant->id}">Розница</label>
								<input id="price_{$variant->id}" name="variants[price][]" type="text"
									value="{$variant->price|escape}" />
							</div>

							{if $variant->cost_price > 0}
								<div class="profit">
									<span title="Наценка">+{$variant->price-$variant->cost_price} {$currency->sign}
										{(($variant->price - $variant->cost_price) / $variant->cost_price * 100)|ceil}%</span>
								</div>
							{/if}

							<div class="variant_discount">
								<label for="cost_price_{$variant->id}">Оптовая цена</label>
								<input id="cost_price_{$variant->id}" name="variants[cost_price][]" type="text"
									value="{$variant->cost_price|escape}" />
							</div>
						</li>

						<li class="variant_amount_info">
							<div class="variant_amount">
								<div class="input_disabled"></div>
								<input name="variants[stock][]" type="text"
									value="{if $variant->infinity}∞{elseif !$variant->stock}0{else}{$variant->stock|escape}{/if}"
									disabled />
								<span>{$settings->units}</span>
							</div>
							<div class="variant_weight">
								<div class="input_disabled"></div>
								<input name="variants[weight][]" type="text" value="{$variant->weight}" disabled />
								<span>{$settings->weight_units}/{$settings->units}</span>
							</div>
						</li>

						<li class="variant_provider_info">

							<div class="variant_provider">
								<label for="provider_{$variant->id}">Поставщик</label>
								<select id="provider_{$variant->id}" name="variants[provider_id][]">
									<option value="">-</option>
									{foreach $providers as $provider}
										<option value="{$provider->id}" {if $provider->id==$variant->provider_id}selected{/if}>
											{$provider->name}</option>
									{/foreach}
								</select>
							</div>

							<div class="variant_merchant">
								<label for="merchant_{$variant->id}">Выводить в прайс</label>
								<select id="merchant_{$variant->id}" name="variants[merchant_id][]">
									<option value="">-</option>
									{foreach $merchants as $merchant}
										<option value="{$merchant->id}" {if $merchant->id==$variant->merchant_id}selected{/if}>
											{$merchant->name}</option>
									{/foreach}
								</select>
							</div>

							<div class="variant_awaiting_date">
								<label for="awaiting_date_{$variant->id}">Дата поставки</label>
								<input id="awaiting_date_{$variant->id}" type=text name="variants[awaiting_date][]"
									value='{$variant->awaiting_date|date}' />
							</div>

							<div class="variant_custom">
								<label for="awaiting_{$variant->id}">Выводить ожидаем</label>
								<input id="awaiting_{$variant->id}" type=checkbox name="variants[awaiting][]"
									value="{$variant->id|escape}" {if $variant->awaiting}checked{/if}
									title="Выводить ожидаем" />
							</div>

							<div class="variant_custom">
								<label for="custom_{$variant->id}">Выводить под заказ</label>
								<input id="custom_{$variant->id}" type=checkbox name="variants[custom][]"
									value="{$variant->id|escape}" {if $variant->custom}checked{/if}
									title="Выводить под заказ" />
							</div>

						</li>
					</ul>
				{/foreach}
			</div>


			<ul id="new_variant" style="display:none;">
				<li class="variant_name_info">
					<div class="variant_name">
						<div class="move_zone"></div>
						<input name="variants[id][]" type="hidden" value="" />
						<input name="variants[name][]" type="text" value="" />
						<a class="del_variant"><img src="/{$config->templates_subdir}images/cross-circle-frame.png"
								alt="удалить" /></a>
					</div>
				</li>

				<li class="variant_sku_info">
					<div class="variant_sku">
						<div class="input_disabled"></div>
						<input name="variants[sku][]" type="text" value="" disabled />
					</div>
				</li>

				<li class="variant_price_info">
					<div class="variant_old">
						<label>Старая цена</label>
						<input name="variants[old_price][]" type="text" value="" />
					</div>

					<div class="variant_price">
						<label>Розница</label>
						<input name="variants[price][]" type="text" value="" />
					</div>

					<div class="variant_discount">
						<label>Оптовая цена</label>
						<input name="variants[cost_price][]" type="text" value="" />
					</div>
				</li>

				<li class="variant_amount_info">
					<div class="variant_amount">
						<div class="input_disabled"></div>
						<input name="variants[stock][]" type="text" value="0" disabled />
						<span>{$settings->units}</span>
					</div>
					<div class="variant_weight">
						<div class="input_disabled"></div>
						<input name="variants[weight][]" type="text" value="" disabled />
						<span>{$settings->weight_units}</span>
					</div>
				</li>

				<li class="variant_provider_info">
					<div class="variant_provider">
						<label>Поставщик</label>
						<select name="variants[provider_id][]">
							<option value="">-</option>
							{foreach $providers as $provider}
								<option value="{$provider->id}">{$provider->name}</option>
							{/foreach}
						</select>
					</div>

					<div class="variant_merchant">
						<label>Выводить в прайс</label>
						<select name="variants[merchant_id][]">
							<option value="">-</option>
							{foreach $merchants as $merchant}
								<option value="{$merchant->id}">{$merchant->name}</option>
							{/foreach}
						</select>
					</div>

					<div class="variant_awaiting_date">
						<label>Дата поставки</label>
						<input type=text name="variants[awaiting_date][]" value='{$smarty.now|date_format:'%d.%m.%Y'}'>
					</div>

					<div class="variant_custom">
						<label>Выводить ожидаем</label>
						<input type=checkbox name="variants[awaiting][]" value="" title="Выводить ожидаем">
					</div>

					<div class="variant_custom">
						<label>Выводить под заказ</label>
						<input type=checkbox name="variants[custom][]" value="" title="Выводить под заказ">
					</div>
				</li>
			</ul>

			<div class="btn_row_add">
				<span class="add" id="add_variant">
					<i class="dash_link">Добавить вариант</i>
				</span>
				<input class="button_green" type="submit" name="" value="Сохранить" />
			</div>
		</div>


		<!-- Статистика продажи товара-->
		{if ($user|user_access:stats and $product->id)}
			<div class="block_flex w100 layer product_stats">
				<div id='product_stats'></div>
			</div>
		{/if}


		<!-- Связанные товары --->
		<div class="block_flex layer">
			<h2>
				Связанные товары
				<span class="sum_total">{$related_products|count}
					{$related_products|count|plural:'товар':'товаров':'товара'}</span>
			</h2>
			<div class="list sortable related_products">

				{foreach $related_products as $related_product}
					<div
						class="{if !$related_product->visible}visible_off{/if} {if $related_product->disable}disable{/if} row">
						<div class="move">
							<div class="move_zone"></div>
						</div>

						<div class="image">
							<input type="hidden" name="related_products[]" value="{$related_product->id}">
							<img class="product_icon"
								src="{if $related_product->image_filename}{$related_product->image_filename|resize:50:50}{else}{$config->templates_subdir}images/cargo.png{/if}">
						</div>

						<div class="name">
							<a
								href="{url view=ProductPriceAdmin id=$related_product->id return=$smarty.server.REQUEST_URI}">{$related_product->name}</a>
						</div>
						<div class="icons">
							<a href="#" class="delete"></a>
						</div>
					</div>
				{/foreach}

				<div id="new_related_product" class="row" style='display:none;'>
					<div class="move">
						<div class="move_zone"></div>
					</div>
					<div class="image">
						<input type=hidden name=related_products[] value=''>
						<img class=product_icon src=''>
					</div>
					<div class="name">
						<a class="related_product_name" href=""></a>
					</div>
					<div class="icons">
						<a href='#' class="delete"></a>
					</div>
				</div>
			</div>

			<input type=text id='related_products' class="input_autocomplete"
				placeholder='Выберите товар чтобы добавить его'>

			<div class="btn_row">
				<input class="button_green" type="submit" name="" value="Сохранить" />
			</div>
		</div>
	</div>
</form>


<!-- Заказы с товаром -->
<div class="columns">
	<div class="block_flex layer w100">
		<div class="header_top mt_40">
			<h1>
				{if $orders_count}{$orders_count}{else}Нет{/if} заказ{$orders_count|plural:'':'ов':'а'}
				{if $user|user_access:orders_labels AND $orders_price->sum_total_price}
					<span class="sum_total">на сумму: {$orders_price->sum_total_price|convert} {$currency->sign}
						<span class="sum_profit_price">+{$orders_price->sum_profit_price|convert} {$currency->sign}</span>
					</span>
				{/if}
			</h1>
			<form class="export_btn" method="post"
				action="{url view=ExportEntityAdmin entity=product_orders product_id=$product->id}" target="_blank">
				<input type="hidden" name="session_id" value="{$smarty.session.id}" />
				<input type="image" src="/{$config->templates_subdir}images/export_excel.png" name="export"
					title="Экспортировать заказы с товаром" />
			</form>
		</div>

		<div class="list">
			{foreach $orders as $order}
				<div class="{if $order->paid}highlight{/if} row">

					<div class="order_date">
						<a class="order_id"
							href="{url view=OrderAdmin id=$order->id return=$smarty.server.REQUEST_URI|urlencode}">№<span>{$order->id}</span></a>
						<div class="date">{$order->date|date}</div>
						<div class="time">{$order->date|time}</div>
					</div>

					<div class="order_name">

						<a href="{url view=OrderAdmin id=$order->id return=$smarty.server.REQUEST_URI|urlencode}">
							<span>{$order->name|escape}</span>
						</a>

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
							{if $user|user_access:orders_finance}
								<span class="profit_price">+{$order->profit_price|convert} {$currency->sign}</span>
							{/if}
						</div>
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
						{foreach $order->labels as $l}
							<span class="order_label_text" style="background-color:#{$l->color};"
								title="{$l->name}">{$l->name}</span>
						{/foreach}
					</div>

					<div class="icons">
						{if $order->paid}
							<img src='/{$config->templates_subdir}images/cash_stack.png' alt='Оплачен' title='Оплачен' />
						{else}
							<img src='/{$config->templates_subdir}images/cash_stack_gray.png' alt='Не оплачен'
								title='Не оплачен' />
						{/if}

						{if $order->status == 3}
							<img src='/{$config->templates_subdir}images/cross.png' alt='Отменен' title='Отменен' />
						{/if}
					</div>

				</div>
			{/foreach}
		</div>
	</div>
</div>


<script src="/{$config->templates_subdir}js/autocomplete/jquery.autocomplete-min.js"></script>
<script src="/{$config->templates_subdir}js/jquery/datepicker/jquery.ui.datepicker-ru.js"></script>

{include file='parts/charts_init.tpl'}

<script>
	let php_product_id = '{$product->id}';
	let php_currency_name = '{$currency->name}';
	let php_currency_sign = '{$currency->sign}';

	{literal}
		$(function() {

			// Выбор даты
			$('input[name="variants[awaiting_date][]"]').datepicker({regional: 'ru'});

			// Сортировка вариантов
			$('#variants_block').sortable({
				items: 'ul',
				handle: '.move_zone',
				tolerance: "pointer",
				opacity: 0.90,
				axis: 'y'
			});


			// Сортировка связанных товаров
			$('.sortable').sortable({
				items: '.row',
				handle: '.move_zone',
				tolerance: "pointer",
				opacity: 0.95,
				axis: 'y',
				update: function(event, ui) {
					colorize();
				}
			});


			// Добавление варианта
			let variant = $('#new_variant').clone(true).removeAttr('id');
			$('#new_variant').remove();

			$('#variants_block span.add').click(function() {
				if (!$('#variants_block').is('.single_variant')) {
					$(variant).clone(true).appendTo('#variants').fadeIn('slow').find(
						"input[name*=variant][name*=name]").focus();
				} else {
					$('#variants_block .variant_name').css("display", "revert-layer");
					$('#variants_block').removeClass('single_variant');
				}
				return false;
			});

			// Удаление варианта
			$('#variants_block').on('click', 'a.del_variant', function() {
				if ($("#variants ul").length > 1) {
					$(this).closest("ul").fadeOut(200, function() {
						$(this).remove();
					});
				} else {
					$('#variants_block .variant_name input[name*=variant][name*=name]').val('');
					$('#variants_block .variant_name').hide('slow');
					$('#variants_block').addClass('single_variant');
				}
				return false;
			});


			// Добавление связанного товара 
			let new_related_product = $('#new_related_product').clone(true).removeAttr('id');
			$('#new_related_product').remove();

			$("input#related_products").autocomplete({
				serviceUrl: '/app/agmin/ajax/search_products.php',
				minChars: 0,
				noCache: false,
				onSelect: function(suggestion) {
					$("input#related_products").val('').focus().blur();
					new_item = new_related_product.clone().appendTo('.related_products');
					new_item.find('a.related_product_name').html(suggestion.data.name);
					new_item.find('a.related_product_name').attr('href',
						'?view=ProductAdmin&id=' + suggestion.data.id);
					new_item.find('input[name*="related_products"]').val(suggestion.data.id);
					if (suggestion.data.image)
						new_item.find('img.product_icon').attr("src", suggestion.data.image);
					else
						new_item.find('img.product_icon').remove();

					if (suggestion.data.disable == 1)
						new_item.addClass("disable");
					if (suggestion.data.visible == 0)
						new_item.addClass("visible_off");

					new_item.show();
				},
				formatResult: function(suggestions, currentValue) {
					let reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g');
					let pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
					return (suggestions.data.image ? "<img align=absmiddle src='" + suggestions.data
						.image + "'> " : '') + suggestions.value.replace(new RegExp(pattern, 'gi'),
						'<strong>$1<\/strong>');
				}
			});

			// Удаление связанного товара
			$(".related_products").on('click', 'a.delete', function() {
				$(this).closest("div.row").fadeOut(200, function() {
					$(this).remove();
				});
				return false;
			});


			// Выводим график
			show_stat_graphic(
				'product_stats',
				{product_id: php_product_id, filter: 'byMonth'},
				['totalPrice', 'profitPrice', 'amount', 'add', 'delete'],
				options,
				php_currency_sign
			);


			// Бесконечность на складе
			$("input[name*=variants][name*=stock]").focus(function() {
				if ($(this).val() == '∞')
					$(this).val('');
				return false;
			});

			$("input[name*=variants][name*=stock]").blur(function() {
				if ($(this).val() == '')
					$(this).val('∞');
			});


			// Редактирование колонки input
			$("#variants_block").on('dblclick', 'div.input_disabled', function() {
				let select_column = $(this).parent().attr('class');

				// Открываем input всей колонки
				$("." + select_column).find('input').prop('disabled', false);
				$(this).parent().find('input').focus();
				$("." + select_column).find('div.input_disabled').remove();
				return false;
			});

		});
	{/literal}
</script>