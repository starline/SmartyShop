{include file='products/products_settings_menu_part.tpl'}

{if $brand->id}
	{$meta_title = $brand->name scope=global}
{else}
	{$meta_title = 'Новый бренд' scope=global}
{/if}

{if $message_success}
	<!-- Системное сообщение -->
	<div class="message message_success">
		<span class="text">{if $message_success=='added'}Бренд добавлен
			{elseif $message_success=='updated'}Бренд
			обновлен{else}{$message_success}
			{/if}</span>
	</div>
{/if}


{if $message_error}
	<!-- Системное сообщение -->
	<div class="message message_error">
		<span class="text">{if $message_error=='url_exists'}Бренд с таким адресом уже
			существует{else}{$message_error}
			{/if}</span>
	</div>
{/if}


<!-- Основная форма -->
<form method=post class=form_css enctype="multipart/form-data">
	<input type=hidden name="session_id" value="{$smarty.session.id}">

	<div class="columns">
		<div class="block_flex w100">
			<div class="over_name">
				<div class="checkbox_line">
					<div class="checkbox_item">
						<input name="featured" value="1" type="checkbox" id="featured_checkbox"
							{if $brand->featured}checked{/if} />
						<label for="featured_checkbox">Избранный</label>
					</div>
				</div>
			</div>
			<div class="name_row">
				<input class="name" name="name" type="text" value="{$brand->name|escape}" />
				<input name="id" type="hidden" value="{$brand->id|escape}" />
			</div>
		</div>

		<div class="block_flex layer">
			<h2>Параметры страницы</h2>
			<ul class="property_block">
				<li>
					<label for="url" class="property_name">URL</label>
					<input id="url" name="url" type="text" value="{$brand->url|escape}" />
				</li>
				<li>
					<label for="meta_title" class="property_name">Title</label>
					<input id="meta_title" name="meta_title" type="text" value="{$brand->meta_title|escape}" />
				</li>
				<li>
					<label for="property_name" class="property_name">Описание (MetaDescription)
						<div class="emojis">{$settings->emojis|escape}</div>
					</label>
					<textarea id="property_name" name="meta_description">{$brand->meta_description|escape}</textarea>
				</li>
			</ul>
			<div class="btn_row">
				<input class="button_green" type="submit" name="" value="Сохранить" />
			</div>
		</div>


		<!-- Изображения -->
		<div class="block_flex layer images">
			<h2>Изображение бренда</h2>
			<input class="upload_image" name="image" type="file">
			<input type="hidden" name="delete_image" value="">
			{if !$brand->image|empty}
				<ul>
					<li>
						<span class="delete">
							<img src="/{$config->templates_subdir}images/cross-circle-frame.png" />
						</span>
						<a href="../{$config->images_brands_dir}{$brand->image}" class="zoom" data-fancybox="product_images"
							data-caption="{$product->name|escape}">
							<img src="../{$config->images_brands_dir}{$brand->image}" />
						</a>
					</li>
				</ul>
			{/if}
		</div>

		<div class="block_flex w100 layer">
			<h2>Описание</h2>
			<textarea name="description" class="html_editor editor_large">{$brand->description|escape}</textarea>

			<div class="btn_row">
				<input class="button_green" type="submit" name="" value="Сохранить" />
			</div>
		</div>

	</div>
</form>


{* Подключаем Tiny MCE *}
{include file='parts/tinymce_init.tpl'}


<script>
	{literal}

		//On document load 
		$(function() {

			// Удаление изображений
			$(".images span.delete").on('click', function() {
				$("input[name='delete_image']").val('1');
				$(this).closest("ul").fadeOut(200, function() { $(this).remove(); });
				return false;
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
			$('input[textarea="meta_description"]').change(function() { meta_description_touched = true; });
			$('input[name="url"]').change(function() { url_touched = true; });

			$('input[name="name"]').keyup(function() { set_meta(); });

			function set_meta() {
				if (!meta_title_touched)
					$('input[name="meta_title"]').val(generate_meta_title());
				if (!meta_description_touched)
					$('textarea[name="meta_description"]').val(generate_meta_description());
				if (!url_touched)
					$('input[name="url"]').val(generate_url());
			}

			function generate_meta_description() {
				name = $('input[name="name"]').val();
				return name;
			}
		});
	{/literal}
</script>