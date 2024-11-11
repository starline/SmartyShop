{capture name=tabs}
	{if $user|user_access:products_view}
		<li class="mini {if in_array($view, array('ProductsAdmin','ProductAdmin', 'ProductPriceAdmin'))}active{/if}">
			<a href="{url view=ProductsAdmin clear=true}">Товары</a>
		</li>
	{/if}

	<!-- Левая часть -->
	{if $user|user_access:products_merchants}
		<li class="mini right {if $view == 'MerchantsAdmin' || $view == 'MerchantAdmin'}active{/if}">
			<a href="{url view=MerchantsAdmin clear=true}">Прайсы</a>
		</li>
	{/if}

	{if $user|user_access:products_categories}
		<li class="mini right {if in_array($view, array('CategoriesAdmin','CategoryAdmin'))}active{/if}">
			<a href="{url view=CategoriesAdmin clear=true}">Категории</a>
		</li>
	{/if}

	{if $user|user_access:products_features}
		<li class="mini right {if $view=='FeaturesAdmin' || $view=='FeatureAdmin'}active{/if}">
			<a href="{url view=FeaturesAdmin clear=true}">Характеристики</a>
		</li>
	{/if}

	{if $user|user_access:products_brands}
		<li class="mini right {if $view=='BrandsAdmin' || $view=='BrandAdmin'}active{/if}">
			<a href="{url view=BrandsAdmin clear=true}">Бренды</a>
		</li>
	{/if}
{/capture}