{include file='warehouse/warehouse_menu_part.tpl'}


{if $brand->id}
	{$meta_title = $provider->name scope=global}
{else}
	{$meta_title = 'Новый провайдер' scope=global}
{/if}

{include file='parts/tinymce_init.tpl'}

{if $message_success}
	<div class="message message_success">
		<span class="text">{if $message_success=='added'}Поставщик добавлен
			{elseif $message_success=='updated'}Поставщик
			обновлен{else}{$message_success}
			{/if}</span>
	</div>
{/if}


<!-- Основная форма -->
<form method=post class=form_css enctype="multipart/form-data">
	<input type=hidden name="session_id" value="{$smarty.session.id}">

	<div class="block_flex w100">
		<div class="over_name">
			<div class="checkbox_line">
				<div class="checkbox_item">
					<input name="no_restore_price" value="1" type="checkbox" id="no_restore_price_checkbox"
						{if $provider->no_restore_price}checked{/if} />
					<label for="no_restore_price_checkbox">Не обнулять склад</label>
				</div>
			</div>
		</div>

		<div class="name_row">
			<input class="name" name=name type="text" value="{$provider->name|escape}" />
			<input name=id type="hidden" value="{$provider->id|escape}" />
		</div>
	</div>

	<div class="block_flex w100 layer">
		<h2>Описание</h2>
		<textarea name="description" class="html_editor editor_large">{$provider->description|escape}</textarea>

		<div class="btn_row">
			<input class="button_green" type="submit" name="" value="Сохранить" />
		</div>
	</div>


</form>