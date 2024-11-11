{include file='orders/orders_settings_menu_part.tpl'}

{$meta_title='Способы оплаты' scope=global}

{if $message_error}
	<!-- Системное сообщение -->
	<div class="message message_error">
		<span class="text">{if $message_error == 'order'}Невозможно удалить способ оплаты связанный с заказом{/if}</span>
	</div>
{/if}

<div id=header class=header_top>
	<h1>{$meta_title}</h1>
	<a class="add" href="{url view=OrdersPaymentMethodAdmin clear=true}">Добавить способ оплаты</a>
</div>

<div id="main_list">
	{if $payment_methods}
		<form id="list_form" method="post">
			<input type="hidden" name="session_id" value="{$smarty.session.id}">

			<div id="payment_methods" class="list">
				{foreach $payment_methods as $payment_method}
					<div class="{if !$payment_method->enabled}enabled_off{/if} {if !$payment_method->enabled_public}enabled_public_off{else}highlight{/if} row"
						item_id="{$payment_method->id}">

						<input type="hidden" name="positions[{$payment_method->id}]" value="{$payment_method->position}" />

						<div class="move">
							<div class="move_zone"></div>
						</div>
						<div class="checkbox">
							<input type="checkbox" name="check[]" value="{$payment_method->id}" />
						</div>
						<div class="name">
							<a href="{url view=OrdersPaymentMethodAdmin id=$payment_method->id}">{$payment_method->name}</a>
							<div class=" notice">{$payment_method->comment|escape}</div>
						</div>
						<div class="icons">
							<a class="cents" title="Показывать клиенту при заказе" href="#"></a>
							<a class="enable" title="Показывать менеджеру" href="#"></a>
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
		Нет способов оплаты
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
			$("a.enable").click(function() {
				ajax_icon($(this), 'payment_method', 'enabled', session);
				return false;
			});

			$("a.cents").click(function() {
				ajax_icon($(this), 'payment_method', 'enabled_public', session);
				return false;
			});
		});
	{/literal}
</script>