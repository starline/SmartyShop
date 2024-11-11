{include file='settings/settings_menu_part.tpl'}

{* Title *}
{$meta_title='Бекап' scope=global}

{if $message_success}
	<!-- Системное сообщение -->
	<div class="message message_success">
		<span class="text">{if $message_success == 'created'}Бекап создан
			{elseif $message_success == 'restored'}Бекап
			восстановлен{/if}</span>
	</div>
{/if}

{if $message_error}
	<!-- Системное сообщение -->
	<div class="message message_error">
		<span class="text">
			{if $message_error == 'no_permission'}Установите права на запись в папку {$backup_dir}
			{else}{$message_error}
			{/if}
		</span>
	</div>
{/if}

{* Заголовок *}
<div class=header_top>
	<h1>Бекап</h1>
	{if $message_error != 'no_permission'}
		<form id="hidden" method="post">
			<input type="hidden" name="session_id" value="{$smarty.session.id}">
			<input type="hidden" name="action" value="">
			<input type="hidden" name="name" value="">
		</form>

		<a class="add" href="">Создать бекап</a>
	{/if}
</div>

{if $backups}
	<div id="main_list">

		<form id="list_form" method="post">
			<input type="hidden" name="session_id" value="{$smarty.session.id}">

			<div id="backups" class="list">
				{foreach $backups as $backup}
					<div class="row">
						{if $message_error != 'no_permission'}
							<div class="checkbox">
								<input type="checkbox" name="check[]" value="{$backup->name}" />
							</div>
						{/if}
						<div class="name">
							<a href="{$config->root_url}/files/backup/{$backup->name}">{$backup->name}</a>
							<div class="round_box">
								{if $backup->size>1024*1024}
									{($backup->size/1024/1024)|round:2} МБ
								{else}
									{($backup->size/1024)|round:2} КБ
								{/if}
							</div>
						</div>
						<div class="icons">
							{if $message_error != 'no_permission'}
								<a class="delete" title="Удалить" href="#"></a>
							{/if}
						</div>
						<div class="icons">
							<a class="restore" title="Восстановить этот бекап" href="#"></a>
						</div>
					</div>
				{/foreach}
			</div>

			{if $message_error != 'no_permission'}
				<div id="action">
					<span id="check_all" class="dash_link">Выбрать все</span>

					<span id="select">
						<select name="action">
							<option value="">Выбрать действие</option>
							<option value="delete">Удалить</option>
						</select>
					</span>

					<input id="apply_action" class="button_green" type="submit" value="Применить">
				</div>
			{/if}

		</form>
	</div>
{/if}



<script>
	{literal}

		$(function() {

			// Восстановить 
			$("a.restore").click(function() {
				file = $(this).closest(".row").find('[name*="check"]').val();
				$('form#hidden input[name="action"]').val('restore');
				$('form#hidden input[name="name"]').val(file);
				$('form#hidden').submit();
				return false;
			});

			// Создать бекап 
			$("a.add").click(function() {
				$('form#hidden input[name="action"]').val('create');
				$('form#hidden').submit();
				return false;
			});

			$("form#hidden").submit(function() {
				if ($('input[name="action"]').val() == 'restore' && !confirm(
						'Текущие данные будут потеряны. Подтвердите восстановление'))
					return false;
			});

		});

	{/literal}
</script>