{capture name=tabs}
	<li class="mini {if $view == 'UsersAdmin' || $view == 'UserAdmin'} active{/if}">
		<a href="{url view=UsersAdmin clear=true}">Покупатели</a>
	</li>


	{if $user|user_access:users_notify}
		<li class="right mini {if $view == 'NotifyAdmin' || $view == 'NotifyListAdmin'}active{/if}">
			<a href="{url view=NotifyListAdmin clear=true}">Оповещения</a>
		</li>
	{/if}

	{if $user|user_access:users_coupons}
		<li class="right mini {if $view == 'CouponsAdmin' || $view == 'CouponAdmin'}active{/if}">
			<a href="{url view=CouponsAdmin clear=true}">Купоны</a>
		</li>
	{/if}

	{if $user|user_access:users_groups}
		<li class="right mini {if $view == 'GroupsAdmin' || $view == 'GroupAdmin'}active{/if}">
			<a href="{url view=GroupsAdmin clear=true}">Группы</a>
		</li>
	{/if}
{/capture}