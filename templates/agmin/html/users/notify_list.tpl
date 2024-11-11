{include file='users/users_menu_part.tpl'}

{$meta_title='Оповещения пользователей' scope=global}

<div id=header class=header_top>
	<h1>Оповещения пользователей</h1>
	<a class="add" href="{url view=NotifyAdmin clear=true}">Добавить способ оповещения</a>
</div>


<div id="main_list">
	{if !$notify_list|empty}
		<form id="list_form" method="post">
			<input type="hidden" name="session_id" value="{$smarty.session.id}">
			<div class="list">

				{foreach $notify_list as $notify}
					<div class="row" item_id="{$group->id}">

						<div class="move">
							<input type="hidden" name="positions[{$notify->id}]" value="{$notify->position}">
							<div class="move_zone"></div>
						</div>

						<div class="checkbox">
							<input type="checkbox" name="check[]" value="{$notify->id}"
								{if !$user|user_access:users_groups_delete}disabled{/if} />
						</div>

						<div class=name>
							<a href="{url view=NotifyAdmin id=$notify->id clear=true}">{$notify->name}</a>
						</div>


						<div class="icons">
							<a class="delete" title="Удалить" href="#"></a>
						</div>

					</div>
				{/foreach}
			</div>

			<div id="action">
				<span id="check_all" class="dash_link">Выбрать все</span>

				<span id=select>
					<select name="action">
						<option value="">Выбрать действие</option>
						<option value="delete">Удалить</option>
					</select>
				</span>

				<input id="apply_action" class="button_green" type="submit" value="Применить">
			</div>

		</form>
	{/if}
</div>



<script>
	{literal}
		$(function() {

			// Сортировка списка
			$(".list").sortable({
				items: ".row",
				handle: ".move_zone",
				tolerance: "pointer",
				opacity: 0.95,
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