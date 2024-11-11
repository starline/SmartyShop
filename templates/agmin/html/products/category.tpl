{include file='products/products_settings_menu_part.tpl'}

{if $category->id}
	{$meta_title = $category->name scope=global}
{else}
	{$meta_title = 'Новая категория' scope=global}
{/if}

{if $message_success}
	<div class="message message_success">
		<span class="text">{if $message_success=='added'}Категория добавлена
			{elseif $message_success=='updated'}Категория
			обновлена{else}{$message_success}
			{/if}</span>
	</div>
{/if}

{if $message_error}
	<!-- Системное сообщение -->
	<div class="message message_error">
		<span class="text">{if $message_error=='url_exists'}Категория с таким адресом уже
			существует{else}{$message_error}
			{/if}</span>
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
						<input name="visible" value="1" type="checkbox" id="visible_checkbox"
							{if $category->visible}checked{/if} />
						<label for="visible_checkbox">Активна</label>
					</div>
					<div class="checkbox_item">
						<input name="main" value="1" type="checkbox" id="main_checkbox"
							{if $category->main}checked{/if} />
						<label for="main_checkbox">Показыват на главной</label>
					</div>
				</div>

				<a class="out_link" target="_self" href="../{$category->url}">Открыть категорию на сайте</a>
			</div>

			<div class="name_row">
				<span class="item_id">#{$category->id|escape}</span>
				<input class="name" name="name" type="text" value="{$category->name|escape}" autocomplete="off" />
				<input name="id" type="hidden" value="{$category->id|escape}" />
			</div>
		</div>


		<div class="block_flex">
			<div class="layer">
				<h2>Позиция в дереве категорий</h2>
				<div class="select">
					<select name="parent_id" class="chosen_select">
						<option value="0">Корневая категория</option>
						{function name=category_select level=0}
							{foreach $cats as $cat}
								{if $category->id != $cat->id}
									<option value='{$cat->id}' data="{$cat->name}"
										{if $category->parent_id == $cat->id}selected{/if}>
										{section name=sp loop=$level} &nbsp; &nbsp; {/section}{$cat->name}</option>
									{category_select cats=$cat->subcategories level=$level+1}
								{/if}
							{/foreach}
						{/function}
						{category_select cats=$categories}
					</select>

					{if $category->id}
						<a class="out_link"
							href="{url view=ProductsAdmin category_id=$category->id type=print return=null}">
							Перейти к товарам категории в админке
						</a>
					{/if}
				</div>
			</div>

			<div class="layer">
				<h2>Параметры страницы</h2>
				<ul class="property_block">
					<li>
						<label class="property_name" for="url">Адрес (url)</label>
						<input name="url" id="url" type="text" value="{$category->url|escape}" />
					</li>
					<li>
						<label class="property_name" for="meta_title">Заголовок (MetaTitle)</label>
						<input name="meta_title" id="meta_title" type="text" value="{$category->meta_title|escape}" />
					</li>
					<li>
						<label class="property_name" for="h1">Заголовок (H1)</label>
						<input name="h1" id="h1" type="text" value="{$category->h1|escape}" />
					</li>
					<li>
						<label class="property_name" for="meta_description">Описание (MetaDescription)
							<div class="emojis">{$settings->emojis|escape}</div>
						</label>
						<textarea name="meta_description"
							id="meta_description">{$category->meta_description|escape}</textarea>
					</li>
				</ul>
			</div>

			<div class="layer">
				<h2>SEO параметры</h2>
				<ul class="property_block">
					<li>
						<label class="property_name" for=seo_keywords>Ключевые слова (keywords)</label>
						<textarea name="seo_keywords" id="seo_keywords">{$seo_keywords}</textarea>
					</li>
					<li>
						<label class="property_name" for=seo_faqs>Поисковые подсказки (FAQ)</label>
						<textarea name="seo_faqs" id="seo_faqs">{$seo_faqs}</textarea>
					</li>
				</ul>
			</div>
		</div>

		<div class="block_flex">


			<!-- Изображение категории -->
			<div id="images" class="layer images">
				<h2>Изображения категории</h2>
				<ul class="sortable">
					{foreach $images as $image}
						<li>
							<span class="delete">
								<img src="/{$config->templates_subdir}images/cross-circle-frame.png" />
							</span>
							<a href="{$image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="images"
								data-caption="{$category->name|escape}">
								<img src="{$image->filename|resize:220:220}" alt="" />
							</a>
							<input type="hidden" name="images[]" value="{$image->id}" />
						</li>
					{/foreach}
				</ul>
				<div class="dropZone">
					<input class="dropInput" type="file" name="dropped_images[]" multiple />
					<div class="dropMessage">Перетащите файлы сюда</div>
				</div>
			</div>


			<!-- Cинонимы категории -->
			<h3 class="mt_15">Синонимы категрии</h3>
			<ul class="features_variants sessions" id="sort">
				{foreach $synonyms as $synonym}
					<li class="one">
						<div class="move">
							<div class="move_zone"></div>
						</div>
						<input name="synonyms[]" type="text" value="{$synonym->name}" />
						<span class="delete"><i class="dash_link">Удалить</i></span>
					</li>
				{/foreach}

				<li id="new_variant" class="one" style="display:none;">
					<div class="move">
						<div class="move_zone"></div>
					</div>
					<input name=synonyms[] type="text" value="" />
					<span class="delete"><i class="dash_link">Удалить</i></span>
				</li>
			</ul>

			<span class="add"><i class="dash_link">Добавить вариант</i></span>
		</div>


		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="" value="Сохранить" />
		</div>


		<div class="block_flex w100 layer">
			<h2>Краткое описание</h2>
			<textarea name="annotation" class="html_editor editor_small">{$category->annotation|escape}</textarea>
		</div>


		<div class="block_flex w100 layer">
			<h2>Описание</h2>
			<textarea name="description" class="html_editor editor_large">{$category->description|escape}</textarea>
		</div>


		<div id="images_content" class="block_flex w100 layer images">
			<h2>Картинки описания</h2>
			<ul class="sortable">
				{foreach $images_content as $image}
					<li>
						<span class="delete">
							<img src="/{$config->templates_subdir}images/cross-circle-frame.png" />
						</span>
						<a href="{$image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="images_content"
							data-caption="{$category->name|escape}">
							<img src="{$image->filename|resize:220:220}" />
						</a>
						<input type="hidden" name="images_content[]" value="{$image->id}" />
					</li>
				{/foreach}
			</ul>

			<div class="dropZone">
				<input class="dropInput" type="file" name="dropped_images_content[]" multiple />
				<div class="dropMessage">Перетащите файлы сюда</div>
			</div>
		</div>


		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="" value="Сохранить" />
		</div>
	</div>
