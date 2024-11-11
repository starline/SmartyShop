{include file='content/content_menu_part.tpl'}

{if $post->id}
	{$meta_title = $post->name scope=global}
{else}
	{$meta_title = 'Новая запись в блоге' scope=global}
{/if}



{if $message_success}
	<div class="message message_success">
		<span class="text">{if $message_success == 'added'}Запись добавлена
			{elseif $message_success == 'updated'}Запись
			обновлена{/if}</span>
	</div>
{/if}


{if $message_error}
	<div class="message message_error">
		<span class="text">{if $message_error == 'url_exists'}Запись с таким адресом уже существует{/if}</span>
	</div>
{/if}

<!-- Основная форма -->
<form method=post class=form_css enctype="multipart/form-data">
	<input type=hidden name="session_id" value="{$smarty.session.id}">
	<div class="columns">

		<div class="block_flex w100">
			<div class="over_name">
				<div class=checkbox_line>
					<div class="checkbox_item">
						<input name="visible" value="1" type="checkbox" id="active_checkbox"
							{if $post->visible}checked{/if} />
						<label for="active_checkbox">Активна</label>
					</div>
				</div>
				<a class="out_link" target="_self" href="../blog/{$post->url}">Открыть статью на сайте</a>
			</div>

			<div class="name_row whith_id">
				<span class="item_id">H1</span>
				<input class="name" name=name type="text" value="{$post->name|escape}" autocomplete="off" />
				<input name="id" type="hidden" value="{$post->id|escape}" />
			</div>
		</div>

		<div class="block_flex layer">
			<h2>Параметры страницы</h2>
			<ul class="property_block">
				<li>
					<label for="date" class="property_name">Дата</label>
					<input type="text" name="date" id="date" class="small_inp" value="{$post->date|date}" />
				</li>
				<li>
					<label for="url" class="property_name">Адрес (url)</label>
					<div class="property_value">
						<div class="property_value_prefix">blog/</div>
						<div class="property_value_suffix">
							<input id="url" name="url" type="text" value="{$post->url|escape}" />
						</div>
					</div>
				</li>
				<li>
					<label for=meta_title class="property_name">Заголовок (MetaTitle)</label>
					<input id=meta_title name="meta_title" type="text" value="{$post->meta_title|escape}" />
				</li>
				<li>
					<label for=meta_description class="property_name">Описание (MetaDescription)
						</br>{$settings->emojis|escape}</label>
					<textarea id=meta_description name="meta_description">{$post->meta_description|escape}</textarea>
				</li>
				<li>
					<label for="seo_keywords" class="property_name">Ключевые слова (SEO)</label>
					<textarea id="seo_keywords" name="seo_keywords">{$seo_keywords|join:"\n"}</textarea>
				</li>
			</ul>

			<ul class="property_block layer">
				<li>
					<label for="annotation" class="property_name">Краткое описание</label>
					<textarea id="annotation" name="annotation">{$post->annotation|escape}</textarea>
				</li>
			</ul>

			<div class="btn_row">
				<input class="button_green" type="submit" name="" value="Сохранить" />
			</div>
		</div>

		<div id="images" class="block_flex layer images">
			<h2>Изображения поста</h2>
			<ul>
				{foreach $post->images as $image}
					<li>
						<span class="delete">
							<img src='/{$config->templates_subdir}images/cross-circle-frame.png' />
						</span>

						<a href="{$image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="images">
							<img src="{$image->filename|resize:220:220}" />
						</a>
						<input type="hidden" name="images[]" value="{$image->id}" />
					</li>
				{/foreach}
			</ul>

			<div class="dropZone">
				<input type="file" name="dropped_images[]" multiple class="dropInput" />
				<div class="dropMessage">Перетащите файлы сюда</div>
			</div>

			<div class="add_image"></div>
			<span class="upload_image">
				<i class="dash_link" id="upload_image">Добавить изображение</i>
			</span>
		</div>

		<div class="block_flex w100 layer">
			<h2>Полное описание</h2>
			<textarea name="body" class="html_editor editor_large">{$post->body|escape}</textarea>
		</div>

		<div class="block_flex btn_row w100">
			<input class="button_green" type="submit" name="" value="Сохранить" />
		</div>

	</div>
</form>

<script src="/{$config->templates_subdir}js/jquery/datepicker/jquery.ui.datepicker-ru.js"></script>

{* Подключаем Tiny MCE *}
{include file='parts/tinymce_init.tpl'}
{include file='parts/images_upload_init.tpl'}

<script>
	{literal}
		$(function() {

			$('input[name="date"]').datepicker({
				regional: 'ru'
			});

			// Автозаполнение мета-тегов
			meta_title_touched = true;
			meta_description_touched = true;
			url_touched = true;

			if ($('input[name="meta_title"]').val() == generate_meta_title() || $('input[name="meta_title"]').val() ==
				'')
				meta_title_touched = false;

			if ($('textarea[name="meta_description"]').val() == generate_meta_description() || $(
					'textarea[name="meta_description"]').val() == '')
				meta_description_touched = false;

			if ($('input[name="url"]').val() == generate_url() || $('input[name="url"]').val() == '')
				url_touched = false;

			$('input[name="meta_title"]').change(function() { meta_title_touched = true; });
			$('textarea[name="meta_description"]').change(function() { meta_description_touched = true; });
			$('input[name="url"]').change(function() { url_touched = true; });
			$('input[name="name"]').keyup(function() { set_meta(); });

		});

		function set_meta() {
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

		function generate_meta_description() {
			return $('textarea[name=annotation]').val().replace(/(<([^>]+)>)/ig, " ").replace(/(\&nbsp;)/ig, " ").replace(
				/^\s+|\s+$/g, '').substr(0, 512);
		}
	{/literal}
</script>