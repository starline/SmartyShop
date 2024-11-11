{include file='users/users_menu_part.tpl'}
{include file='users/user_submenu_part.tpl'}

{if $current_user->id}
	{$meta_title = $current_user->name|escape scope=global}
{/if}

{if $message_success}
	<div class="message message_success">
		<span class="text">{if $message_success=='updated'}Пользователь
			отредактирован{else}{$message_success|escape}
			{/if}</span>
	</div>
{/if}


<!-- Основная форма -->
<form method="post" class=form_css>
	<input type="hidden" name="session_id" value="{$smarty.session.id}">

	<div class="columns">

		<div class="block_flex w100">
			<div class="over_name">
				<div class="checkbox_line">

				</div>
			</div>
			<div class="name_row">
				<input name="id" type="hidden" value="{$current_user->id|escape}" />
				<input class="name" name="name" type="text" value="{$current_user->name|escape}" autocomplete="off"
					disabled />
			</div>
		</div>

		<div class="block_flex layer">
			<h2>Данные пользователя</h2>
			<ul class="property_block">
				{if !$groups|empty}
					<li>
						<label class="property_name" for="group_id">Группа</label>
						<select id="group_id" name="group_id" {if !$user|user_access:users_groups}disabled{/if}>
							<option value="">Не входит в группу</option>
							{foreach $groups as $g}
								<option value='{$g->id}' {if $current_user->group_id == $g->id}selected{/if}>{$g->name|escape}
								</option>
							{/foreach}
						</select>
					</li>
				{/if}
			</ul>

			<div class="btn_row">
				<input class="button_green" type="submit" value="Сохранить" />
			</div>

		</div>

		<div class="block_flex layer">
			<h2>Права пользователя</h2>
			<select class="multiple_categories" multiple name="permissions[]">
				{foreach $permissions_list as $value=>$name}
					<option value='{$value}' {if in_array($value, $permissions)}selected{/if}>{$name}</option>
				{/foreach}
			</select>
		</div>

		<div class="block_flex layer">
			<h2>Настройки оповещений</h2>
			<ul class="property_block">
				{foreach $notify_methods as $method}
					<li>
						<label for="{$method->module}" class="property_name">{$method->name}</label>
						<select name="user_notify_types[{$method->id}][]" multiple id="{$method->module}">
							{foreach $notify_types as $type_name=>$type_description}
								<option value='{$type_name}'
									{if in_array($type_name, $user_notify_types[$method->id])}selected{/if}>
									{$type_description}
								</option>
							{/foreach}
						</select>
					</li>
				{/foreach}
			</ul>

			<div class="btn_row">
				<input class="button_green" type="submit" value="Сохранить" />
			</div>
		</div>
</form>