{include file='products/products_settings_menu_part.tpl'}

{$meta_title='Категории товаров' scope=global}

{if $message_success}
	<div class="message message_success">
		<span class="text">Категория
			{if $message_success == 'delete'}удалена{elseif  $message_success == 'update'}обновлена{/if}</span>
	</div>
{/if}


{* Заголовок *}
<div id="header" class="header_top">
	<h1>{$meta_title}</h1>
	<a class="add" href="{url view=CategoryAdmin return=$smarty.server.REQUEST_URI|escape}">Добавить
		категорию</a>
</div>


<div id="main_list" class="categories">
	{if $categories}

		<form id="list_form" method="post">
			<input type="hidden" name="session_id" value="{$smarty.session.id}">

			{function name=categories_tree level=0}
				{if $categories}
					<div id="categories" class="list sortable">
						{foreach $categories as $category}
							<div class="tree_row level_{$category->level}">
								<div class="row {if !$category->visible}visible_off{/if}" item_id="{$category->id}">
									<input type="hidden" name="positions[{$category->id}]" value="{$category->position}">
									<div class="move" style="margin-left:{$level*24}px">
										<div class="move_zone"></div>
									</div>
									<div class="checkbox">
										<input type="checkbox" name="check[]" value="{$category->id}" />
									</div>
									<div class="name">
										<a
											href="{url view=CategoryAdmin id=$category->id return=$smarty.server.REQUEST_URI|escape}">{$category->name|escape}</a>
										<div class="icons">
											<a class="external_link" title="Предпросмотр в новом окне" href="../product/{$product->id}"
												target="_blank"></a>
										</div>
									</div>
									{if $category->main}
										<span class="round_box">На главной</span>
									{/if}
									<div class="icons">
										<a class="enable" title="Активна" href="#"></a>
										<a class="delete" title="Удалить" href="#"></a>
									</div>
								</div>

								{categories_tree categories=$category->subcategories level=$level+1}
							</div>
						{/foreach}
					</div>
				{/if}
			{/function}

			{categories_tree categories=$categories}

			<div id="action">
				<span id="check_all" class="dash_link">Выбрать все</span>

				<span id="select">
					<select name="action">
						<option value="">Выбрать действие</option>
						<option value="enable">Сделать видимыми</option>
						<option value="disable">Сделать невидимыми</option>
						<option value="delete">Удалить</option>
					</select>
				</span>

				<input id="apply_action" class="button_green" type="submit" value="Применить">
			</div>

		</form>
	{else}
		Нет категорий
	{/if}
</div>


<script>
	let session = '{$smarty.session.id}';

	{literal}
		$(function() {

			// Раскраска строк
			function colorize() {
				$(".list .level_2").find("div.tree_row:even .row").addClass('even');
				$(".list .level_2").find("div.tree_row:odd .row").removeClass('even');
			}
			colorize();

			// Сортировка списка
			$(".sortable").sortable({
				items: ".tree_row",
				handle: ".move_zone",
				tolerance: "pointer",
				opacity: 0.90,
				axis: "y",
				update: function() {
					$("#list_form input[name*='check']").prop('checked', false);
					$("#list_form").ajaxSubmit(function() {
						colorize();
					});
				}
			});


			// Скрыт/Видим
			$("a.enable").click(function() {
				ajax_icon($(this), 'category', 'visible', session);
				return false;
			});

		});
	{/literal}
</script>