</form>


{* Подключаем Tiny MCE *}
{include file='parts/tinymce_init.tpl'}
{include file='parts/images_upload_init.tpl'}

<script src="/{$config->templates_subdir}js/jquery/chosen/chosen.jquery.js"></script>
<link href="/{$config->templates_subdir}js/jquery/chosen/chosen.css" rel="stylesheet" type="text/css" />


<script>
	{literal}
		$(function() {

			// Useful select
			$(".chosen_select").chosen();

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


			// Добавление синонима
			let s_variant = $('#new_variant').clone(true);
			$('#new_variant').remove().removeAttr('id');

			$('span.add').on('click', function() {
				$(s_variant).clone(true).appendTo('.features_variants').show('slow').find(
					'input[name="synonyms[]"]').focus();
				return false;
			});

			// Удаление синонима
			$(".features_variants").on('click', '.delete', function() {
				$(this).closest(".one").fadeOut(200, function() {
					$(this).remove();
				});
				return false;
			});


			$("#sort").sortable({
				items: ".one:not(.sort_disabled)",
				cancel: ".sort_disabled",
				handle: ".move_zone",
				axis: 'y',
				opacity: 0.95,
				tolerance: "pointer"
			});

			function set_meta() {
				if (!meta_title_touched)
					$('input[name="meta_title"]').val(generate_meta_title());
				if (!meta_description_touched)
					$('textarea[name="meta_description"]').val(generate_meta_description());
				if (!url_touched)
					$('input[name="url"]').val(generate_url());
			}

			function generate_meta_description() {
				return $('textarea[name=description]').val().replace(/(<([^>]+)>)/ig, " ").replace(/(\&nbsp;)/ig,
					" ").replace(
					/^\s+|\s+$/g, '').substr(0, 512);
			}
		});
	{/literal}
</script>