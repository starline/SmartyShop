{capture name=tabs}

	{if $user|user_access:settings}
		<li class=" mini{if $view=='SettingsAdmin'}active{/if}">
			<a href="{url view=SettingsAdmin clear=true}">Настройки</a>
		</li>
	{/if}


	{if $user|user_access:design}
		<li class="mini right {if $view == 'ImagesAdmin'}active{/if}">
			<a href="{url view=ImagesAdmin clear=true}">Изображения</a>
		</li>

		<li class="mini right {if $view == 'StylesAdmin'}active{/if}">
			<a href="{url view=StylesAdmin clear=true}">Стили</a>
		</li>

		<li class="mini right {if $view == 'TemplatesAdmin'}active{/if}">
			<a href="{url view=TemplatesAdmin clear=true}">Шаблоны</a>
		</li>

		<li class="right mini {if $view == 'ThemeAdmin'}active{/if}">
			<a href="{url view=ThemeAdmin clear=true}">Тема</a>
		</li>
	{/if}

{/capture}