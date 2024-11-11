{* Письмо о заказе на почту админа *}

{if $order->paid}
	{$subject = "Заказ №`$order->id` оплачен" scope=global}
{else}
	{$subject = "Новый заказ №`$order->id`" scope=global}
{/if}

<div id="header">
	<h1 style="display: inline;">
		<a href="{$config->root_url}/agmin?view=OrderAdmin&id={$order->id}">Заказ №{$order->id}</a>
	</h1>
	<span>от {$order->date|date}</span>
	<p>{$settings->company_name} - {$settings->company_description}</p>
</div>


<div id="customer">
	<h2>Получатель</h2>
	<p>{$order->name|escape}</p>
	<p>{$order->phone|escape}</p>
	<p>{$order->email|escape}</p>
	<p>{$order->address|escape}</p>
	<p><i>{$order->comment|escape|nl2br}</i></p>
</div>


<div id="purchases">
	<table>
		<tr>
			<th></th>
			<th>Товар</th>
			<th style="text-align: right;">Цена</th>
			<th style="text-align: right;">Кол-во</th>
			<th style="text-align: right;">Всего</th>
		</tr>

		{foreach $purchases as $purchase}
			<tr>
				<td>
					{$image = $purchase->product->images[0]}
					<a href="{$config->root_url}/tovar-{$purchase->product->url}">
						<img border="0" src="{$image->filename|resize:50:50}">
					</a>
				</td>
				<td>
					<a href="{$config->root_url}/tovar-{$purchase->product->url}">{$purchase->product_name|escape}</a>
					{$purchase->variant_name|escape} {if $purchase->sku} (арт {$purchase->sku|escape}){/if}
				</td>
				<td>
					{$purchase->price} {$currency->sign}
				</td>
				<td>
					{$purchase->amount} {$settings->units}
				</td>
				<td>
					{$purchase->price * $purchase->amount} {$currency->sign}
				</td>
			</tr>
		{/foreach}

	</table>
</div>

{if !$delivery_method->name|empty}
	<div id="delivery" style="margin: 25px 0;">
		<h2>Доставка</h2>
		<div>{$delivery_method->name|escape}</div>
		{if $order->delivery_price > 0}
			<div style="text-align: right;">
				{$order->delivery_price|convert}&nbsp;{$currency->sign}{if $order->separate_delivery}&nbsp;(оплачивается&nbsp;отдельно){/if}
			</div>
		{/if}
	</div>
{/if}


<div id="total" style="margin: 25px 0;">
	<h2>Оплата</h2>
	{if $order->discount > 0}
		<tr>
			<th style="text-align: left;">Скидка</th>
			<td>{$order->discount} %</td>
		</tr>
	{/if}

	{if $order->coupon_discount > 0}
		<tr style="text-align: left;">
			<th>Купон{if $order->coupon_code}&nbsp;({$order->coupon_code}){/if}</th>
			<td>{$order->coupon_discount}&nbsp;{$currency->sign}</td>
		</tr>
	{/if}

	<tr>
		<th style="text-align: left;">Итого:</th>
		<td class="total">{$order->payment_price}&nbsp;{$currency->sign}</td>
	</tr>

	{if !$payment_method->public_name|empty}
		<tr>
			<th style="text-align: left;">Оплата:</th>
			<td>{$payment_method->public_name}</td>
		</tr>
	{/if}
	</table>
</div>