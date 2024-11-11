{include file='finance/finance_menu_part.tpl'}

{if $payment->id}
	{$meta_title = "Платеж №`$payment->id`" scope=global}
{else}
	{$meta_title = "Новый платеж" scope=global}
{/if}

{if $message_success}
	<div class="message message_success">
		<span class="text">{if $message_success=='updated'}Платеж обновлен
			{elseif $message_success=='added'}Платеж
			добавлен{else}{$message_success}
			{/if}</span>
	</div>
{/if}


{if $message_error}
	<!-- Системное сообщение -->
	<div class="message message_error">
		<span class="text">
			{if $message_error=='error update'}
				Ошибка обновления
			{elseif $message_error=='error add'}
				Ошибка добавления
			{elseif $message_error=='error uploading image'}
				Ошибка при загрузке изображения
			{/if}
		</span>
	</div>
{/if}


<form method="post" class="form_css" enctype="multipart/form-data">
	<input type="hidden" name="session_id" value="{$smarty.session.id}" />
	<input name="id" type="hidden" value="{$payment->id|escape}" />

	<div class="header_top">
		<h1>{if $payment->id|isset}
				{if $cur_type == 2}Перевод{else}Платеж{/if} #{$payment->id|escape}
			{else}
				Новый {if $cur_type==2}перевод{else}платеж{/if}
			{/if}
		</h1>
	</div>

	<div class="columns">

		<div class="block_flex">
			<ul class="property_block">
				<li>
					<label class="property_name" for="type">Тип платежа</label>
					<select class="status" name="type" id="type" {if $cur_type == 2 || $payment->verified}disabled{/if}>
						{if ($cur_type == 2 AND $payment->type == 0) || $cur_type != 2 || ($cur_type == 2 AND !$rel_payment->id)}
							<option value="0" {if $payment->type == 0}selected{/if}>Расход</option>
						{/if}
						{if (($cur_type == 2 AND $payment->type == 1) || $cur_type != 2)}
							<option value="1" {if $payment->type == 1 || $cur_type == 1}selected{/if}>Приход</option>
						{/if}
					</select>
				</li>

				<li>
					<label class="property_name" for="purse_id">Кошелек</label>
					<select name="purse_id" id="purse_id" {if $payment->verified}disabled{/if}>
						{foreach $purses as $p}
							<option class="{if !$p->enabled}disabled{/if}" value="{$p->id}"
								currency_sign="{$p->currency_sign}" currency_id="{$p->currency_id}"
								{if $payment->purse_id == $p->id}selected{/if}>{$p->name}
								({$p->currency_sign})
							</option>
						{/foreach}
					</select>
				</li>

				{if $cur_type == 2}
					<li>
						<label class="property_name" for="purse_to_id">Кошелек
							{if $payment->type == 0}куда{else}откуда{/if}</label>
						<select name="purse_to_id" id="purse_to_id" {if $payment->verified}disabled{/if}>
							{foreach $purses as $p}
								<option class="{if !$p->enabled}disabled{/if}" value="{$p->id}"
									currency_sign="{$p->currency_sign}" currency_id="{$p->currency_id}"
									{if $rel_payment->purse_id|isset AND $rel_payment->purse_id == $p->id}selected{/if}>
									{$p->name} ({$p->currency_sign})
								</option>
							{/foreach}
						</select>
					</li>
				{else}
					<li>
						<label class="property_name" for="finance_category_id">Категория</label>
						<select name="finance_category_id" id="finance_category_id" {if $payment->verified}disabled{/if}>
							{foreach $categories as $c}
								<option value="{$c->id}" class="type_{$c->type}" {if $payment->finance_category_id == $c->id}
									selected {/if}>{$c->name}</option>
							{/foreach}
						</select>
					</li>
				{/if}

				<li class="mt_40">
					<label for="amount" class="property_name">Сумма</label>

					<div class="property_value">
						<div class="whith_unit">
							<input id="amount" class="small_inp numbermask_2" type="text" name="amount"
								value="{$payment->amount}" autocomplete='off' {if $payment->verified}disabled{/if} />
							<span id="currency_sign" class="label_unit">{$current_currency->sign}</span>
						</div>
						<div class="whith_unit">
							<input name="currency_rate" id="currency_rate" class="small_inp numbermask_4" type="text"
								value="{if $payment->currency_rate>0}{$payment->currency_rate}{else}{$current_currency->rate_to}{/if}"
								{if $payment->verified}disabled{/if} />
							<input type="hidden" name="currency_amount" value="{$payment->currency_amount}"
								autocomplete='off' {if $payment->verified}disabled{/if} />
							<span class="label_unit" id="to_currency">{$payment->currency_amount}
								{$to_currency->sign}</span>
						</div>
					</div>
				</li>

				<li class="mt_40">
					<label class="property_name" for="comment">Комментарий</label>
					<textarea id="comment" name="comment"
						{if $payment->verified}disabled{/if}>{$payment->comment|escape}</textarea>
				</li>
			</ul>

			<div class="btn_row">
				<input class="button_green" type="submit" value="Сохранить" />
			</div>
		</div>


		<div class="block_flex">
			<div class="order_date_time">
				{if !$payment->date|empty}
					<div>
						Создан <span>{$payment->date|date} в {$payment->date|time}</span>
					</div>
				{/if}

				{if !$payment->manager->id|empty}
					<div class="order_manager">
						Кем: <a href="{url view=UserAdmin id=$payment->manager->id clear=true}"
							target="_blank">{$payment->manager->name|escape}</a>
					</div>
				{/if}

				{if !$payment->purse_amount|empty}
					<div>
						Остаток: <span>{$payment->purse_amount|price_format:2:true} {$payment->currency_sign}</span>
					</div>
				{/if}

				{if !$payment->id|empty}
					<div class="checkbox_item mt_20">
						<input type="checkbox" name="verified" id="verified" value="1" {if $payment->verified}checked{/if}>
						<label for="verified">Сверено</label>
					</div>
				{/if}

				{if $payment->verified}
					<div>
						Проверен <span>{$payment->verified_date|date} в {$payment->verified_date|time}</span>
					</div>
					<div class="order_manager">
						Кем: <a href="{url view=UserAdmin id=$payment->verified_user->id clear=true}"
							target="_self">{$payment->verified_user->name|escape}</a>
					</div>
				{/if}
			</div>




			<!-- Изображения -->
			<div id="images" class="layer images">
				<h2>Фотоотчет</h2>
				<ul>
					{if !$payment->images|empty}
						{foreach $payment->images as $image}
							<li>
								<span class="delete">
									<img src="/{$config->templates_subdir}images/cross-circle-frame.png" />
								</span>

								<a href="{$image->filename|resize:1080:1080}" class="zoom" data-fancybox="images"
									data-caption="{$payment->comment|escape}">
									<img src="{$image->filename|resize:220:220}" />
								</a>
								<input type="hidden" name="images[]" value="{$image->id}" />
							</li>
						{/foreach}
					{/if}
				</ul>

				<div class="dropZone">
					<input type="file" name="dropped_images[]" multiple class="dropInput" />
					<div class="dropMessage">Перетащите файлы сюда</div>
				</div>

				<div class="add_image"></div>
				<span class="upload_image">
					<i class="dash_link">Добавить изображение</i>
				</span>
			</div>


			<!-- Контрагент -->
			<div class="layer">
				<h2>Контрагент
					<span class="btn_icon btn_edit_entity">
						<img src="/{$config->templates_subdir}images/pencil.png" alt="Редактировать"
							title="Редактировать">
					</span>
					{if $contractor}
						<span class="btn_icon btn_delete_entity">
							<img src="/{$config->templates_subdir}images/delete.png" alt="Удалить" title="Удалить">
						</span>
					{/if}
				</h2>

				<div class='view_entity'>
					{if !$contractor->entity_id|empty}
						<a
							href="{url view=$contractor->view_name id=$contractor->entity_id clear=true}">{$contractor->entity->name}</a>
					{/if}
				</div>

				<div class="edit_entity" {if $contractor}style="display:none;" {/if}>
					<ul class="property_block">
						<li>
							<label class="property_name" for="entity_name">Тип контрагента</label>
							<select name="entity_name" id="entity_name">
								<option value="">Выбирите тип контрагента</option>
								{foreach $contractor_types as $contr}
									<option value="{$contr['entity_name']}" data-type="{$contr['search']}"
										{if $contr['entity_name'] == 'user'} data-sort="manager" {/if}
										{if !$contractor->entity_name|empty and $contractor->entity_name == $contr['entity_name']}selected{/if}>
										{$contr['name']}
									</option>
								{/foreach}
							</select>
						</li>
						<li class="hide_input select_entity">
							<label class="property_name" for="entity">Контрагент</label>
							<input type="hidden" name="entity_id" value="{$contractor->entity_id ?? ''}">
							<input type="text" id="entity" class="input_autocomplete"
								placeholder="Выберите контрагента">
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</form>

