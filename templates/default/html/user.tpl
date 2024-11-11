<div class="user_info login-wrap">
	<h1>{$user->name|escape}</h1>

	{if $error}
		<div class="message_error">
			{if $error == 'empty_name'}Введите имя
			{elseif $error == 'empty_email'}Введите email
			{elseif $error == 'empty_password'}Введите пароль
			{elseif $error == 'user_exists'}Пользователь с таким email уже зарегистрирован
			{else}{$error}
			{/if}
		</div>
	{/if}

	<form class="form_block" method="post">
		<div class="row">
			<label for=name>Имя</label>
			<input data-format=".+" data-notice="Введите имя" value="{$name|escape}" name="name" id="name"
				maxlength="255" type="text" />
		</div>
		<div class="row">
			<label for=email>Email</label>
			<input data-format="email" id=email data-notice="Введите email" value="{$email|escape}" name="email"
				maxlength="255" type="text" />
		</div>
		<div class="row">
			<label for=password><a href='#' onclick="$('#password').show();return false;">Изменить пароль</a></label>
			<input id="password" value="" name="password" type="password" style="display:none;" />
		</div>
		<div class="row btn_row">
			<button class="button btn_green" name="submit" value="Сохранить">Сохранить</button>
		</div>
	</form>
</div>

{if $orders}
	<div class="user_orders">
		<h1>Ваши заказы</h1>
		<ul id="orders_history">
			{foreach name=orders item=order from=$orders}
				<li>
					{$order->date|date} <a href='order/{$order->url}'>Заказ №{$order->id}</a>
					{if $order->paid == 1}оплачен,{/if}
					{if $order->status == 0}ждет обработки
					{elseif $order->status == 1}в обработке
					{elseif $order->status == 2}выполнен
					{elseif $order->status == 3}отменен
					{/if}
				</li>
			{/foreach}
		</ul>
	</div>
{/if}