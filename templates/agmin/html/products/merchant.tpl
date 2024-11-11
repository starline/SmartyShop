{include file='products/products_settings_menu_part.tpl'}

{if $merchant->id}
	{$meta_title = "Прайс" scope=global}
{else}
	{$meta_title = 'Новый прайс' scope=global}
{/if}

{if $message_success}
	<div class="message message_success">
		<span class="text">
			{if $message_success=='updated'}Прайс обновлен
			{elseif $message_success=='added'}Прайс добавлен
			{else}{$message_success}
			{/if}
		</span>
	</div>
{/if}

<form method="post" class="form_css" enctype="multipart/form-data">
	<input type=hidden name="session_id" value="{$smarty.session.id}" />

	<div class="name_row whith_id">
		<span class="item_id">#{$merchant->id|escape}</span>
		<input class="name" name="name" type="text" value="{$merchant->name|escape}" />

		<input name="id" type="hidden" value="{$merchant->id|escape}" />
	</div>

	<div class="btn_row">
		<input class="button_green" type="submit" value="Сохранить" />
	</div>
</form>