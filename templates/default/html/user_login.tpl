{* Страница входа пользователя *}

{* Канонический адрес страницы *}
{$canonical = "/user/login" scope=global}
{$meta_title = "Вход" scope=global}

<div class="login-wrap">
	<h1>Вход <a href="/user/register">Регистрация →</a></h1>

	{if $error}
		<div class="message_error">
			{if $error == 'login_incorrect'}Неверный логин или пароль.
			{elseif $error == 'user_disabled'}Ваш аккаунт еще не активирован.
			{else}{$error}
			{/if}
		</div>
	{/if}

	<form class="form_block" name="login_form" method="post" action="/user/login">
		<div class="row">
			<label for="email">Email</label>
			<input type="text" id="email" name="email" value="{$email|escape}" maxlength="255" autocomplete="email" />
		</div>
		<div class="row">
			<label for="password">Пароль <a href="/user/password-remind">напомнить?</a></label>
			<input type="password" id="password" name="password" value />
		</div>
		<div class="row btn_row">
			<button class="button btn_green" name="submit" value="true">Войти</button>
		</div>
	</form>
</div>