{* Подключаем Fancybox*}
{include file='parts/images_upload_init.tpl'}

<script src="/{$config->templates_subdir}js/autocomplete/jquery.autocomplete-min.js"></script>
<script src="/{$config->templates_subdir}js/jquery/jquery.numbermask.js"></script>


<script>
	{if $cur_type != 2}
		{if $payment->id}
			let current_type = '{$payment->type}';
			let current_finance_category_id = '{$payment->finance_category_id}';
		{else}
			let current_type = '{$cur_type}';
			let current_finance_category_id = null;
		{/if}
	{else}
		let current_type = null;
		let current_finance_category_id = null;
	{/if}

	let currencies = {$currencies|json_encode};
	let purses = {$purses|json_encode};
	let to_currency_sign = '{$to_currency->sign}';

	{literal}

		$(function() {

			// Сhange main purse
			$('select[name="purse_id"]').change(function() {
				let purse_id = $(this).val();

				let index_purse = purses.findIndex(function(x) {
					return x.id === purse_id
				})

				let purse_currency_id = purses[index_purse].currency_id;
				let currency = '';

				for (const [key, value] of Object.entries(currencies)) {
					if (value.id == purse_currency_id) {
						currency = value;
					}
				}

				let currency_sign = $('option:selected', this).attr('currency_sign');
				$('#currency_sign').html(currency_sign);

				$('input[name="currency_rate"]').val(currency.rate_to);
				currency_amount_update();
			});


			// Change to purse
			$('select[name="purse_to_id"]').change(function() {
				to_currency_sign = $('option:selected', this).attr('currency_sign');
				currency_amount_update();
			});


			// Обновляем сумму по курсу
			$('input[name="amount"], input[name="currency_rate"]').keyup(function() {
				currency_amount_update();
			});

			function currency_amount_update() {
				let currency_rate = $('input[name="currency_rate"]').val().replace(",", ".");
				let amount = $('input[name="amount"]').val().replace(",", ".");
				let result = (currency_rate * amount).toFixed(2);
				$('#to_currency').html(result + ' ' + to_currency_sign);
				$('input[name="currency_amount"]').val(result);
			}


			// Устанавливаем категорию
			change_categories(current_type);

			function change_categories(type) {
				$('select[name="finance_category_id"]').find('option').hide();
				$('select[name="finance_category_id"]').find('option.type_' + type).show();
			}


			// Смена типа платежа
			$('select[name="type"]').change(function() {
				var type = $(this).val()
				change_categories(type);

				// Set first option
				$('select[name="finance_category_id"]').val($(
					'select[name="finance_category_id"] option.type_' + type + ':first').val());
			});


			// Выбираем контрагента
			$(".btn_edit_entity").click(function() {
				$("div.view_entity").hide();
				$("div.edit_entity").show();
				$('input[name="entity_id"]').val('');
				$('select[name="entity_name"]').val('');
				return false;
			});


			// удаляем контрагента
			$(".btn_delete_entity").click(function() {
				$("div.view_entity").hide();
				$("div.edit_entity").show();
				$('input[name="entity_id"]').val('');
				$('select[name="entity_name"]').val('');
				return false;
			});


			// Выбираем тип контрагента
			$('select[name="entity_name"]').change(function() {
				let entity_type = $(this).find('option:selected').data('type');
				let params = {};

				let entity_sort = $(this).find('option:selected').data('sort');
				if (entity_sort) {
					params.sort = entity_sort;
				}

				if (entity_type !== undefined) {
					$(".select_entity").removeClass('hide_input');
					$("input#entity").autocomplete({
						serviceUrl: '/app/agmin/ajax/' + entity_type + '.php',
						minChars: 0,
						noCache: false,
						params: params,
						onSelect: function(suggestion) {
							$('input[name="entity_id"]').val(suggestion.data.id);
						}
					});
				} else {
					$('input[name="entity_id"]').val('');
					$("input#entity").val('');
					$(".select_entity").hide();
				}
			});


			// Устанавливаем формат
			$('input.numbermask_2').numberMask({type: 'float', afterPoint: 2, decimalMark: ['.', ',']});
			$('input.numbermask_4').numberMask({type: 'float', afterPoint: 4, decimalMark: ['.', ',']});
		});

	{/literal}
</script>