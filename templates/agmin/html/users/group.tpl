{include file='users/users_menu_part.tpl'}

{if $group->id}
	{$meta_title = $group->name scope=global}
{else}
	{$meta_title = 'Новая группа' scope=global}
{/if}



{if $message_success}
	<div class="message message_success">
		<span class="text">{if $message_success=='added'}Группа добавлена
			{elseif $message_success=='updated'}Группа
			изменена{else}{$message_success|escape}
			{/if}</span>
	</div>
{/if}


{if $message_error}
	<div class="message message_error">
		<span class="text">{$message_error}</span>
		<a class="button" href="">Вернуться</a>
	</div>
{/if}


<form method=post class=form_css enctype="multipart/form-data">
	<input type=hidden name="session_id" value="{$smarty.session.id}">

	<div class="name_row">
		<input class="name" name=name type="text" value="{$group->name|escape}" />
		<input name=id type="hidden" value="{$group->id|escape}" />
	</div>

	<div class="columns">
		<div class="block_flex">
			<ul class="property_block">
				<li>
					<label class="property_name" for=discount>Скидка</label>
					<div class="whith_unit">
						<input name="discount" id=discount class="small_inp" type="text"
							value="{$group->discount|escape}" />
						<span class="label_unit">%</span>
					</div>
				</li>
			</ul>
		</div>
		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="" value="Сохранить" />
		</div>
	</div>
</form>