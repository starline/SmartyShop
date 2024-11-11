{* Шаблон корзины *}

{$wrapper = "index_order.tpl" scope=global}
{$meta_title = "Корзина" scope=global}

<div class="cart_wrap">

	<h1>
		{if $cart->purchases}
			В корзине {$cart->total_products} {$cart->total_products|plural:'товар':'товаров':'товара'}
		{else}
			Корзина пуста
		{/if}
	</h1>

	{if $cart->purchases}
		<form method="post" name="cart" action="/cart">
			<div id="purchases">

				{foreach $cart->purchases as $purchase}
					<div class="purchase_row">
						<div class="remove">
							<a href="/cart/remove/{$purchase->variant->id}" class="ajax">
								<img src="/{$config->templates_subdir}{$settings->theme}/images/delete.png"
									title="Удалить из корзины" alt="Удалить из корзины">
							</a>
						</div>
						<div class="image">
							{$image = $purchase->product->images|first}
							{if $image}
								<a href="/tovar-{$purchase->product->url}">
									<img src="{$image->filename|resize:50:50}" alt="{$product->name|escape}">
								</a>
							{/if}
						</div>
						<div class="name">
							<a href="/tovar-{$purchase->product->url}">{$purchase->product->name|escape}</a>
							{if $purchase->variant->name} - {$purchase->variant->name|escape}{/if}
						</div>
						<div class="amount">
							<select name="amounts[{$purchase->variant->id}]" onchange="cart_submit();">
								{$loop = ($purchase->variant->custom) ? $settings->max_order_amount : $purchase->variant->stock + 1}
								{section name=amounts start=1 loop=$loop step=1}
									<option value="{$smarty.section.amounts.index}"
										{if $purchase->amount == $smarty.section.amounts.index}selected{/if}>
										{$smarty.section.amounts.index} {$settings->units}</option>
								{/section}
							</select>
						</div>
						<div class="price purchase_total_price">
							{($purchase->variant->price*$purchase->amount)|convert} {$currency->sign}
						</div>
					</div>
				{/foreach}
			</div>

			<div class="bottom_cart_row">
				<div class="cart-total">
					<div class="total">Итого: {$cart->total_price|convert}&nbsp;{$currency->sign}</div>
					<div>
						{if $is_ajax}
							<span class="button continue" onclick="fancyclose();">Продолжить покупки</span>
						{/if}
						<a class="button btn_green ml_25" href="/cart?step=checkout">Оформить заказ</a>
					</div>
				</div>
			</div>
		</form>

	{else}
		<p>Выберите товары в каталоге. Когда корзина будет сформирована, можно будет оформить заказ. Приятных покупок!</p>
	{/if}

</div>


<script>
	{if $cart->purchases}
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push({
			'event': 'addToCart',
			'ecommerce': {
				'currencyCode': '{$currency->code}',
				'add': {
					'products': [
						{foreach $cart->purchases as $p}
							{
								'id': '{$p->variant->sku|escape}',
								'name': '{$p->product->name|escape}',
								'variant': '{$p->variant->name|escape}',
								'price': '{$p->variant->price}',
								'position': {$p@index},
								'category': '{$p->category_name|escape}',
								'quantity': {$p->amount}
							},
						{/foreach}
					]
				}
			}
		});
	{/if}

	function cart_submit() {
		$('form[name=cart]').submit();
	}

	function fancyclose() {
		$.fancybox.close();
	}
</script>