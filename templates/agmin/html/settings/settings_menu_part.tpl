{capture name=tabs}

	{if $user|user_access:settings}
		<li class="mini {if $view=='SettingsAdmin'}active{/if}">
			<a href="{url view=SettingsAdmin clear=true}">Настройки</a>
		</li>
	{/if}

	{if $user|user_access:backup}
		<li class="mini {if $view=='BackupAdmin'}active{/if}">
			<a href="{url view=BackupAdmin clear=true}">Бекап</a>
		</li>
	{/if}

	<li class="mini {if $view == 'ScriptsAdmin'}active{/if}">
		<a href="{url view=ScriptsAdmin clear=true}">Скрипты</a>
	</li>

	{if $user|user_access:design}
		<li class="mini right {if $view == 'ThemeAdmin'}active{/if}">
			<a href="{url view=ThemeAdmin clear=true}">Тема</a>
		</li>
	{/if}

{/capture}