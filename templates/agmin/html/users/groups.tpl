{include file='users/users_menu_part.tpl'}

{$meta_title='Группы пользователей' scope=global}

<div id=header class=header_top>
	<h1>Группы пользователей</h1>
	<a class="add" href="{url view=GroupAdmin clear=true}">Добавить группу</a>
</div>


<div id="main_list">
	<form id="list_form" method="post">
		<input type="hidden" name="session_id" value="{$smarty.session.id}">
		<div id="groups" class="list group_list">

			{foreach $groups as $group}
				<div class="row" item_id="{$group->id}">

					{if $user|user_access:users_groups_edit}
						<div class="move">
							<input type="hidden" name="positions[{$group->id}]" value="{$group->position}">
							<div class="move_zone"></div>
						</div>
					{/if}

					<div class="checkbox">
						<input type="checkbox" name="check[]" value="{$group->id}"
							{if !$user|user_access:users_groups_delete}disabled{/if} />
					</div>

					<div class=name>
						<a href="{url view=GroupAdmin id=$group->id clear=true}">{$group->name}</a>
					</div>

					<div class="group_discount">
						{$group->discount} %
					</div>

					{if $user|user_access:users_groups_delete}
						<div class="icons">
							<a class="delete" title="Удалить" href="#"></a>
						</div>
					{/if}

				</div>
			{/foreach}
		</div>

		{if $user|user_access:users_groups_delete}
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
		{/if}

	</form>
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