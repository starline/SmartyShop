{include file='finance/finance_menu_part.tpl'}

{$meta_title = 'Валюты' scope=global}

{if $message_success}
	<div class="message message_success">
		<span class="text">Валюты обновлены</span>
	</div>
{/if}


<!-- Заголовок -->
<div id=header class=header_top>
	<h1>Валюты</h1>
	<a class="add" id="add_currency" href="#">Добавить</a>
</div>


<form method=post>
	<input type="hidden" name="session_id" value="{$smarty.session.id}">

	<!-- Валюты -->
	<div id="currencies_block">
		<div id="currencies">
			<ul>
				<li class="move"></li>
				<li class="name">Название валюты</li>
				<li class="icons"></li>
				<li class="sign">Знак</li>
				<li class="iso">Код ISO</li>
			</ul>

			{foreach $currencies as $c}
				<ul class="sortable {if !$c->enabled}enabled_off{/if} {if $c->cents == 0}cents_off{/if} row">
					<input type="hidden" name="check[]" value="{$c->id}" />
					<li class="move">
						<div class="move_zone"></div>
					</li>
					<li class="name">
						<input name="currency[id][{$c->id}]" type="hidden" value="{$c->id|escape}" />
						<input name="currency[name][{$c->id}]" type=text value="{$c->name|escape}" />
					</li>
					<li class="icons">
						<a class="cents" href="#" title="Выводить копейки"></a>
						<a class="enable" href="#" title="Показывать на сайте"></a>
					</li>
					<li class="sign">
						<input name="currency[sign][{$c->id}]" type="text" value="{$c->sign|escape}" />
					</li>
					<li class="iso">
						<input name="currency[code][{$c->id}]" type="text" value="{$c->code|escape}" />
					</li>
					<li class="rate">
						{if !$c@first}
							<div class=rate_from><input name="currency[rate_from][{$c->id}]" type="text"
									value="{$c->rate_from|escape}" /> {$c->sign}</div>
							<div class=rate_to>= <input name="currency[rate_to][{$c->id}]" type="text"
									value="{$c->rate_to|escape}" /> {$currency->sign}</div>
						{else}
							<input name="currency[rate_from][{$c->id}]" type="hidden" value="{$c->rate_from|escape}" />
							<input name="currency[rate_to][{$c->id}]" type="hidden" value="{$c->rate_to|escape}" />
						{/if}
					</li>
					<li class="icons">
						{if !$c@first}
							<a class="delete" href="#" title="Удалить"></a>
						{/if}
					</li>
				</ul>
			{/foreach}

			<ul id="new_currency" style='display:none;'>
				<li class="move">
					<div class="move_zone"></div>
				</li>
				<li class="name">
					<input name="currency[id][]" type="hidden" value="" />
					<input name="currency[name][]" type=text value="" />
				</li>
				<li class="icons"></li>
				<li class="sign">
					<input name="currency[sign][]" type=text value="" />
				</li>
				<li class="iso">
					<input name="currency[code][]" type=text value="" />
				</li>
				<li class="rate">
					<div class=rate_from>
						<input name="currency[rate_from][]" type=text value="1" />
					</div>
					<div class=rate_to>= <input name="currency[rate_to][]" type=text value="1" />
						{$currency->sign|escape}
					</div>
				</li>
				<li class="icons"></li>
			</ul>
		</div>

	</div>
	<!-- Валюты (The End)-->


	<div id="action">
		<input type=hidden name="recalculate" value='0' />
		<input type=hidden name="action" value='' />
		<input type=hidden name="action_id" value='' />
		<input id='apply_action' class="button_green" type=submit value="Применить" />
	</div>
</form>


<script>
	const session = '{$smarty.session.id}';

	{literal}
		$(function() {

			// Сортировка списка
			// Сортировка вариантов
			$("#currencies_block").sortable({
				items: 'ul.sortable',
				handle: '.move_zone',
				cancel: '#header',
				tolerance: "pointer",
				opacity: 0.90,
				axis: 'y'
			});

			// Добавление валюты
			var curr = $('#new_currency').clone(true);
			$('#new_currency').remove().removeAttr('id');
			$('a#add_currency').click(function() {
				$(curr).clone(true).appendTo('#currencies').fadeIn('slow').find(
					"input[name*=currency][name*=name]").focus();
				return false;
			});

			// Скрыт/Видим
			$("a.enable").click(function() {
				ajax_icon($(this), 'currency', 'enabled', session);
				return false;
			});

			// Центы
			$("a.cents").click(function() {
				ajax_icon($(this), 'currency', 'cents', session);
				return false;
			});

			//  Удалить валюту
			$("a.delete").click(function() {
				$('input[type="hidden"][name="action"]').val('delete');
				$('input[type="hidden"][name="action_id"]').val($(this).closest("ul").find(
					'input[type="hidden"][name*="currency[id]"]').val());
				$(this).closest("form").submit();
			});

			// Запоминаем id первой валюты, чтобы определить изменение базовой валюты
			var base_currency_id = $('input[name*="currency[id]"]').val();

			$("form").submit(function() {
				if ($('input[type="hidden"][name="action"]').val() == 'delete' && !confirm(
						'Подтвердите удаление'))
					return false;
				if (base_currency_id != $('input[name*="currency[id]"]:first').val() && confirm(
						'Пересчитать все цены в ' + $('input[name*="name"]:first').val() +
						' по текущему курсу?', 'msgBox Title'))
					$('input[name="recalculate"]').val(1);
			});
		});
	{/literal}
</script>