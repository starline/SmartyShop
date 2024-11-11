{include file='products/products_settings_menu_part.tpl'}

{$meta_title='Характеристики товаров' scope=global}

<div class="two_columns_list">

	<!-- Заголовок -->
	<div id=header class=header_top>
		<h1>{$meta_title}</h1>
		<a class="add" href="{url view=FeatureAdmin clear=true}">Добавить
			свойство</a>
	</div>

	<!-- Меню -->
	<div id="right_menu">

		<!-- Категории товаров -->
		{include file='parts/categories_tree_part.tpl'}
	</div>


	<div id="main_list" class="features">
		{if $features}
			<form id="list_form" method="post">
				<input type="hidden" name="session_id" value="{$smarty.session.id}">

				<div class="list">
					{foreach $features as $feature}
						<div class="{if !$feature->in_filter}in_filter_off{/if} row" item_id="{$feature->id}">
							<input type="hidden" name="positions[{$feature->id}]" value="{$feature->position}">
							<div class="move">
								<div class="move_zone"></div>
							</div>
							<div class="checkbox">
								<input type="checkbox" name="check[]" value="{$feature->id}" />
							</div>
							<div class="name">
								<a href="{url view=FeatureAdmin id=$feature->id clear=true}">{$feature->name|escape}</a>
							</div>
							<div class="icons">
								<a title="Использовать в фильтре" class="in_filter" href='#'></a>
								<a title="Удалить" class="delete" href='#'></a>
							</div>
						</div>
					{/foreach}
				</div>

				<div id="action">
					<span id="check_all" class="dash_link">Выбрать все</span>

					<span id="select">
						<select name="action">
							<option value="">Выбрать действие</option>
							<option value="set_in_filter">Использовать в фильтре</option>
							<option value="unset_in_filter">Не использовать в фильтре</option>
							<option value="delete">Удалить</option>
						</select>
					</span>

					<input id="apply_action" class="button_green" type="submit" value="Применить">
				</div>
			</form>
		{else}
			Нет свойств
		{/if}
	</div>

</div>

<script>
	let session = '{$smarty.session.id}';

	{literal}
		$(function() {

			// Сортировка списка
			$(".list").sortable({
				items: ".row",
				handle: ".move_zone",
				tolerance: "pointer",
				opacity: 0.90,
				axis: 'y',
				update: function(event, ui) {
					$("#list_form input[name*='check']").prop('checked', false);
					$("#list_form").ajaxSubmit(function() {
						colorize();
					});
				}
			});


			// Указать "в фильтре"/"не в фильтре"
			$("a.in_filter").click(function() {
				ajax_icon($(this), 'feature', 'in_filter', session);
				return false;
			});

		});
	{/literal}
</script>