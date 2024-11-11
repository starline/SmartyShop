{include file='warehouse/warehouse_menu_part.tpl'}

{$meta_title='Поставщики' scope=global}

{* Заголовок *}
<div id=header class=header_top>
	<h1>Поставщики</h1>
	<a class="add" href="{url view=ProviderAdmin}">Добавить
		поставщика</a>
</div>


<div id="main_list" class="brands">
	{if $providers}
		<form id="list_form" method="post">
			<input type="hidden" name="session_id" value="{$smarty.session.id}">

			<div id="providers" class="list brands">
				{foreach $providers as $provider}
					<div class="row" item_id="{$provider->id}">
						<div class="checkbox">
							<input type="checkbox" name="check[]" value="{$provider->id}" />
						</div>
						<div class="name">
							<a href="{url view=ProviderAdmin id=$provider->id}">{$provider->name|escape}</a>
						</div>
						<div class="icons">
							<a class="delete" title="Удалить" href="#"></a>
						</div>
					</div>
				{/foreach}
			</div>

			<div id="action">
				<span id="check_all" class="dash_link">Выбрать все</span>

				<span id="select">
					<select name="action">
						<option value="">Выбрать действие</option>
						<option value="delete">Удалить</option>
					</select>
				</span>
				<input id="apply_action" class="button_green" type="submit" value="Применить">
			</div>

		</form>
	{else}
		Нет брендов
	{/if}
</div>