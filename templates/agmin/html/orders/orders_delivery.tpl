{include file='orders/orders_settings_menu_part.tpl'}

{if $delivery->id}
	{$meta_title = $delivery->name scope=global}
{else}
	{$meta_title = 'Новый способ доставки' scope=global}
{/if}


{if $message_success}
	<!-- Системное сообщение -->
	<div class="message message_success">
		<span class="text">{if $message_success == 'added'}Способ доставки
			добавлен{elseif $message_success == 'updated'}Способ доставки изменен
			{/if}</span>
	</div>
{/if}

{if $message_error}
	<!-- Системное сообщение -->
	<div class="message message_error">
		<span class="text">{$message_error}</span>
		<a class="button" href="">Вернуться</a>
	</div>
{/if}


<!-- Основная форма -->
<form method="post" class="form_css" enctype="multipart/form-data">
	<input type="hidden" name="session_id" value="{$smarty.session.id}" />
	<div class="columns">
		<div class="block_flex w100">
			<div class="over_name">
				<div class="checkbox_line">
					<div class="checkbox_item">
						<input name="enabled" value="1" type="checkbox" id="active_checkbox"
							{if $delivery->enabled}checked{/if} />
						<label for="active_checkbox">Активен</label>
					</div>
				</div>
			</div>

			<div class="name_row">
				<input class="name" name="name" type="text" value="{$delivery->name|escape}" />
				<input name="id" type="hidden" value="{$delivery->id}" />
			</div>
		</div>

		<div class="block_flex layer">
			<h2>Стоимость доставки</h2>
			<ul class="property_block">
				<li>
					<label for="price" class="property_name">Стоимость</label>
					<div class="whith_unit">
						<input id="price" name="price" class="small_inp" type="text" value="{$delivery->price}" />
						<span class="label_unit"> {$currency->sign}</span>
					</div>
				</li>
				<li>
					<label for="free_from" class="property_name">Бесплатна от</label>
					<div class="whith_unit">
						<input id="free_from" name="free_from" class="small_inp" type="text"
							value="{$delivery->free_from}" />
						<span class="label_unit">{$currency->sign}</span>
					</div>
				</li>
				<li class="checkbox_item">
					<input id="separate_payment" name="separate_payment" type="checkbox" value="1"
						{if $delivery->separate_payment}checked{/if} />
					<label for="separate_payment">Оплачивается отдельно</label>
				</li>
				<li>
					<label class="property_name" for="finance_purse_id">Кошелек для оплаты доставки</label>
					<select name="finance_purse_id" id="finance_purse_id">
						<option value="">Не выбран</option>
						{foreach $finance_purses as $finance_purse}
							<option class="{if !$finance_purse->enabled}disabled{/if}" value="{$finance_purse->id}"
								{if $delivery->finance_purse_id == $finance_purse->id}selected{/if}>
								{$finance_purse->name|escape}</option>
						{/foreach}
					</select>
				</li>
			</ul>
		</div>

		<div class="block_flex layer">
			<h2>Возможные способы оплаты</h2>
			<div>
				{foreach $payment_methods as $payment_method}
					<div class="checkbox_item {if !$payment_method->enabled}disabled{/if}">
						<input type="checkbox" name="delivery_payments[]" id="payment_{$payment_method->id}"
							value='{$payment_method->id}'
							{if in_array($payment_method->id, $delivery_payments)}checked{/if}>
						<label for="payment_{$payment_method->id}">{$payment_method->name}</label>
					</div>
				{/foreach}
			</div>
		</div>

		<div class="block_flex layer">
			<h2>Модуль доставки</h2>
			<ul class="property_block">
				<li>
					<label class="property_name" for="module">Модуль</label>
					<select name="module" id="module">
						<option value="">Без модуля</option>
						{foreach $delivery_modules as $delivery_module}
							<option value="{$delivery_module@key|escape}"
								{if $delivery->module == $delivery_module@key}selected{/if}>
								{$delivery_module->name|escape}</option>
						{/foreach}
					</select>
				</li>
			</ul>
		</div>


		{foreach $delivery_modules as $delivery_module}
			<div class="block_flex layer module_settings"
				{if $delivery_module@key != $delivery->module}style='display:none;' {/if} module="{$delivery_module@key}">
				<h2>{$delivery_module->name}</h2>

				{* Параметры модуля доставки *}
				<ul class="property_block">
					{foreach $delivery_module->settings as $setting}
						{$variable_name = $setting->variable}
						{if count((array)$setting->options) > 1}
							<li>
								<label class="property_name"
									for="{$delivery_module@key}-{$setting->variable}">{$setting->name}</label>
								<select name="delivery_settings[{$setting->variable}]"
									id="{$delivery_module@key}-{$setting->variable}">
									{foreach $setting->options as $option}
										<option value='{$option->value}'
											{if $option->value==$delivery_settings[$setting->variable]}selected{/if}>
											{$option->name|escape}</option>
									{/foreach}
								</select>
							</li>
						{elseif count((array)$setting->options) == 1}
							{$option = $setting->options|@first}
							<li>
								<label class="property_name"
									for="{$delivery_module@key}-{$setting->variable}">{$setting->name|escape}</label>
								<input name="delivery_settings[{$setting->variable}]" type="checkbox"
									value="{$option->value|escape}"
									{if $option->value==$delivery_settings[$setting->variable]}checked{/if}
									id="{$delivery_module@key}-{$setting->variable}" />
							</li>
						{else}
							<li>
								<label class="property_name"
									for="{$delivery_module@key}-{$setting->variable}">{$setting->name|escape}</label>
								<input name="delivery_settings[{$setting->variable}]" type="text"
									value="{$delivery_settings[$setting->variable]|escape}"
									id="{$delivery_module@key}-{$setting->variable}" />
							</li>
						{/if}
					{/foreach}
				</ul>
				{* END Параметры модуля доставки *}

			</div>
		{/foreach}

		<div class="block_flex layer module_settings" {if $delivery->module != ''}style='display:none;' {/if} module="">
		</div>

		<div class="block_flex w100 layer">
			<h2>Описание</h2>
			<textarea name="description" class="html_editor editor_small">{$delivery->description|escape}</textarea>
		</div>

		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="" value="Сохранить" />
		</div>
	</div>
</form>

{* Подключаем Tiny MCE *}
{include file='parts/tinymce_init.tpl'}


<script>
	{literal}
		// On document load
		$(function() {
			$('div.module_settings').filter(':hidden').find("input, select, textarea").prop("disabled", true);
			$('select[name=module]').change(function() {
				$('div.module_settings').hide().find("input, select, textarea").prop("disabled", true);
				$("div.module_settings[module='" + $(this).val() + "']").show().find("input, select, textarea")
					.prop("disabled", false);
			});
		});
	{/literal}
</script>