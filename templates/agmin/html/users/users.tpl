{include file='users/users_menu_part.tpl'}

{$meta_title='Покупатели' scope=global}

<div class="two_columns_list">

	<div id="header">
		<div class="header_top">
			{if $keyword && $users_count>0}
				<h1>{$users_count|plural:'Нашелся':'Нашлось':'Нашлись'} {$users_count}
					{$users_count|plural:'покупатель':'покупателей':'покупателя'}</h1>
			{elseif $users_count>0}
				<h1>{$users_count} {$users_count|plural:'покупатель':'покупателей':'покупателя'}</h1>
			{else}
				<h1>Нет покупателей</h1>
			{/if}

			{if $users_count>0 and $user|user_access:export}
				<form class="export_btn" method="post" action="{url view=ExportEntityAdmin entity=users}" target="_blank">
					<input type="hidden" name="session_id" value="{$smarty.session.id}" />
					<input type="image" src="/{$config->templates_subdir}images/export_excel.png" name="export"
						title="Экспортировать этих покупателей" />
				</form>
			{/if}


			<div id="search">
				<form method="get">
					<input type="hidden" name="view" value='UsersAdmin' />
					<input class="search" type="text" name="keyword" value="{$keyword|escape}"
						placeholder="Имя, фамилия, телефон, email" />
					<input class="search_button" type="submit" value="" />
				</form>
			</div>
		</div>
	</div>


	<div id="right_menu">
		<div class="popup_menu_btn">
			<span class="popup_btn_sign">
				<li></li>
				<li></li>
				<li></li>
			</span>
			<span class="popup_btn_text">Фильтр</span>
		</div>
		<div id="popup_menu_block">
			{if $groups}
				<ul class="menu_list">
					<li class="{if !$group->id AND !$manager}selected{/if}">
						<a href="{url view=UsersAdmin clear=true}">Показать всех</a>
					</li>
					{foreach $groups as $g}
						<li class="{if $group->id == $g->id}selected{/if}">
							<a href="{url view=UsersAdmin group_id=$g->id clear=true}">{$g->name}</a>
						</li>
					{/foreach}
				</ul>
			{/if}

			{if $user|user_access:users_manager}
				<ul class="menu_list layer">
					<li class="{if $manager==1}selected{/if}">
						<a href="{url view=UsersAdmin manager=1 clear=true}">Сотрудники</a>
					</li>
				</ul>
			{/if}
		</div>
	</div>



	<div id="main_list">
		{if $users}

			<div class="list_top_row">
				{if !$pagination_hide}
					{include file='parts/pagination.tpl'}
				{elseif ($page_limit<$users_count)}
					<div id="pagination">Показано только первые {$page_limit} покупателей</div>
				{/if}

				<div id="sort_links">
					Упорядочить по
					{if $sort!='name'}<a href="{url sort=name}">имени</a>{else}<b>Имени</b>{/if} или
					{if $sort!='date'}<a href="{url sort=date}">дате</a>{else}<b>Дате</b>{/if}
				</div>
			</div>

			<form id="form_list" method="post">
				<input type="hidden" name="session_id" value="{$smarty.session.id}">

				<div id="users" class="list">
					{foreach $users as $u}
						<div class="{if !$u->enabled}enabled_off{/if} row" item_id="{$u->id}">

							<div class="checkbox">
								<input type="checkbox" name="check[]" value="{$u->id}"
									{if !$user|user_access:users_edit}disabled{/if} />
							</div>

							<div class="user_name">
								<a
									href="{url view=UserAdmin id=$u->id clear=true}">{if $u->name}{$u->name|escape}{else}-{/if}</a>
							</div>
							<div class="user_phone">
								{$u->phone|escape|replace:',':' '}
							</div>
							<div class="user_email">
								<a href="mailto:{$u->name|escape}<{$u->email|escape}>">{$u->email|escape}</a>
							</div>
							<div class="user_group">
								{$groups[$u->group_id]->name}
							</div>

							<div class="icons">
								<a class="enable {if $user|user_access:users_edit}edit{/if}"
									title="{if $u->enabled}Активен{else}Заблокирован{/if}"></a>

								{if $user|user_access:users_delete}
									<a class="delete" title="Удалить" href="#"></a>
								{/if}
							</div>

						</div>
					{/foreach}
				</div>

				{if $user|user_access:users_edit}
					<div id="action">
						<span id="check_all" class="dash_link">Выбрать все</span>
						<span id=select>
							<select name="action">
								<option value="">Выбрать действие</option>
								<option value="disable">Заблокировать</option>
								<option value="enable">Разблокировать</option>
								{if $user|user_access:users_delete}
									<option value="delete">Удалить</option>
								{/if}
							</select>
						</span>
						<input id="apply_action" class="button_green" type="submit" value="Применить" />
					</div>
				{/if}

			</form>

			{if !$pagination_hide}
				{include file='parts/pagination.tpl'}
			{/if}

		{/if}
	</div>

</div>


<script>
	const session = '{$smarty.session.id}';

	{literal}
		$(function() {

			// Скрыт/Видим
			$("a.enable.edit").click(function() {
				ajax_icon($(this), 'user', 'enabled', session);
				return false;
			});
		});
	{/literal}
</script>