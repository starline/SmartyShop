{include file='orders/orders_settings_menu_part.tpl'}

{$meta_title='Метки заказов' scope=global}

{* Заголовок *}
<div class=header_top>
	<h1>{$meta_title}</h1>
	<a class="add" href="{url view=OrdersLabelAdmin}">Новая метка</a>
</div>


<div id="main_list">
	{if $labels}
		<form id="list_form" method="post">

			<input type="hidden" name="session_id" value="{$smarty.session.id}">

			<div id="labels" class="list">
				{foreach $labels as $label}
					<div class="row {if !$label->enabled}enabled_off{/if} {if !$label->in_filter}in_filter_off{/if}"
						item_id="{$label->id}">

						<input type="hidden" name="positions[{$label->id}]" value="{$label->position}" />

						<div class="move">
							<div class="move_zone"></div>
						</div>

						<div class="checkbox">
							<input type="checkbox" name="check[]" value="{$label->id}" />
						</div>

						<div class="name">
							<span style="background-color:#{$label->color};" class="order_label"></span>
							<a href="{url view=OrdersLabelAdmin id=$label->id}">{$label->name|escape}</a>
						</div>

						<div class="icons">
							<a class="in_filter" title="Использовать в фильтре" href='#'></a>
							<a class="enable edit" title="Активен" href='#'></a>
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
		Нет меток
	{/if}
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

			// Скрыт/Видим
			$("a.enable.edit").click(function() {
				ajax_icon($(this), 'label', 'enabled', session);
				return false;
			});

			// Указать "в фильтре"/"не в фильтре"
			$("a.in_filter").click(function() {
				ajax_icon($(this), 'label', 'in_filter', session);
				return false;
			});

		});
	{/literal}
</script>