{include file='warehouse/warehouse_menu_part.tpl'}

{if $movement->id}
	{$meta_title = "Поставка №`$movement->id`" scope=global}
{else}
	{$meta_title = 'Новая поставка' scope=global}
{/if}

{if $message_error}
	<!-- Системное сообщение -->
	<div class="message message_error">
		<span class="text">{if $message_error == 'error_closing'}Нехватка товара на
			складе{else}{$message_error|escape}
			{/if}</span>
	</div>
{elseif $message_success}
	<div class="message message_success">
		<span class="text">{if $message_success == 'updated'}Перемещение
			обновлено{elseif $message_success == 'added'}Пермещение добавлено
			{else}{$message_success}
			{/if}</span>
	</div>
{/if}

<form method="post" id="order" enctype="multipart/form-data" class="form_css two_columns_order">
	<input type="hidden" name="session_id" value="{$smarty.session.id}" />
	<input name="id" type="hidden" value="{$movement->id|escape}" />

	<div class=header_top>
		<h1>{if $movement->id}Перемещение №{$movement->id|escape}{else}Новое перемещение товра{/if}
			<select class=status name="status" {if !$can_edit}disabled{/if}>
				<option value="0" {if $movement->status == 0}selected{/if}>Новое</option>
				{if $movement->status != 3 AND $movement->status != 4}
					<option value="1" {if $movement->status == 1}selected{/if}>Ожидаем</option>
					<option value="2" {if $movement->status == 2}selected{/if}>Поступило</option>
				{/if}
				{if $movement->status != 1 AND $movement->status != 2}
					<option value="3" {if $movement->status == 3}selected{/if}>Списано</option>
				{/if}
				<option value="4" {if $movement->status == 4}selected{/if}>Отмена</option>
			</select>
		</h1>

		{if $movement->id}
			<a class="print_icon fl_r" href="{url view=WarehouseMovementAdmin id=$movement->id type=print}" target="_blank">
				<img src="/{$config->templates_subdir}images/printer.png" title="Печать поставки">
			</a>
		{/if}
	</div>


	<div id="order_details">
		<h2>Детали поставки</h2>

		<div class="order_date_time">
			{if !$movement->date|empty}
				<div>
					Создан {$movement->date|date} в {$movement->date|time} <span class="fl_r">Изменён
						{$movement->modified|date} в {$movement->modified|time}</span>
				</div>
			{/if}

			{if !$movement->manager|empty}
				<div class="order_manager">
					Последняя правка: <a href="{url view=UserAdmin id=$movement->manager->id clear=true}"
						target="_blank">{$movement->manager->name|escape}</a>
				</div>
			{/if}

			<ul>
				<li class="awaiting_date">
					<label class="property_name" for="awaiting_date">Дата
						{if $movement->status == 2 || $movement->status == 1}поставки{elseif $movement->status == 3}списания{elseif $movement->status == 4}отмены{else}перемещения{/if}</label>
					<input id="awaiting_date" type="text" name="awaiting_date" value="{$movement->awaiting_date|date}"
						{if !$can_edit}disabled{/if} />
				</li>
			</ul>
		</div>

		<!-- Примечания -->
		<div class="note_wrap layer">
			<h2>Примечание
				{if $can_edit}
					<a href="#" class="edit_note">
						<img src="/{$config->templates_subdir}images/pencil.png" alt="Редактировать" title="Редактировать">
					</a>
				{/if}
			</h2>

			<ul class="note_block">
				<li>
					<div class="edit_note" style="display:none;">
						<textarea name="note">{$movement->note|escape}</textarea>
					</div>

					<div class="view_note" {if !$movement->note}style="display:none;" {/if}>
						<div class="note_text">{$movement->note|escape}</div>
					</div>
				</li>
			</ul>
		</div>


		{if $user|user_access:warehouse_edit}
			<div class="note_wrap layer">
				<h2>Примечаниe логиста
					<a href="#" class="edit_note">
						<img src="/{$config->templates_subdir}images/pencil.png" alt="Редактировать" title="Редактировать">
					</a>
				</h2>

				<ul class="note_block">
					<li>
						<div class="edit_note" style="display:none;">
							<textarea name="note_logist">{$movement->note_logist|escape}</textarea>
						</div>

						<div class="view_note" {if !$movement->note_logist}style="display:none;" {/if}>
							<div class="note_text">{$movement->note_logist|escape}</div>
						</div>
					</li>
				</ul>
			</div>
		{/if}


		<!-- Параметры партии -->
		{if $total->weight > 0}
			<div class="layer">
				<h2>Параметры партии</h2>

				<div class="order_details_row total_wholesale_price">
					Общий вес: <b>{$total->weight|convert} <span class="currency">{$settings->weight_units}</span></b>
				</div>
				{if $user|user_access:finance}
					<div class="order_details_row total_wholesale_price">
						Закупка: <b>{$total->wholesale|convert} <span class="currency">{$currency->sign}</span></b>
					</div>
					<div class="order_details_row total_retail_price">
						Продажи: <b>{$total->retail|convert} <span class="currency">{$currency->sign}</b> </span>
					</div>
					<div class="order_details_row total_profit_price">
						Прибыль: <b>{($total->retail - $total->wholesale)|convert} <span
								class="currency">{$currency->sign}</span></b>
					</div>
				{/if}
			</div>
		{/if}


		{if $user|user_access:finance}
			<div class="layer">
				<h2>Финансы</h2>

				{if !$payments|empty}
					<div class="order_details_row total_wholesale_price">Всего: <b>{$total->payments|convert} <span
								class="currency">{$currency->sign}</span></b></div>
				{/if}

				<div class="btn_row">
					<a class="button"
						href="{url view=FinancePaymentAdmin cur_type=0 contractor_entity_name=wh_movement contractor_entity_id=$movement->id return=$smarty.server.REQUEST_URI clear=true}">Добавить
						платеж</a>
				</div>

				{if !$payments|empty}
					<div class="list">
						{foreach $payments as $p}
							<div class="row {if !$p->verified}verified_off{else}verified_on{/if}" item_id="{$p->id}">
								<div class="payment_amount {if $p->type == 0}minus{/if} {if $p->related_payment_id}transfer{/if}">
									<a href="{url view=FinancePaymentAdmin id=$p->id}">{if $p->type == 0}-{else}+{/if}{$p->amount|convert}
										{$p->currency_sign}</a>
									{if $p->currency_rate!=1 AND !$p->related_payment_id}<div class="notice">{$p->currency_amount}
										{$currency->sign}</div>{/if}
									<div class="order_date">
										<div class="date">{$p->date|date}</div>
										<div class="time">{$p->date|time}</div>
									</div>
								</div>
								<div class="user_name">
									{if $p->category_name}
										{$p->category_name}
									{else}
										Премещение между кошельками
									{/if} <div class="notice">{$p->comment}</div>
								</div>

								<div class="icons">
									<a class="verified edit" title="Cверка с бухгалтерией"></a>
								</div>
							</div>
						{/foreach}
					</div>
				{/if}

			</div>
		{/if}

	</div>


	<!-- Список поставки -->
	<div id="purchases">
		<div class="list purchases">

			{if !$purchases|empty}
				{foreach $purchases as $purchase}
					<div class="row">

						<div class="move">
							<div class="move_zone"></div>
						</div>

						<div class="image">
							<input type="hidden" name="purchases[id][]" value="{$purchase->id}" />

							{$image = $purchase->product->images|first}
							<img class="product_icon"
								src="{if $image}{$image->filename|resize:50:50}{else}{$config->templates_subdir}images/cargo.png{/if}" />
						</div>

						<div class="name product_name">
							{if $purchase->product}
								<a href="{url view=ProductPriceAdmin id=$purchase->product->id}">{$purchase->product_name}</a>
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
									{if $purchase->product->variants|count==1 && $purchase->variant_name == '' && $purchase->variant->sku == ''}style="display:none;"
									{/if}>

									{* если вариант удален, показываем сохраненный в перемещении *}
									{if !$purchase->variant}
										<option price="{$purchase->price}" cost_price="{$purchase->cost_price}"
											amount="{$purchase->amount}" value="">
											{$purchase->variant_name|escape} {if $purchase->sku}(арт. {$purchase->sku}){/if}
										</option>
									{/if}

									{foreach $purchase->product->variants as $v}
										<option price="{$v->price}" cost_price="{$v->cost_price}" amount='{$v->stock}'
											weight='{$v->weight}' value='{$v->id}'
											{if $v->id == $purchase->variant_id}selected{/if}>
											{$v->name} {if $v->sku}(арт. {$v->sku}){/if}
										</option>
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

						{if $user|user_access:products_price}
							<div class="price">
								<div class="cost_price">
									<span class="js_change">{$purchase->price}</span>
									<span class="price_sign">{$currency->sign}</span>
								</div>

								<div>
									<div class="view_purchase">
										{$purchase->cost_price|convert}<span class="price_sign">{$currency->sign}</span>
									</div>
									<div class="edit_purchase" style="display:none;">
										<input type="text" name="purchases[cost_price][]" value="{$purchase->cost_price}"
											size="5" /> {$currency->sign}
									</div>
								</div>
							</div>
						{/if}

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
									<select name="purchases[amount][]">
										{section name=amounts start=1 loop=$settings->max_order_amount step=1}
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

							{if $purchase->variant}
								<div class="variant_stock">остаток: <span class="js_change">{$purchase->variant->stock}</span>
								</div>
							{else}
								{if !$purchase->product}
									<img src="/{$config->templates_subdir}images/error.png" alt="Товар был удалён"
										title="Товар был удалён">
								{elseif !$purchase->variant}
									<img src="/{$config->templates_subdir}images/error.png" alt="Вариант товара был удалён"
										title="Вариант товара был удалён">
								{/if}
							{/if}
						</div>

						{if $user|user_access:products_marking}
							<div class="icons">
								<a href="{url view=ProductMarkingAdmin variant_id=$purchase->variant->id type=print clear=true}"
									target="_blank" class="print" title="Распечать этикету"></a>

								{if $can_edit}
									<a href="#" class="delete" title="Удалить"></a>
								{/if}
							</div>
						{/if}
					</div>
				{/foreach}
			{/if}

			<div id="new_purchase" class="row sort_disabled" style="display:none;">
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
					{if $user|user_access:products_price}
						<div class="cost_price">
							<span class="js_change"></span>
							<span class="price_sign">{$currency->sign}</span>
						</div>
					{/if}
					<div>
						<input type="text" name="purchases[cost_price][]" value="" size="5"
							{if !$user|user_access:products_price} disabled {/if} /> {$currency->sign}
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

		<div id="add_purchase" {if !$purchases|empty}style="display:none;" {/if}>
			<input type="text" id="add_purchase" class="input_autocomplete"
				placeholder="Выберите товар чтобы добавить его">
		</div>

		{if $purchases and $can_edit}
			<span class="dash_link edit_purchases">редактировать покупки</span>
		{/if}


		<!-- Изображения -->
		{if $movement->images || $can_edit}
			<div id="images" class="block_flex layer images">
				<h2>Фотоотчет</h2>

				<ul>
					{foreach $movement->images as $image}
						<li>
							{if $can_edit}
								<span class="delete">
									<img src="/{$config->templates_subdir}images/cross-circle-frame.png" />
								</span>
							{/if}

							<a href="{$image->filename|resize:1080:1080}" class="zoom" data-fancybox="images"
								data-caption="{$payment->comment|escape}">
								<img src="{$image->filename|resize:220:220}" />
							</a>
							<input type="hidden" name="images[]" value="{$image->id}" />
						</li>
					{/foreach}
				</ul>

				{if $can_edit}
					<div class="dropZone">
						<input type="file" name="dropped_images[]" multiple class="dropInput" />
						<div class="dropMessage">Перетащите файлы сюда</div>
					</div>

					<div class="add_image"></div>

					<span class="upload_image">
						<span class="dash_link">Добавить изображение</span>
					</span>
				{/if}
			</div>
		{/if}

		{if $can_edit}
			<div class="btn_row">
				<input class="button_green" type="submit" name="" value="Сохранить" />
			</div>
		{/if}

	</div>
