{$wrapper = "index_order.tpl" scope=global}

{$meta_title = "Заказ №`$order->id`" scope=global}

{if $order->status == 0}
	<div class="message message_success">
		<span class="text">
			Информация о доставке и оплате успешно сохранена.<br>
			В ближайшее время мы свяжемся с вами для уточнения деталей заказа.
		</span>
	</div>
{/if}

<h1>Заказ №{$order->id}
	{if $order->status == 0}принят в обработку{/if}{if $order->status == 1}готовится к
	отправке{/if}{if $order->status == 2}выполнен{/if}{if $order->status == 3}отклонен{/if}{if $order->paid == 1},
	оплачен{/if}
</h1>

<div class=cart_wrapper>
	<div class="main_list" id=main_list>
		<div id="purchases">

			{foreach $purchases as $purchase}
				<div class="purchase_row">
					<div class="image">
						{$image = $purchase->product->images|first}
						{if $image}
							<a href="/tovar-{$purchase->product->url}"><img src="{$image->filename|resize:50:50}"
									alt="{$product->name|escape}"></a>
						{/if}
					</div>

					<div class="name">
						<a href="/tovar-{$purchase->product->url}">{$purchase->product->name|escape}</a>
						{$purchase->variant->name|escape}

						{if $order->paid && $purchase->variant->attachment}
							<a class="download_attachment" href="/order/{$order->url}/{$purchase->variant->attachment}">скачать
								файл</a>
						{/if}
					</div>

					<div class="amount">
						{($purchase->price)|convert}&nbsp;{$currency->sign} &times;
						{$purchase->amount}&nbsp;{$settings->units}
					</div>

					<div class="price purchase_total_price">
						{($purchase->price*$purchase->amount)|convert}&nbsp;{$currency->sign}
					</div>
				</div>
			{/foreach}

			{if $order->discount > 0}
				<div class="purchase_row">
					<div class="amount">скидка</div>
					<div class="price">
						{$order->discount}&nbsp;%
					</div>
				</div>
			{/if}

			{if $order->coupon_discount > 0}
				<div class="purchase_row">
					<div class="amount">купон</div>
					<div class="price">
						&minus;{$order->coupon_discount|convert}&nbsp;{$currency->sign}
					</div>
				</div>
			{/if}

			{* Если стоимость доставки входит в сумму заказа *}
			{if !$order->separate_delivery && $order->delivery_price>0}
				<div class="purchase_row">
					<div class="amount">{$delivery->name|escape}</div>
					<div class="price">
						{$order->delivery_price|convert}&nbsp;{$currency->sign}
					</div>
				</div>
			{/if}
		</div>

		<div class="form_block">
			<h2>Информация о получателе</h2>
			<div class="wrapper">
				<div class='row column_2'>
					<div class=row_part>
						<div class="label">Телефон</div>
						<div class="value">{$order->phone|escape}</div>
					</div>
					{if $order->name}
						<div class=row_part>
							<div class="label">Имя</div>
							<div class="value">{$order->name|escape}</div>
						</div>
					{/if}
				</div>
				<div class="row column_2">
					{if $order->email}
						<div class=row_part>
							<div class="label">Email</div>
							<div class="value">{$order->email|escape}</div>
						</div>
					{/if}
					{if $order->address}
						<div class=row_part>
							<div class="label">Город доставки</div>
							<div class="value">{$order->address|escape}</div>
						</div>
					{/if}
				</div>
				{if $order->comment}
					<div class=row>
						<div class="label">Номер отделения или комментарий к заказу</div>
						<div class="value">{$order->comment|escape|nl2br}</div>
					</div>
				{/if}
			</div>
		</div>

		<!-- Доставка -->
		{if $delivery}
			<div class="form_block">
				<h2>Cпособ доставки</h2>
				<div class="wrapper">
					<div class="form_item_name">{$delivery->name}
						{if $order->total_price < $delivery->free_from && $delivery->price > 0}
							<span class="delivery_price">{$delivery->price|convert}&nbsp;{$currency->sign}</span>
						{elseif $order->total_price >= $delivery->free_from}
							<span class="delivery_price">(бесплатно)</span>
						{/if}
					</div>
					<div class="form_item_description">
						{$delivery->description}
					</div>
				</div>
			</div>
		{/if}

		<!-- Оплата -->
		{if $payment_method->module != 'null' AND $payment_method->module}
			<div class="form_block">
				<h2>Cпособ оплаты</h2>
				<div class="wrapper">
					<div class="form_item_name">{$payment_method->public_name}</div>
					<div class="form_item_description">
						{$payment_method->description}
					</div>
					<div class="payment_form">
						{get_payment_module_html order_id=$order->id module=$payment_method->module view_type='order'}
					</div>
				</div>
			</div>
		{/if}
	</div>

	<div class="right_menu" id="right_menu">
		<div class="form_block">
			<div class="wrapper">
				<div class="row checkout_total">
					<div class="left_part">К оплате:</div>
					<div class="right_part">{$order->payment_price|convert} {$currency->sign}</div>
				</div>
			</div>
		</div>
	</div>
</div>



<script>
	//https://enhancedecommerce.appspot.com/
	window.dataLayer = window.dataLayer || [];
	dataLayer.push({
		'event': 'conversion',
		'value': {$order->total_price},
		'transaction_id': {$order->id},
		'currency': '{$currency->code}'
	});

	window.dataLayer.push({
		'event': 'DynamicRemarketing',
		'dynamicParams': {
			'ecomm_prodid': ['{$variants_sku|join:"','"}'],
			'ecomm_pagetype': 'purchase',
			'ecomm_totalvalue': {$order->total_price}
		}
	});

	window.dataLayer.push({
		'event': 'purchase',
		'ecommerce': {
			'purchase': {
				'actionField': {
					'id': {$order->id},
					'revenue': {$order->total_price},
					'affiliation': '{$settings->site_name|escape}'
				},
				'products': [
					{foreach $purchases as $p}
						{
							'id': '{$p->variant->sku}',
							'name': '{$p->product->name}',
							'variant': '{$p->variant->name}',
							'price': '{$p->price}',
							'quantity': '{$p->amount}',
							'category': '{$p->product->category_name}'
						},
					{/foreach}
				]
			},
		}
	});
</script>