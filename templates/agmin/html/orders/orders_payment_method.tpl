{include file='orders/orders_settings_menu_part.tpl'}

{if $payment_method->id}
	{$meta_title = $payment_method->name scope=global}
{else}
	{$meta_title = 'Новый способ оплаты' scope=global}
{/if}


{if $message_success}
	<!-- Системное сообщение -->
	<div class="message message_success">
		<span class="text">{if $message_success == 'added'}Способ оплаты
			добавлен{elseif $message_success == 'updated'}Способ оплаты изменен
			{/if}</span>
	</div>
{/if}


<!-- Основная форма -->
<form method=post class=form_css enctype="multipart/form-data">
	<input type=hidden name="session_id" value="{$smarty.session.id}">

	<div class=columns>

		<div class="block_flex w100">
			<div class="over_name">
				<div class="checkbox_line">
					<div class="checkbox_item">
						<input name="enabled" value="1" type="checkbox" id="enabled"
							{if $payment_method->enabled}checked{/if} />
						<label for="enabled">Показывать менеджеру</label>
					</div>
					<div class="checkbox_item">
						<input name="enabled_public" value="1" type="checkbox" id="enabled_public"
							{if $payment_method->enabled_public}checked{/if} />
						<label for="enabled_public">Показывать клиенту при заказе</label>
					</div>
				</div>
			</div>

			<div class="name_row">
				<input name="id" type="hidden" value="{$payment_method->id}" />
				<input class="name" name="name" type="text" value="{$payment_method->name|escape}" autocomplete="off" />
			</div>
		</div>


		<div class="block_flex layer">
			<h2>Настройки оплаты</h2>
			<ul class="property_block">
				<li>
					<label for="public_name">Публичное название</label>
					<input name="public_name" id="public_name" type="text"
						value="{$payment_method->public_name|escape}" />
				</li>
				<li>
					<label for="module">Модуль Оплаты</label>
					<select name="module" id="module">
						<option value="">Ручная обработка</option>
						{foreach $payment_modules as $payment_module}
							<option value="{$payment_module@key|escape}"
								{if $payment_method->module == $payment_module@key}selected{/if}>
								{$payment_module->name|escape}</option>
						{/foreach}
					</select>
				</li>
				<li>
					<label for="currency_id">Валюта</label>
					<select name="currency_id" id="currency_id">
						{foreach $currencies as $currency}
							<option value="{$currency->id}" {if $currency->id==$payment_method->currency_id}selected{/if}>
								{$currency->name|escape}</option>
						{/foreach}
					</select>
				</li>
			</ul>
		</div>

		{foreach $payment_modules as $payment_module}
			<div class="block_flex layer module_settings"
				{if $payment_module@key!=$payment_method->module}style="display:none;" {/if} module="{$payment_module@key}">

				<h2>{$payment_module->name}</h2>

				{* Параметры модуля оплаты *}
				<ul class="property_block">
					{foreach $payment_module->settings as $setting}
						{$variable_name = $setting->variable}
						{if count((array)$setting->options) > 1}
							<li>
								<label class="property_name"
									for="{$payment_module@key}-{$setting->variable}">{$setting->name}</label>
								<select name="payment_settings[{$setting->variable}]"
									id="{$payment_module@key}-{$setting->variable}">
									{foreach $setting->options as $option}
										<option value='{$option->value}'
											{if $option->value == $payment_settings->{$setting->variable}}selected{/if}>
											{$option->name|escape}</option>
									{/foreach}
								</select>
							</li>
						{elseif count((array)$setting->options) == 1}
							{$option = $setting->options|@first}
							<li>
								<label class="property_name"
									for="{$payment_module@key}-{$setting->variable}">{$setting->name|escape}</label>
								<input name="payment_settings[{$setting->variable}]" type="checkbox" value="{$option->value|escape}"
									{if $option->value == $payment_settings->{$setting->variable}}checked{/if}
									id="{$payment_module@key}-{$setting->variable}" />
							</li>


						{elseif !empty($setting->type) and $setting->type == "file"}
							{* File upload *}
							<li>
								<label class="property_name"
									for="{$payment_module@key}-{$setting->variable}">{$setting->name|escape}</label>
								<input name="{$setting->variable}" type="file" id="{$payment_module@key}-{$setting->variable}" />
							</li>

							{if $payment_settings->{$setting->variable}}
								<li>
									<img
										src="{$config->root_url}/{$payment_settings->{$setting->variable}}?{math equation='rand(10,10000)'}" />
								</li>
							{/if}
						{else}
							<li>
								<label class="property_name"
									for="{$payment_module@key}-{$setting->variable}">{$setting->name|escape}</label>
								<input name="payment_settings[{$setting->variable}]" type="text"
									value="{$payment_settings->{$setting->variable}|escape}"
									id="{$payment_module@key}-{$setting->variable}" />
							</li>
						{/if}
					{/foreach}
				</ul>
				{* END Параметры модуля оплаты *}

			</div>
		{/foreach}

		<div class="block_flex layer module_settings" {if $payment_method->module != ''}style="display:none;" {/if}
			module=""></div>

		<div class="block_flex layer">
			<h2>Дополнительные настройки</h2>
			<ul class="property_block">
				<li>
					<label for="finance_purse_id" class="property_name">Связанный кошелек</label>
					<select name="finance_purse_id" id="finance_purse_id">
						<option value="0">---</option>
						{foreach $purses as $purse}
							<option class="{if !$purse->enabled}disabled{/if}"
								{if $purse->id == $payment_method->finance_purse_id} selected {/if} value="{$purse->id}">
								{$purse->name} ({$purse->currency_sign})</option>
						{/foreach}
					</select>
				</li>
				<li>
					<label for="comment" class="property_name">Заметки</label>
					<textarea name="comment" id="comment">{$payment_method->comment|escape}</textarea>
				</li>
			</ul>
		</div>

		<div class="block_flex layer">
			<h2>Возможные способы доставки</h2>
			<div>
				{foreach $deliveries as $delivery}
					{if $delivery->enabled}
						<div class="checkbox_item {if !$delivery->enabled}disabled{/if}">
							<input type="checkbox" name="payment_deliveries[]" id="delivery_{$delivery->id}"
								value="{$delivery->id}" {if in_array($delivery->id, $payment_deliveries)}checked{/if}>
							<label for="delivery_{$delivery->id}">{$delivery->name}</label>
						</div>
					{/if}
				{/foreach}
			</div>
		</div>

		<div class="block_flex w100 layer">
			<h2>Описание для клиента</h2>
			<textarea name="description" class="editor_small">{$payment_method->description|escape}</textarea>
		</div>

		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="" value="Сохранить" />
		</div>

	</div>

</form>

{include file='parts/tinymce_init.tpl'}

<script>
	{literal}
		$(function() {
			$('div.module_settings').filter(':hidden').find("input, select, textarea").prop("disabled", true);

			$('select[name=module]').change(function() {
				$('div.module_settings').hide().find("input, select, textarea").prop("disabled", true);
				$("div.module_settings[module='" + $(this).val() + "']").show().find(
					"input, select, textarea").prop("disabled", false);

			});
		});
	{/literal}
</script>