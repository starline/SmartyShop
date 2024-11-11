{* Письмо пользователю для восстановления пароля *}

{* Канонический адрес страницы *}
{$canonical="/user/password-remind" scope=global}

<div class="login-wrap">
	{if $email_sent}
		<h1>Вам отправлено письмо</h1>
		<p>На <b>{$email|escape}</b> отправлено письмо для восстановления пароля.</p>
	{else}
		<h1>Напоминание пароля</h1>

		{if $error}
			<div class="message_error">
				{if $error == 'user_not_found'}
					Пользователь не найден
				{else}
					{$error}
				{/if}
			</div>
		{/if}

		<form class="form_block" method="post">
			<div class="row">
				<label for=email>Введите email, который вы указывали при регистрации</label>
				<input type="text" name="email" id=email data-format="email" data-notice="Введите email"
					value="{$email|escape}" maxlength="255" autocomplete="email" />
			</div>

			<div class="row btn_row">
				<button class="button btn_green" name="submit" value="true">Вспомнить</button>
			</div>
		</form>
	{/if}
</div>