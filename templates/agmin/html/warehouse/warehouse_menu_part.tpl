{capture name=tabs}
	{if $user|user_access:products_view}
		<li class="mini {if $view=='ProductsAdmin' || $view=='ProductAdmin'}active{/if}">
			<a href="{url view=ProductsAdmin clear=true}">Товары</a>
		</li>
	{/if}

	{if $user|user_access:warehouse}
		<li
			class="mini {if ($view=='WarehouseMovementAdmin' || $view=='WarehouseMovementsAdmin')}active{/if} {if !in_array($view, array('WarehouseMovementAdmin','WarehouseMovementsAdmin','ProvidersAdmin','ProviderAdmin'))}mini{/if}">
			<a href="{url view=WarehouseMovementsAdmin clear=true}">Поставки</a>
		</li>
	{/if}

	{if $user|user_access:warehouse_providers}
		<li class="mini right {if $view=='ProvidersAdmin' || $view=='ProviderAdmin'}active{/if}">
			<a href="{url view=ProvidersAdmin clear=true}">Поставщики</a>
		</li>
	{/if}
{/capture}