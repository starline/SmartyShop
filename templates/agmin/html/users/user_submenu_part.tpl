{capture name=subtabs}
	{if $current_user->manager}
		<ul id="submenu" class="submenu">
			{if $user|user_access:users_settings and $current_user->id}
				<li {if $view == 'UserAdmin'}class="active" {/if}>
					<a href="{url view=UserAdmin id=$current_user->id clear=true}">Информация</a>
				</li>
			{/if}

			{if $user|user_access:users_settings and $current_user->id}
				<li {if $view == 'UserSettingsAdmin'}class="active" {/if}>
					<a href="{url view=UserSettingsAdmin id=$current_user->id clear=true}">Настройки сотрудника</a>
				</li>
			{/if}

			{if $smarty.get.return}
				<li class="back">
					<a class="out_link" href="{$smarty.get.return}">Назад</a>
				</li>
			{/if}

		</ul>
	{/if}
{/capture}