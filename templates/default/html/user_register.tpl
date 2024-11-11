{$meta_title = "Регистрация" scope=global}
{$meta_description = "Страница регистрации пользователя" scope=global}

<div class="login-wrap">
	<h1>Регистрация</h1>

	{if $error}
		<div class="message_error">
			{if $error == 'empty_name'}Введите имя
			{elseif $error == 'empty_email'}Введите email
			{elseif $error == 'empty_password'}Введите пароль
			{elseif $error == 'user_exists'}Пользователь с таким email уже зарегистрирован
			{elseif $error == 'captcha'}Неверно введена капча
			{else}{$error}
			{/if}
		</div>
	{/if}

	<form class="form_block" method="post">
		<div class="row">
			<label for=name>Имя</label>
			<input type="text" name="name" id=name data-format=".+" data-notice="Введите имя" value="{$name|escape}"
				maxlength="255" autocomplete="name" />
		</div>
		<div class="row">
			<label for=email>Email</label>
			<input type="text" name="email" id=email data-format="email" data-notice="Введите email"
				value="{$email|escape}" maxlength="255" autocomplete="email" />
		</div>
		<div class="row">
			<label for=password>Пароль</label>
			<input type="password" name="password" id=password data-format=".+" data-notice="Введите пароль" value="" />
		</div>
		<div class="row">
			<div class="g-recaptcha" data-sitekey="{$config->rc_public_key}"></div>
		</div>
		<div class="row btn_row">
			<button class="button btn_green" name="register" name="submit" value="true">Зарегистрироваться</button>
		</div>
	</form>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>