{include file='finance/finance_menu_part.tpl'}

{$meta_title='Кошелки' scope=global}

<div class=header_top>
	{if $purses_count>0}
		<h1 class="total_amount">
			{$purses_count} {$purses_count|plural:'кошелек':'кошельков':'кошелька'}

			{foreach $total_amount as $ta}
				<div class="currency_amount">
					<span class="sum_total">{$ta->amount|price_format:2:true} <span
							class="sum_profit_price">{$ta->sign}</span></span>
				</div>
			{/foreach}
		</h1>
	{else}
		<h1>Нет кошельков</h1>
	{/if}

	<a class="add" href="{url view=PurseAdmin}">Добавить кошелек</a>
</div>


<div id="main_list" class="finance">
	{if $purses}

		<form id="list_form" method="post">
			<input type="hidden" name="session_id" value="{$smarty.session.id}">

			<div class="list">
				{foreach $purses as $p}
					<div class="{if !$p->enabled}enabled_off{/if} row" item_id="{$p->id}">

						<div class="move">
							<input type="hidden" name="positions[{$p->id}]" value="{$p->position}">
							<div class="move_zone"></div>
						</div>

						<div class="checkbox">
							<input type="checkbox" name="check[]" value="{$p->id}" />
						</div>

						<div class="user_name">
							<a href="{url view=PurseAdmin id=$p->id}">{$p->name}</a>
							<div class="notice">{$p->comment|escape}</div>
						</div>

						<div class="payment_amount {if $p->amount<0}minus{/if}">
							{$p->amount|price_format:2:true} {$p->currency_sign}
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

				<input id="apply_action" class="button_green" type="submit" value="Применить">

			</div>
		</form>

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
				ajax_icon($(this), 'purse', 'enabled', session);
				return false;
			});

		});
	{/literal}
</script>