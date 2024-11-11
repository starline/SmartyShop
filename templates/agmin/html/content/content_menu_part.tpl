{capture name=tabs}

	{if $user|user_access:comments}
		<li class="mini {if $view=='CommentsAdmin' || $view=='CommentAdmin'}active{/if}">
			<a href="{url view=CommentsAdmin clear=true}">Комментарии</a>
		</li>
	{/if}

	{if $user|user_access:feedbacks}
		<li class="mini {if $view=='FeedbacksAdmin'}active{/if}">
			<a href="{url view=FeedbacksAdmin clear=true}">Обратная связь</a>
		</li>
	{/if}

	{if $user|user_access:blog}
		<li class="mini {if $view=='BlogAdmin' || $view=='PostAdmin'}active{/if}">
			<a href="{url view=BlogAdmin clear=true}">Блог</a>
		</li>
	{/if}


	{if $user|user_access:pages}
		{foreach $menus as $m}
			<li class="mini right {if !$menu->id|empty and $m->id == $menu->id}active{/if}">
				<a href="{url view=PagesAdmin menu_id=$m->id clear=true}">{$m->name}</a>
			</li>
		{/foreach}
	{/if}

{/capture}