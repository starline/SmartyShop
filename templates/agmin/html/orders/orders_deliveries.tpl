{include file='orders/orders_settings_menu_part.tpl'}

{$meta_title='Способы доставки' scope=global}

{if $message_error}
	<!-- Системное сообщение -->
	<div class="message message_error">
		<span class="text">{if $message_error == 'order'}Невозможно удалить способ доставки связанный с заказом{/if}</span>
	</div>
{/if}

<!-- Заголовок -->
<div id="header" class="header_top">
	<h1>{$meta_title}</h1>
	<a class="add" href="{url view=OrdersDeliveryAdmin clear=true}">Добавить способ доставки</a>
</div>

<div id="main_list">
	{if $deliveries}
		<form id="list_form" method="post">

			<input type="hidden" name="session_id" value="{$smarty.session.id}" />

			<div id="deliveries" class="list">
				{foreach $deliveries as $delivery}
					<div class="row {if !$delivery->enabled}enabled_off{/if}" item_id="{$delivery->id}">
						<input type="hidden" name="positions[{$delivery->id}]" value="{$delivery->position}" />
						<div class="move">
							<div class="move_zone"></div>
						</div>
						<div class="checkbox">
							<input type="checkbox" name="check[]" value="{$delivery->id}" />
						</div>
						<div class="name">
							<a href="{url view=OrdersDeliveryAdmin id=$delivery->id}">{$delivery->name|escape}</a>
						</div>
						<div class="icons">
							<a class="enable" title="Активен" href="#"></a>
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
						<option value="enable">Включить</option>
						<option value="disable">Выключить</option>
						<option value="delete">Удалить</option>
					</select>
				</span>
				<input id="apply_action" class="button_green" type="submit" value="Применить" />
			</div>
		</form>
	{else}
		Нет способов доставки
	{/if}
</div>

<script>
	const session = '{$smarty.session.id}';

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
			$("a.enable").click(function() {
				ajax_icon($(this), 'delivery', 'enabled', session);
				return false;
			});

		});
	{/literal}
</script>