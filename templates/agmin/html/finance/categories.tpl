{include file='finance/finance_menu_part.tpl'}

{$meta_title='Категории платежей' scope=global}

<div id=header class=header_top>
	{if $categories_count>0}
		<h1>{$categories_count} {$categories_count|plural:'категория':'категорий':'категории'} платежей</h1>
	{else}
		<h1>Нет категорий платежей</h1>
	{/if}

	<a class="add" href="{url view=FinanceCategoryAdmin}">Добавить категорию платежей</a>
</div>

{if $categories}
	<div id="main_list" class="finance">
		<form id="list_form" method="post">
			<input type="hidden" name="session_id" value="{$smarty.session.id}">

			<div class="list">

				{foreach $categories as $c}
					<div class="row" item_id="{$c->id}">

						<input type="hidden" name="positions[{$c->id}]" value="{$c->position}">
						<div class="move">
							<div class="move_zone"></div>
						</div>

						<div class="checkbox">
							<input type="checkbox" name="check[]" value="{$c->id}" />
						</div>

						<div class="name">
							<a href="{url view=FinanceCategoryAdmin id=$c->id}">{$c->name}</a>
							<div class="notice">{$c->comment}</div>
						</div>
						<div class="user_phone">
							{if $c->type == 1}
								Приход
							{else}
								Расход
							{/if}
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

	</div>
{/if}



<script>
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

		});
	{/literal}
</script>