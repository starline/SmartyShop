<!-- Товар-->
{if $product->disable}
	{$is_instock = false}
{else}
	{foreach $product->variants as $v}
		{if $v->stock > 0}
			{$is_instock = true}
			{break}
		{/if}

		{if $v->custom == 1}
			{$is_custom = true}
		{/if}

		{if $v->awaiting == 1}
			{$is_awaiting = true}
			{$awaiting_date = $v->awaiting_date}
		{/if}
	{/foreach}
{/if}


<li class="{if $product->featured}featured{/if} {if $product->disable}no_stock{/if}" data-product="{$product->id}">
	<div class="product_wrap">
		<div class="product_content">

			<div class="promo_block">
				{if $product->sale}
					<div class="sale" title="Акция и скидка">Супер цена</div>
				{/if}
			</div>

			<!-- Фото товара -->
			{if $product->image}
				<a class="image" href="/tovar-{$product->url}">
					<img src="{$product->image->filename|resize:220:220}" alt="{$product->name|escape}"
						title="{$product->name|escape}">
				</a>
			{/if}

			<div class="product_info">
				<a class="name" href="/tovar-{$product->url}"
					title="{$product->name|escape}">{$product->name|escape}</a>
				<div class="annotation">{$product->annotation}</div>
			</div>

			<div class="status_stock">
				{if $product->disable}
					<span class="notinstock">Товар больше не поставляется</span>
				{elseif $is_instock == true}
					<span class="instock">В наличии</span>
				{elseif $is_custom == true}
					<span class="awaiting">Под заказ</span>
				{elseif $is_awaiting == true}
					<span class="awaiting">
						Ожидается поставка
						{if strtotime(date("d.m.Y")) < strtotime($awaiting_date)}{$awaiting_date|date}{/if}</span>
				{else}
					<span class="notinstock">Нет в наличии</span>
				{/if}
			</div>

			<div class="product_price">

				<!-- Выбор варианта товара -->
				<form class="variants" action="/cart">
					<div class="variant">
						<input id="variants_{$product->variant->id}" name="variant" value="{$product->variant->id}"
							type="hidden" />
						<input name="amount" value="1" type="hidden" />
						<div class="price">{$product->variant->price|convert}<span
								class="price_sign">{$currency->sign|escape}</span>
						</div>
					</div>

					{if $is_instock == true}
						<button type="submit" class="button btn_buy btn_green" value="в корзину"
							data-result-text="добавлено">
							<svg class="cart-icon" viewBox="0 0 1024 1024">
								<path fill="#fff"
									d="M97.718857 109.714286a109.714286 109.714286 0 0 1 107.349333 87.064381L220.16 268.190476h0.24381l49.005714 234.666667L306.541714 682.666667h459.678476l70.460953-341.333334H285.500952l-15.286857-73.142857h566.491429a73.142857 73.142857 0 0 1 71.631238 87.942095l-70.460952 341.333334A73.142857 73.142857 0 0 1 766.22019 755.809524H306.541714a73.142857 73.142857 0 0 1-71.631238-58.343619l-69.241905-335.335619-0.463238 0.097524-31.695238-150.357334A36.571429 36.571429 0 0 0 97.718857 182.857143H35.157333v-73.142857zM304.761905 926.47619a60.952381 60.952381 0 1 0 0-121.904761 60.952381 60.952381 0 0 0 0 121.904761z m438.857143 0a60.952381 60.952381 0 1 0 0-121.904761 60.952381 60.952381 0 0 0 0 121.904761z">
								</path>
							</svg>
							<span>Купить</span>
						</button>
					{/if}

				</form>
			</div>
		</div>
	</div>
</li>