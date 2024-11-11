{* Шаблон корзины *}

{$wrapper = "index_order.tpl" scope=global}
{$meta_title = "Корзина" scope=global}

<div class="info-box">
	Заполните данные получателя и реквизиты доставки. Мы в ближайшее время перезвоним вам. Заказы отправляются только
	после подтверждения по телефону. Если по каким-то причинам мы с вами не связываемся продолжительное время, не
	стесняйтесь, перезвоните нам по контактным телефонам - это ускорит обработку вашего заказа.
</div>

<h1>Оформление заказа</h1>

<h2 class="cart_info">
	{if $cart->purchases}
		В корзине {$cart->total_products} {$cart->total_products|plural:'товар':'товаров':'товара'}
	{else}
		Корзина пуста
	{/if}
</h2>

<form method="post" name="cart" id=cart action="/cart?step=checkout">
	<div class=cart_wrapper>
		<div class="main_list" id=main_list>

			{if $cart->purchases}
				<div id="purchases">
					{foreach $cart->purchases as $purchase}
						<div class="purchase_row">
							<div class="image">
								{$image = $purchase->product->images|first}
								{if $image}
									<a href="/product/{$purchase->product->id}" target="_blank">
										<img src="{$image->filename|resize:50:50}" alt="{$product->name|escape}">
									</a>
								{/if}
							</div>
							<div class="name">
								<a href="/product/{$purchase->product->id}"
									target="_blank">{$purchase->product->name|escape}</a>
								{if $purchase->variant->name}
									- {$purchase->variant->name|escape}
								{/if}
							</div>
							<div class="amount">
								{($purchase->variant->price)|convert} {$currency->sign} &times; {$purchase->amount}
								{$settings->units}
							</div>
							<div class="price purchase_total_price">
								{($purchase->variant->price*$purchase->amount)|convert} {$currency->sign}
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

				</div>

				<div class="form_block">
					<h2>1. Информация о получателе</h2>
					<div class="wrapper">
						{if $error}
							<div class="message_error">
								{if $error == 'empty_name'}Введите Ваше Имя{/if}
								{if $error == 'empty_phone'}Введите Ваш номер телефона{/if}
							</div>
						{/if}
						<div class='row column_2'>
							<div class=row_part>
								<label for=phone>Телефон <span>(Обязательно)</span></label>
								<input id=phone name="phone" type="text" value="{$order->phone|escape}"
									autocomplete="tel" />
							</div>
							<div class=row_part>
								<label for=name>Имя, фамилия <span>(Обязательно)</span></label>
								<input id=name name="name" type="text" value="{$order->name|escape}" data-format=".+"
									data-notice="Введите имя" autocomplete="name" />
							</div>
						</div>
						<div class="row column_2">
							<div class=row_part>
								<label for=email>Email <span>(Сюда отправим инфрмацию о заказe)</span></label>
								<input id=email name="email" type="text" value="{$order->email|escape}" data-format="email"
									data-notice="Введите email" autocomplete="email" />
							</div>
							<div class=row_part>
								<label for=address>Город доставки</label>
								<input id=address name="address" type="text" value="{$order->address|escape}"
									autocomplete="address-level1" />
							</div>
						</div>
						<div class=row>
							<label for=comment>Номер отделения или комментарий к заказу</label>
							<textarea id=comment name="comment" id="order_comment">{$order->comment|escape|nl2br}</textarea>
						</div>
					</div>
				</div>

				<div class="form_block">
					{if $deliveries}
						<h2>2. Выберите способ доставки:</h2>
						<div class="wrapper">
							<ul id="deliveries">
								{foreach $deliveries as $delivery}
									<li>
										<div class="checkbox">
											<input type="radio" name="delivery_id" value="{$delivery->id}"
												{if $order->delivery_id==$delivery->id}checked{/if} id="deliveries_{$delivery->id}">
											<label for="deliveries_{$delivery->id}">
												{$delivery->name}
												{if $cart->total_price < $delivery->free_from && $delivery->price>0}
													<span
														class="delivery_price">{$delivery->price|convert}&nbsp;{$currency->sign}</span>
												{elseif $cart->total_price >= $delivery->free_from}
													<span class="delivery_price">бесплатно</span>
												{/if}
											</label>
										</div>

										<div class="description">
											{$delivery->description}
										</div>
									</li>
								{/foreach}
							</ul>
						</div>
					{/if}
				</div>

				<div class=form_block>
					{if $payment_methods}
						<h2>3. Выберите способ оплаты</h2>
						<div class="wrapper">
							<ul id="payments">
								{foreach $payment_methods as $payment_method}
									<li>
										<div class="checkbox">
											<input type="radio" name="payment_method_id" value='{$payment_method->id}'
												{if $payment_method->id == $order->payment_method_id}checked{/if}
												id="payment_{$payment_method->id}">
											<label for="payment_{$payment_method->id}">{$payment_method->public_name}</label>
										</div>
										<div class="description">
											{$payment_method->description}
										</div>
									</li>
								{/foreach}
							</ul>
						</div>
					{/if}
				</div>


			{else}
				<p>
					Выберите товары в каталоге. Когда корзина будет сформирована, можно будет оформить заказ. Приятных
					покупок!
				</p>
			{/if}

		</div>

		<div class="right_menu" id=right_menu>

			{if $coupon_request}
				<div class=form_block>
					<div class="wrapper">
						{if $coupon_error}
							<div class="message_error">
								{if $coupon_error == 'invalid'}Такого промокода у нас нет{/if}
							</div>
						{/if}
						<div class="row">
							<label for="coupon_code">Введите промокод <span>узнай свою скидку</span></label>
							<input id="coupon_code" name="coupon_code" type="text" value="{$order->coupon_code|escape}"
								autocomplete="off" />
						</div>
						<div class="row">
							<button class="button btn_grey" form=cart type="submit" name="promocod" value="true">
								Применить промокод
							</button>
						</div>
					</div>
				</div>
			{/if}

			<div class=form_block>
				<div class="wrapper">
					<div class="row checkout_total">
						<div class="left_part">Итого:</div>
						<div class="right_part">{$cart->total_price|convert} {$currency->sign}</div>
					</div>
					<button class="button btn_green mt_25" form=cart type="submit" name="checkout" value="true">
						Подтвердить заказ
					</button>
				</div>
			</div>
		</div>
	</div>
</form>

<script src="/templates/{$settings->theme|escape}/js/jquery/jquery.mask.js" type="text/javascript"></script>

<script>
	{literal}

		// Добавляем +380
		$('#phone').focus(function() {
			if (!$(this).val()) {
				element = document.getElementById("phone");
				element.value = '+38';
				setTimeout(function() {
					let end = element.value.length;
					element.setSelectionRange(end, end);
				}, 100);
			}
		});

		$('#phone').blur(function() {
			if ($(this).val() == '+38') {
				$(this).val('');
			}
		});

		// Устанавливаем формат номера
		$('#phone').mask('+38 (000) 000-00-00', {
			placeholder: "Номер телефона",
		});

		// Не даем удалить +380
		$('#phone').keydown(function(e) {
			let cursorPosition = $(this).selectionStart;
			if (e.keyCode == 8 && $(this).val() == '+38' || cursorPosition < 3) {
				e.preventDefault();
			}
		});
	{/literal}
</script>