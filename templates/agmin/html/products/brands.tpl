{include file='products/products_settings_menu_part.tpl'}

{$meta_title='Бренды' scope=global}

{* Заголовок *}
<div id=header class=header_top>
	<h1>{$meta_title}</h1>
	<a class="add" href="{url view=BrandAdmin}">Добавить бренд</a>
</div>

{if $brands}
	<div id="main_list">

		<form id="list_form" method="post">
			<input type="hidden" name="session_id" value="{$smarty.session.id}">

			<div class="list">
				{foreach $brands as $brand}
					<div class="row" item_id="{$brand->id}">
						<div class="checkbox">
							<input type="checkbox" name="check[]" value="{$brand->id}" />
						</div>
						<div class="name">
							<a href="{url view=BrandAdmin id=$brand->id}">{$brand->name|escape}</a>
						</div>

						{if $brand->image}
							<div class="brand-image">
								<img class="brand-image" src="../{$config->images_brands_dir}{$brand->image}" />
							</div>
						{/if}

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
	</div>
{else}
	Нет брендов
{/if}