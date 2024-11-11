{include file='finance/finance_menu_part.tpl'}

{if $category->id}
	{$meta_title = "Категория платежей" scope=global}
{else}
	{$meta_title = 'Новая категория  платежей' scope=global}
{/if}

{if $message_success}
	<div class="message message_success">
		<span class="text">{if $message_success=='updated'}Кошелек обновлен
			{elseif $message_success=='added'}Кошелек
			добавлен{else}{$message_success}
			{/if}</span>
	</div>
{/if}


<form method=post class=form_css enctype="multipart/form-data">
	<input type=hidden name="session_id" value="{$smarty.session.id}">

	<div class=name_row>
		<input class="name" name="name" type="text" value="{$category->name|escape}" />
		<input name="id" type="hidden" value="{$category->id|escape}" />
	</div>

	<div class=columns>
		<div class="block_flex">
			<ul class="property_block">
				<li>
					<label class="property_name">Вид платежа</label>
					<select name="type">
						<option value='0' {if $category->type == 0}selected{/if}>Расход</option>
						<option value='1' {if $category->type == 1}selected{/if}>Приход</option>
					</select>
				</li>
				<li>
					<label class="property_name">Заметки</label>
					<textarea name="comment">{$category->comment|escape}</textarea>
				</li>
			</ul>
		</div>
		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="category" value="Сохранить">
		</div>
	</div>

</form>