{*Печать заказа*}
{$wrapper='' scope=global}

<!DOCTYPE html>
<html>

<head>
	<title>Заказ №{$order->id}</title>

	{* Метатеги *}
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="description" content="{$meta_description|escape}" />

	<link rel="icon" href="/{$config->templates_subdir}{$settings->theme|escape}/images/favicon.ico"
		type="image/x-icon" />


	<style>
		{literal}
			@page {
				size: A4 portrait;
			}

			body {
				margin: 0;
				padding: 0;
				font: 0.9em "Open Sans", sans-serif;
				background-color: #fff;
			}

			div.wrapper {
				width: 900px;
				margin-left: auto;
				margin-right: auto;
				font-family: "Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;
				font-size: 10pt;
				color: black;
				background-color: white;
			}

			h1 {
				margin: 0;
				font-weight: normal;
				font-size: 40px;
			}

			h2 {
				margin: 0;
				font-weight: normal;
				font-size: 24px;
			}

			h3 {margin: 0;}

			p {
				font-style: italic;
				margin: 0;
			}

			div#header {
				margin-top: 50px;
				height: 150px;
				width: 300px;
				float: left;
			}

			div#company {
				margin-top: 50px;
				margin-bottom: 20px;
				width: 550px;
				float: right;
				text-align: right;
			}

			div#customer {
				text-align: right;
				float: right;
			}

			div#customer table {
				margin-bottom: 20px;
				font-size: 15px;
			}

			div#purchases {
				margin-bottom: 40px;
				width: 100%;
			}

			div#purchases table {
				width: 100%;
				border-collapse: collapse;
			}

			div#purchases table th {
				font-weight: 600;
				text-align: left;
				font-size: 15px;
			}

			div#purchases td,
			div#purchases th {
				font-size: 14px;
				padding-top: 10px;
				padding-bottom: 10px;
				margin: 0;
			}

			div#purchases td {
				border-top: 1px solid lightgrey;
			}

			div#subtotal {
				float: right;
				width: 500px;
				text-align: right;
				margin-bottom: 40px;
			}

			div#subtotal table {
				width: 500px;
				float: right;
				border-collapse: collapse
			}

			div#subtotal th {
				font-weight: normal;
				text-align: left;
				font-size: 15px;
				border-top: 1px solid black;
			}

			div#subtotal td {
				text-align: right;
				border-top: 1px solid black;
				font-size: 15px;
				padding-top: 10px;
				padding-bottom: 10px;
				margin: 0;
			}

			div#subtotal tr .total {
				font-weight: 600;
			}

			div#total {
				float: right;
				width: 500px;
				text-align: right;
			}

			div#total table {
				width: 500px;
				float: right;
				border-collapse: collapse
			}

			div#total th {
				font-weight: normal;
				text-align: left;
				font-size: 16px;
				border-top: 1px solid black;
			}

			div#total td {
				text-align: right;
				border-top: 1px solid black;
				font-size: 16px;
				padding-top: 10px;
				padding-bottom: 10px;
				margin: 0;
			}

			div#total tr .total {
				font-weight: 600;
				font-size: 24px;
			}

			div#purchases td.align_right,
			div#purchases th.align_right {
				text-align: right;
			}

			.no_border {
				border-top: none !important;
			}

		{/literal}
	</style>

</head>

<body>
	<div class='wrapper'>
		<div id="header">
			<h1>Заказ №{$order->id}</h1>
			<p>от {$order->date|date}</p>
		</div>

		<div id="company">
			<h2>{$settings->company_name}</h2>
			<span>{$settings->company_description}</span>
		</div>

		<div id="customer">
			<h3>Получатель</h3>
			<table>
				<tr>
					<td>{$order->name|escape}</td>
				</tr>
				<tr>
					<td>{$order->phone|escape}</td>
				</tr>
				<tr>
					<td>{$order->email|escape}</td>
				</tr>
				<tr>
					<td>{$order->address|escape}</td>
				</tr>
				<tr>
					<td>
						<i>{$order->comment|escape|nl2br}</i>
					</td>
				</tr>
			</table>
		</div>

		<div id="purchases">
			<table>
				<tr>
					<th>Товар</th>
					<th class="align_right">Цена</th>
					<th class="align_right">Количество</th>
					<th class="align_right">Всего</th>
				</tr>

				{foreach $purchases as $purchase}
					<tr>
						<td>
							<span class="view_purchase">
								{$purchase->product_name} {$purchase->variant_name} {if $purchase->sku} (арт.
								{$purchase->sku}){/if}
							</span>
						</td>
						<td class="align_right">
							<span class="view_purchase">{$purchase->price|convert}</span> {$currency->sign}
						</td>
						<td class="align_right">
							<span class="view_purchase">
								{$purchase->amount} {$settings->units}
							</span>
						</td>
						<td class="align_right">
							<span class="view_purchase">{($purchase->price * $purchase->amount)|convert}</span>
							{$currency->sign}
						</td>
					</tr>
				{/foreach}

				{* Если стоимость доставки входит в сумму заказа *}
				{if $order->delivery_price > 0 AND !$order->separate_delivery}
					<tr>
						<td colspan=3>{$delivery->name|escape}{if $order->separate_delivery} (Оплачивается отдельно){/if}
						</td>
						<td class="align_right">{$order->delivery_price|convert}&nbsp;{$currency->sign}</td>
					</tr>
				{/if}

			</table>
		</div>


		<div id="subtotal">
			<table>
				{if $order->discount > 0}
					<tr>
						<th class=no_border>Скидка</th>
						<td class=no_border>{$order->discount} %
							({($subtotal*($order->discount/100))|convert}&nbsp;{$currency->sign})</td>
					</tr>
				{/if}

				{if $order->coupon_discount>0}
					<tr>
						<th class=no_border>Купон{if $order->coupon_code} ({$order->coupon_code}){/if}</th>
						<td class=no_border>-{$order->coupon_discount|convert}&nbsp;{$currency->sign}</td>
					</tr>
				{/if}

				{if $order->delivery_price > 0 AND !$order->separate_delivery}
					{$total_price = $order->total_price + $order->delivery_price}
				{else}
					{$total_price = $order->total_price}
				{/if}
				<tr>
					<th class="total">Итого</th>
					<td class="total">{$total_price|convert}&nbsp;{$currency->sign}</td>
				</tr>
			</table>
		</div>


		<div id="total">
			<table>
				{if $payment_method}
					<tr>
						<td class=no_border colspan="2">Способ оплаты: {$payment_method->public_name}</td>
					</tr>
					<tr>
						<th class="total">К оплате</th>
						<td class="total">
							{$order->payment_price|convert:$payment_method->currency_id}&nbsp;{$payment_currency->sign}</td>
					</tr>
				{/if}
			</table>
		</div>
	</div>
</body>

</html>