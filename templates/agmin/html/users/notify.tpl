{include file='users/users_menu_part.tpl'}

{if $notify->id}
	{$meta_title = $notify->name scope=global}
{else}
	{$meta_title = 'Новый способ оповещения' scope=global}
{/if}


{if $message_success}
	<!-- Системное сообщение -->
	<div class="message message_success">
		<span class="text">{if $message_success == 'added'}Способ оповещения
			добавлен{elseif $message_success == 'updated'}Способ оповещения изменен
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
						<input name="enabled" value="1" type="checkbox" id="enabled"
							{if $notify->enabled}checked{/if} />
						<label for="enabled">Активный</label>
					</div>
				</div>
			</div>

			<div class="name_row">
				<input name="id" type="hidden" value="{$notify->id}" />
				<input class="name" name="name" type="text" value="{$notify->name|escape}" autocomplete="off" />
			</div>
		</div>


		<div class="block_flex layer">
			<h2>Настройки оповещения</h2>
			<ul class="property_block">

				<li>
					<label for="module">Модуль оповещения</label>
					<select name="module" id="module">
						<option value="">Не установлен</option>
						{foreach $notify_modules as $notify_module}
							<option value="{$notify_module@key|escape}" {if $notify->module == $notify_module@key}selected{/if}>
								{$notify_module->name|escape}</option>
						{/foreach}
					</select>
				</li>
			</ul>
		</div>

		{foreach $notify_modules as $notify_module}
			<div class="block_flex layer module_settings" {if $notify_module@key != $notify->module}style="display:none;"
				{/if} module="{$notify_module@key}">

				<h2>{$notify_module->name}</h2>

				{* Параметры модуля оповещения *}
				<ul class="property_block">
					{foreach $notify_module->settings as $setting}
						{$variable_name = $setting->variable}
						{if count((array)$setting->options) > 1}
							<li>
								<label class="property_name"
									for="{$notify_module@key}-{$setting->variable}">{$setting->name}</label>
								<select name="notify_settings[{$setting->variable}]" id="{$notify_module@key}-{$setting->variable}">
									{foreach $setting->options as $option}
										<option value='{$option->value}'
											{if $option->value == $notify_settings->{$setting->variable}}selected{/if}>
											{$option->name|escape}</option>
									{/foreach}
								</select>
							</li>
						{elseif count((array)$setting->options) == 1}
							{$option = $setting->options|@first}
							<li>
								<label class="property_name"
									for="{$notify_module@key}-{$setting->variable}">{$setting->name|escape}</label>
								<input name="notify_settings[{$setting->variable}]" type="checkbox" value="{$option->value|escape}"
									{if $option->value == $notify_settings->{$setting->variable}}checked{/if}
									id="{$notify_module@key}-{$setting->variable}" />
							</li>


						{elseif !empty($setting->type) and $setting->type == "file"}
							{* File upload *}
							<li>
								<label class="property_name"
									for="{$notify_module@key}-{$setting->variable}">{$setting->name|escape}</label>
								<input name="{$setting->variable}" type="file" id="{$notify_module@key}-{$setting->variable}" />
							</li>

							{if $notify_settings->{$setting->variable}}
								<li>
									<img
										src="{$config->root_url}/{$notify_settings->{$setting->variable}}?{math equation='rand(10,10000)'}" />
								</li>
							{/if}
						{else}
							<li>
								<label class="property_name"
									for="{$notify_module@key}-{$setting->variable}">{$setting->name|escape}</label>
								<input name="notify_settings[{$setting->variable}]" type="text"
									value="{$notify_settings->{$setting->variable}|escape}"
									id="{$notify_module@key}-{$setting->variable}" />
							</li>
						{/if}
					{/foreach}
				</ul>
				{* END Параметры модуля оплаты *}

			</div>
		{/foreach}

		<div class="block_flex layer module_settings" {if $notify->module != ''}style="display:none;" {/if} module="">
		</div>

		<div class="block_flex layer">
			<h2>Дополнительные настройки</h2>
			<ul class="property_block">
				<li>
					<label for="comment" class="property_name">Заметки</label>
					<textarea name="comment" id="comment">{$notify->comment|escape}</textarea>
				</li>
			</ul>
		</div>

		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="" value="Сохранить" />
		</div>

	</div>

</form>

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