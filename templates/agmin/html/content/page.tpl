{include file='content/content_menu_part.tpl'}


{if $page->id}
	{$meta_title = $page->name scope=global}
{else}
	{$meta_title = 'Новая страница' scope=global}
{/if}


{if $message_success}
	<div class="message message_success">
		<span class="text">{if $message_success == 'added'}Страница добавлена
			{elseif $message_success == 'updated'}Страница
			обновлена{/if}</span>
	</div>
{/if}


{if $message_error}
	<div class="message message_error">
		<span class="text">{if $message_error == 'url_exists'}Страница с таким адресом уже существует{/if}</span>
	</div>
{/if}


<!-- Основная форма -->
<form method="post" class="form_css" enctype="multipart/form-data">
	<input type="hidden" name="session_id" value="{$smarty.session.id}">

	<div class="columns">

		<div class="block_flex w100">
			<div class="over_name">
				<div class="checkbox_line">
					<div class="checkbox_item">
						<input name="visible" value="1" type="checkbox" id="active_checkbox"
							{if $page->visible}checked{/if} />
						<label for="active_checkbox">Активна</label>
					</div>
				</div>

				{if $page->url}
					<a class="out_link" target="_self"
						href="{if $page->menu_id == 2}/{$page->url}{else}/info/{$page->url}{/if}">
						Открыть страницу на сайте
					</a>
				{/if}
			</div>

			<div class="name_row">
				<span class="item_id">#{$page->id}</span>
				<input class="name" name="name" type="text" value="{$page->name|escape}" />
				<input name="id" type="hidden" value="{$page->id|escape}" />
			</div>
		</div>

		<div class="block_flex layer">
			<h2>Настройки меню</h2>
			<ul class="property_block">
				<li>
					<label for="h1" class="property_name">Заголовок (H1)</label>
					<input id="h1" name="h1" type="text" value="{$page->h1|escape}">
				</li>
				<li>
					<label for="menu_id" class="property_name">Меню</label>
					<select id="menu_id" name="menu_id">
						{foreach $menus as $m}
							<option value="{$m->id}" {if $page->menu_id == $m->id}selected{/if}>{$m->name|escape}
							</option>
						{/foreach}
					</select>
				</li>
			</ul>
		</div>

		<div class="block_flex layer">
			<h2>Параметры страницы</h2>
			<ul class="property_block">
				<li>
					<label for="url" class="property_name">Адрес</label>
					<div class="property_value">
						<div class="property_value_prefix">{if $page->menu_id != 2}info/{/if}</div>
						<div class="property_value_suffix">
							<input id="url" name="url" type="text" value="{$page->url|escape}" />
						</div>
					</div>
				</li>
				<li>
					<label class="property_name">Заголовок (Title)</label>
					<input name="meta_title" type="text" value="{$page->meta_title|escape}" />
				</li>
				<li>
					<label class="property_name">Описание (MetaDescription)
						<div class="emojis">{$settings->emojis|escape}</div>
					</label>
					<textarea name="meta_description">{$page->meta_description|escape}</textarea>
				</li>
			</ul>
		</div>

		<div class="block_flex w100 layer">
			<h2>Текст страницы</h2>
			<textarea name="body" class="html_editor editor_large">{$page->body|escape}</textarea>
		</div>

		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="" value="Сохранить" />
		</div>

	</div>
</form>


{include file='parts/tinymce_init.tpl'}


<script>
	{literal}
		$(function() {

			// Автозаполнение мета-тегов
			menu_item_name_touched = true;
			meta_title_touched = true;
			meta_description_touched = true;
			url_touched = true;

			if ($('input[name="menu_item_name"]').val() == generate_menu_item_name() || $('input[name="name"]')
				.val() == '')
				menu_item_name_touched = false;
			if ($('input[name="meta_title"]').val() == generate_meta_title() || $('input[name="meta_title"]').val() ==
				'')
				meta_title_touched = false;
			if ($('textarea[name="meta_description"]').val() == generate_meta_description() || $(
					'textarea[name="meta_description"]').val() == '')
				meta_description_touched = false;
			if ($('input[name="url"]').val() == generate_url())
				url_touched = false;

			$('input[name="name"]').change(function() { menu_item_name_touched = true; });
			$('input[name="meta_title"]').change(function() { meta_title_touched = true; });
			$('textarea[name="meta_description"]').change(function() { meta_description_touched = true; });
			$('input[name="url"]').change(function() { url_touched = true; });

			$('input[name="name"]').keyup(function() { set_meta(); });
		});

		function set_meta() {
			if (!menu_item_name_touched)
				$('input[name="name"]').val(generate_menu_item_name());
			if (!meta_title_touched)
				$('input[name="meta_title"]').val(generate_meta_title());
			if (!meta_description_touched) {
				descr = $('textarea[name="meta_description"]');
				descr.val(generate_meta_description());
				descr.scrollTop(descr.outerHeight());
			}
			if (!url_touched)
				$('input[name="url"]').val(generate_url());
		}

		function generate_menu_item_name() {
			name = $('input[name="name"]').val();
			return name;
		}

		function generate_meta_description() {
			return $('textarea[name=body]').val().replace(/(<([^>]+)>)/ig, " ").replace(/(\&nbsp;)/ig, " ").replace(
				/^\s+|\s+$/g, '').substr(0, 512);
		}
	{/literal}
</script>