</form>

<script src="/{$config->templates_subdir}js/autocomplete/jquery.autocomplete-min.js"></script>
<script src="/{$config->templates_subdir}js/jquery/datepicker/jquery.ui.datepicker-ru.js"></script>

{include file='parts/images_upload_init.tpl'}

<script>
	const currency = '{$currency->sign}';
	const max_order_amount = '{$settings->max_order_amount}';
	const units = '{$settings->units}';
	const session_id = '{$smarty.session.id}';

	{literal}

		// On document load 
		$(function() {

			// Редактировать примечание (universal)
			$(".note_wrap a.edit_note").click(function() {
				let layer = $(this).closest('div.note_wrap');
				let text_height = layer.find("div.view_note").height() + 5;
				layer.find("div.edit_note textarea").height(text_height);
				layer.find("div.view_note").hide();
				layer.find("div.edit_note").show();
				return false;
			});


			// Выбор даты
			$('input[name="awaiting_date"]').datepicker({
				regional: 'ru'
			});


			// Сортировка вариантов
			$("#purchases").sortable({
				items: '.row:not(.sort_disabled)',
				handle: ".move_zone",
				cancel: ".sort_disabled",
				tolerance: "pointer",
				opacity: 0.95,
				axis: 'y',
				update: function(event, ui) {
					$("#purchases input[name*='check']").prop('checked', false);
					colorize();
				}
			});


			// Удаление товара
			$(".purchases").on('click', 'a.delete', function() {
				$(this).closest(".row").fadeOut(200, function() { $(this).remove(); });
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
						.data.variants[0].price + currency + '</b><span>' + stock_txt + movement;
				}
			});


			//  Выбор варианта товара. В том числе при добавлении
			$('#purchases').on('change', 'select[name*=purchases][name*=variant_id]', function() {
				change_variant($(this));
			});


			// Изменение макс количества при изменении варианта
			function change_variant(element) {
				let cost_price = element.find('option:selected').attr('cost_price');
				let price = element.find('option:selected').attr('price');
				let weight = element.find('option:selected').attr('weight');

				// Выбираем доступное кол-во товара (по складу)
				let amount = element.find('option:selected').attr('amount');

				element.closest('.row').find('.cost_price .js_change').text(price);
				element.closest('.row').find('input[name*=purchases\\[cost_price\\]]').val(cost_price);
				element.closest('.row').find('.variant_stock .js_change').text(amount);
				element.closest('.row').find('.amount_waight .js_change ').text(weight);

				// Выбираем текущеее выбранное кол-во товаров 
				const amount_select_el = element.closest('.row').find('select[name*=purchases][name*=amount]');
				let selected_amount = (amount_select_el.val() || 1);
				amount_select_el.html('');

				for (let i = 1; i <= max_order_amount; i++)
					amount_select_el.append("<option value='" + i + "'>" + i + " " + units + "</option>");

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

		});

	{/literal}
</script>