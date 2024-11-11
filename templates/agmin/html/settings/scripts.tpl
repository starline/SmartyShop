{include file='settings/settings_menu_part.tpl'}

{$meta_title='Скрипты' scope=global}

<div class="header_top">
	<h1>Скрипты</h1>
</div>


<div class="">
	<form method="post">
		<input type="hidden" name="session_id" value="{$smarty.session.id}">

		<div class="checkbox_line">

			<div class="checkbox_item">
				<input name="action" value="script" type="radio" id="script" />
				<label for="script">Запустить скрипт</label>
			</div>

			<div class="checkbox_item">
				<input name="action" value="php_check" type="radio" id="php_check" />
				<label for="php_check">Проверить PHP</label>
			</div>

			<div class="checkbox_item">
				<input name="action" value="create_user_by_order" type="radio" id="create_user_by_order" />
				<label for="create_user_by_order" title="Cоздаем пользователей по данным из заказов">Пользователи из
					заказов</label>
			</div>


			<div class="checkbox_item">
				<input name="action" value="related_products" type="radio" id="related_products" />
				<label for="related_products" title="Выбираем сопутсвующие товары к каждому товару">Сопутсвующие
					товары</label>
			</div>
		</div>

		<input class="button_green" type="submit" name="do_script" value="Выполнить скрипт" />

	</form>

	<div id="main_list">
		{if $new_users}
			{foreach $new_users as $user}
				<div>
					<a href="{url view=UserAdmin id=$user->id clear=true}">{$user->name} {$user->phone}</a>
				</div>
			{/foreach}
		{/if}

		{if $php_check}
			<div><b>php version:</b> {$php_check->version}</div>
			<div><b>apc.shm_size:</b> {if $php_check->apc|isset}{$php_check->apc} enabled{else}disabled{/if}</div>
			<div><b>default_charset:</b> {$php_check->default_charset}</div>
			<div><b>short_open_tag:</b> {$php_check->short_open_tag}</div>
			<div><b>display_errors:</b> {$php_check->display_errors}</div>
			<div><b>mbstring.func_overload:</b> {$php_check->func_overload}</div>
		{/if}

		{if $result}
			<div>
				<pre>{$result}</pre>
			</div>
		{/if}
	</div>
</div>


<script>
	{literal}

		$(function() {

			$(".do_script").on('click', function() {
				$('form#hidden input[name="action"]').val('restore_orders');
				$('form#hidden').submit();
				return false;
			});
		});

	{/literal}
</script>