{capture name=subtabs}
	<ul id="submenu" class="submenu">

		{if $user|user_access:products_content}
			<li {if $view == 'ProductAdmin'}class="active" {/if}>
				<a href="{url view=ProductAdmin id=$product->id clear=true}">Контент</a>
			</li>
		{/if}

		{if $user|user_access:products_price and $product->id}
			<li {if $view == 'ProductPriceAdmin'}class="active" {/if}>
				<a href="{url view=ProductPriceAdmin id=$product->id clear=true}">Цены</a>
			</li>
		{/if}

		{if $smarty.get.return}
			<li class="back">
				<a class="out_link" href="{$smarty.get.return}">Назад</a>
			</li>
		{/if}

	</ul>
{/capture}