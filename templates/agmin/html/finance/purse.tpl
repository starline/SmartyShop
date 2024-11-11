{include file='finance/finance_menu_part.tpl'}

{if $purse->id}
	{$meta_title = "Кошелек" scope=global}
{else}
	{$meta_title = 'Новый кошелек' scope=global}
{/if}

{if $message_success}
	<div class="message message_success">
		<span class="text">{if $message_success=='updated'}Кошелек обновлен
			{elseif $message_success=='added'}Кошелек
			добавлен{else}{$message_success}
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
						<input name="enabled" value='1' type="checkbox" id="active_checkbox"
							{if $purse->enabled}checked{/if} />
						<label for="active_checkbox">Активен</label>
					</div>
				</div>
			</div>
			<div class="name_row">
				<input class="name" name="name" type="text" value="{$purse->name|escape}" />
				<input name="id" type="hidden" value="{$purse->id|escape}" />
			</div>
		</div>

		<div class="block_flex">
			<ul class="property_block">
				<li>
					<label for="amount" class="property_name">Баланс</label>
					<input id="amount" name="amount" type="text" value="{$purse->amount}" disabled />
				</li>

				{if $check_purse_amount != $purse->amount}
					<li>
						<label for="check_purse_amount" class="property_name">Проверочный баланс</label>
						<input id="check_purse_amount" name="check_purse_amount" type="text" value="{$check_purse_amount}"
							disabled />
					</li>
				{/if}

				<li>
					<label for="currency_id" class="property_name">Валюта</label>
					<select id="currency_id" name="currency_id">
						{foreach $currencies as $c}
							<option value="{$c->id}" {if $purse->currency_id == $c->id}selected{/if}>{$c->name|escape}
							</option>
						{/foreach}
					</select>
				</li>

				<li>
					<label class="property_name">Заметки</label>
					<textarea name="comment">{$purse->comment}</textarea>
				</li>
			</ul>
		</div>

		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="user_info" value="Сохранить">
		</div>
	</div>
</form>