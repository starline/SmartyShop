{capture name=tabs}
	{if $user|user_access:products_view}
		<li class="mini {if in_array($view, array('ProductsAdmin','ProductAdmin', 'ProductPriceAdmin'))}active{/if}">
			<a href="{url view=ProductsAdmin clear=true}">Товары</a>
		</li>
	{/if}

	{if $user|user_access:warehouse}
		<li class="mini {if in_array($view, array('WarehouseMovementAdmin','WarehouseMovementsAdmin'))}active{/if}">
			<a href="{url view=WarehouseMovementsAdmin clear=true}">Поставки</a>
		</li>
	{/if}

	<!-- Левая часть -->
	{if $user|user_access:products_brands}
		<li class="mini right {if $view=='BrandsAdmin' || $view=='BrandAdmin'}active{/if}">
			<a href="{url view=BrandsAdmin clear=true}">Настройки</a>
		</li>
	{/if}
{/capture}