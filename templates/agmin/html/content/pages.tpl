{include file='content/content_menu_part.tpl'}

{* Title *}
{$meta_title = {$menu->name} scope=global}

<div id="header" class="header_top">
	<h1>{$menu->name}</h1>
	<a class="add" href="{url view=PageAdmin}">Добавить страницу</a>
</div>

{if $pages}
	<div id="main_list">

		<form id="list_form" method="post">
			<input type="hidden" name="session_id" value="{$smarty.session.id}">
			<div class="list">
				{foreach $pages as $page}
					<div class="{if !$page->visible}visible_off{/if} row">
						<input type="hidden" name="positions[{$page->id}]" value="{$page->position}">
						<div class="move">
							<div class="move_zone"></div>
						</div>
						<div class="checkbox">
							<input type="checkbox" name="check[]" value="{$page->id}" />
						</div>
						<div class="name">
							<a href="{url view=PageAdmin id=$page->id}">{$page->name|escape}</a>
						</div>
						<div class="icons">
							<a class="preview" title="Предпросмотр в новом окне" href="../{$page->url}" target="_blank"></a>
							<a class="enable" title="Активна" href="#"></a>
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
						<option value="enable">Сделать видимыми</option>
						<option value="disable">Сделать невидимыми</option>
						<option value="delete">Удалить</option>
					</select>
				</span>

				<input id="apply_action" class="button_green" type="submit" value="Применить">

			</div>
		</form>
	</div>
{else}
	Нет страниц
{/if}

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

			// Показать
			$("a.enable").click(function() {
				ajax_icon($(this), 'page', 'visible', session);
				return false;
			});

		});
	{/literal}
</script>