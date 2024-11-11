{include file='products/products_menu_part.tpl'}
{include file='products/product_submenu_part.tpl'}

{if $product->id}
	{$meta_title = $product->name scope=global}
{else}
	{$meta_title = 'Новый товар' scope=global}
{/if}

{if $message_success}
	<div class="message message_success">
		<span class="text">{if $message_success=='added'}Товар добавлен
			{elseif $message_success=='updated'}Товар
			изменен{else}{$message_success|escape}
			{/if}</span>
	</div>
{/if}

{if $message_error}
	<!-- Системное сообщение -->
	<div class="message message_error">
		<span class="text">{if $message_error=='url_exists'}Товар с таким адресом уже
			существует{elseif $message_error=='empty_name'}Введите название
			{else}{$message_error|escape}
			{/if}</span>
	</div>
{/if}

<!-- Основная форма -->
<form method=post class=form_css enctype="multipart/form-data">
	<input type=hidden name="session_id" value="{$smarty.session.id}">

	<div class="columns">

		<div class="block_flex w100 ">
			<div class="over_name">
				<div class="checkbox_line"></div>
				<div class="link_line">
					{if $product->category_id}
						<a class="out_link"
							href="{url view=ProductsAdmin category_id=$product->category_id clear=true}">Перейти к товарам
							категории в админке</a>
					{/if}
					{if $product->id}
						<a class="out_link" target="_self" href="{$settings->site_url}/product/{$product->id}">Открыть товар
							на сайте</a>
					{/if}
				</div>
			</div>
			<div class="name_row">
				<label for="name" class="item_id">#{$product->id}</label>
				<input id="name" class="name" name="name" type="text" value="{$product->name|escape}"
					autocomplete="off" />
				<input name="id" type="hidden" value="{$product->id|escape}" />
			</div>
		</div>


		<!-- Категория -->
		<div class="block_flex layer">
			<div class="select" {if !$categories}style="display:none;" {/if}>
				<label for="category_id">Категория</label>
				<select id="category_id" name="category_id" class="chosen_select">
					{function name=category_select level=0}
						{foreach $categories as $category}
							<option value="{$category->id}" {if $category->id == $selected_id}selected{/if}
								category_name="{$category->name|escape}">{section name=sp loop=$level} &nbsp; &nbsp; &nbsp;
									&nbsp;
								{/section}{$category->name|escape}</option>
							{category_select categories=$category->subcategories selected_id=$selected_id  level=$level+1}
						{/foreach}
					{/function}
					{category_select categories=$categories selected_id=$product->category_id}
				</select>
			</div>
		</div>


		<!-- Бренд -->
		<div class="block_flex layer">
			<div class="select" {if !$brands}style="display:none;" {/if}>
				<label for="brand_id">Бренд</label>
				<select id="brand_id" name="brand_id" class="chosen_select">
					<option value="" {if !$product->brand_id}selected{/if} brand_name="">Не указан</option>
					{foreach $brands as $brand}
						<option value="{$brand->id}" {if $product->brand_id == $brand->id}selected{/if}
							brand_name="{$brand->name|escape}">{$brand->name|escape}</option>
					{/foreach}
				</select>
			</div>
		</div>


		<!-- Параметры страницы -->
		<div class="block_flex layer">
			<h2>Параметры страницы (мета-теги)</h2>
			<ul class="property_block">
				<li>
					<label for="url" class="property_name">Адрес (url)</label>
					<div class="property_value">
						<div class="property_value_prefix">tovar-</div>
						<div class="property_value_suffix">
							<input id="url" name="url" type="text" value="{$product->url|escape}" />
						</div>
					</div>
				</li>
				<li>
					<label for="meta_title" class="property_name">Заголовок (MetaTitle)</label>
					<input id="meta_title" name="meta_title" type="text" value="{$product->meta_title|escape}" />
				</li>
				<li>
					<label for="meta_description" class="property_name">Описание (MetaDescription)
						<div class="emojis">{$settings->emojis|escape}</div>
					</label>
					<textarea id="meta_description"
						name="meta_description">{$product->meta_description|escape}</textarea>
				</li>
				<li class=layer>
					<label for="annotation" class="property_name">Краткое описание</label>
					<textarea id="annotation" name="annotation">{$product->annotation|escape}</textarea>
				</li>
			</ul>
		</div>


		<!-- Изображения товара -->
		<div id="images" class="block_flex layer images">
			<h2>Изображения товара
				{* <a href="#" id=images_wizard>
					<img src="/{$config->templates_subdir}images/wand.png" alt="Подобрать автоматически" title="Подобрать автоматически"/>
				</a> *}
			</h2>
			<ul>
				{foreach $product_images as $image}
					<li>
						<span class="delete">
							<img src="/{$config->templates_subdir}images/cross-circle-frame.png" />
						</span>
						<a href="{$image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="product_images"
							data-caption="{$product->name|escape}">
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
		</div>


		<!-- SEO -->
		<div class="block_flex layer">
			<h2>SEO настройки</h2>
			<ul class="property_block">
				<li>
					<label for="seo" class="property_name">Ключевые слова (SEO)</label>
					<textarea id="seo" name="seo_keywords">{$seo_keywords|join:"\n"}</textarea>
				</li>
			</ul>
		</div>


		<!-- Характеристики товара -->
		<div class="block_flex layer" {if !$categories}style="display:none;" {/if}>
			<h2>Характеристики товара
				<!--<a href="#" id=properties_wizard>
						<img src="/{$config->templates_subdir}images/wand.png" alt="Подобрать автоматически" title="Подобрать автоматически"/>
					</a>-->
			</h2>

			<ul class="property_block prop_ul">
				{foreach $features as $feature}
					{assign var=feature_id value=$feature->id}
					<li feature_id="{$feature_id}">
						{if $feature->variants}
							<label for="options[{$feature_id}]" class="property_name"><a
									href="{url view=FeatureAdmin id=$feature->id clear=true}">{$feature->name}</a></label>
							<select id="options[{$feature_id}]" name="options[{$feature_id}]">
								<option value="">-</option>
								{foreach $feature->variants as $variant}
									<option value="{$variant}" {if $variant==$options[$feature_id]->value}selected{/if}>
										{$variant}
									</option>
								{/foreach}
							</select>
						{else}
							<label for="options[{$feature_id}]" class="property_name">{$feature->name}</label>
							<input id="options[{$feature_id}]" type="text" name="options[{$feature_id}]"
								value="{$options.$feature_id->value|escape}" />
						{/if}
					</li>
				{/foreach}
			</ul>

			<!-- Новые свойства -->
			<ul class="new_features">
				<li id="new_feature">
					<label class="property_name">
						<input type="text" name="new_features_names[]">
					</label>
					<input type="text" name="new_features_values[]" />
				</li>
			</ul>

			<div class="btn_row_add">
				<span class="add">
					<i class="dash_link" id="add_new_feature">Добавить новые характеристики</i>
				</span>
			</div>
		</div>

		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="" value="Сохранить" />
		</div>


		<!-- Полное описание -->
		<div class="block_flex w100 layer">
			<h2>Полное описание</h2>
			<textarea name="body" class="html_editor editor_large">{$product->body|escape}</textarea>
		</div>


		<!-- Картинки описания -->
		<div id="images_content" class="block_flex w100 layer images">
			<h2>Картинки описания</h2>
			<ul>
				{foreach $images_content as $image}
					<li>
						<span class="delete">
							<img src="/{$config->templates_subdir}images/cross-circle-frame.png">
						</span>
						<a href="{$image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="images_content"
							data-caption="{$product->name|escape}">
							<img src="{$image->filename|resize:220:220}" />
						</a>
						<input type="hidden" name="images_content[]" value="{$image->id}" />
					</li>
				{/foreach}
			</ul>

			<div class="dropZone">
				<input type="file" name="dropped_images_content[]" multiple class="dropInput" />
				<div class="dropMessage">Перетащите файлы сюда</div>
			</div>

			<div class="add_image"></div>

			<span class="upload_image">
				<i class="dash_link" id="upload_image">Добавить изображение</i>
			</span> или
			<span class="add_image_url">
				<i class="dash_link" id="add_image_url">загрузить из интернета</i>
			</span>
		</div>

		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="" value="Сохранить" />
		</div>

	</div>
</form>

<script src="/{$config->templates_subdir}js/autocomplete/jquery.autocomplete-min.js"></script>
<script src="/{$config->templates_subdir}js/jquery/chosen/chosen.jquery.js"></script>
<link href="/{$config->templates_subdir}js/jquery/chosen/chosen.css" rel="stylesheet" type="text/css" />

{* Подключаем Tiny MCE *}
{include file='parts/tinymce_init.tpl'}
{include file='parts/images_upload_init.tpl'}

<script>
	const php_product_id = '{$product->id}';
	const php_currency_name = '{$currency->name}';
	const php_currency_sign = '{$currency->sign}';
	const php_templates_subdir = '/{$config->templates_subdir}';

	{literal}
		$(function() {

			// Useful select
			$(".chosen_select").chosen();

			// Изменение набора свойств при изменении категории
			$('select[name="category_id"]:first').change(function() {
				show_category_features($("option:selected", this).val());
			});

			function show_category_features(category_id) {
				$('ul.prop_ul').empty();
				$.ajax({
					url: "/app/agmin/ajax/get_features.php",
					data: {category_id: category_id, product_id: $("input[name=id]").val()},
					dataType: 'json',
					success: function(data) {
						for (i = 0; i < data.length; i++) {
							let feature = data[i];
							let line = $(
								"<li><label class='property_name'></label><input type='text'/></li>");
							let new_line = line.clone(true);

							new_line.find("label.property_name").text(feature.name);
							new_line.find("input").attr('name', "options[" + feature.id + "]").val(feature
								.value);
							new_line.appendTo('ul.prop_ul').find("input")
								.autocomplete({
									serviceUrl: '/app/agmin/ajax/get_options.php',
									minChars: 0,
									params: {feature_id:feature.id},
									noCache: false
								});
						}
					}
				});
				return false;
			}


			// Автодополнение свойств
			$('ul.prop_ul input[name*=options]').each(function(index) {
				let feature_id = $(this).closest('li').attr('feature_id');
				$(this).autocomplete({
					serviceUrl: '/app/agmin/ajax/get_options.php',
					minChars: 0,
					params: {feature_id:feature_id},
					noCache: false
				});
			});


			// Добавление нового свойства товара
			let new_feature = $('#new_feature').clone(true);
			$('#new_feature').remove().removeAttr('id');
			$('#add_new_feature').click(function() {
				$(new_feature).clone(true).appendTo('ul.new_features').fadeIn('slow').find(
					"input[name*=new_feature_name]").focus();
				return false;
			});


			// Волшебные изображения
			let images_num = 8;
			let images_loaded = 0;
			let old_wizar_dicon_src = $('#images_wizard img').attr('src');

			$('#images_wizard').click(function() {

				$('#images_wizard img').attr('src', php_templates_subdir + 'images/loader.gif');
				if (name_changed)
					$('div.images ul li.wizard').remove();

				name_changed = false;
				key = $('input[name=name]').val();
				$.ajax({
					url: "/app/agmin/ajax/get_images.php",
					data: {keyword: key, start: images_loaded},
					dataType: 'json',
					success: function(data) {
						for (i = 0; i < Math.min(data.length, images_num); i++) {
							image_url = data[i];
							$("<li class='wizard'><span class='delete'><img src=" +
								php_templates_subdir +
								"'images/cross-circle-frame.png'></span><a href='" +
								image_url +
								"' target=_blank><img onerror='$(this).closest(\"li\").remove();' src='" +
								image_url +
								"' /><input name=images_urls[] type=hidden value='" +
								image_url + "'></a></li>").appendTo('div .images ul');
						}
						$('#images_wizard img').attr('src', old_wizar_dicon_src);
						images_loaded += images_num;
					}
				});
				return false;
			});


			// Волшебное описание
			let captcha_code = '';
			let old_prop_wizard_icon_src = $('#properties_wizard img').attr('src');
			$('#properties_wizard').click(
				function() {

					$('#properties_wizard img').attr('src', php_templates_subdir + 'images/loader.gif');
					$('#captcha_form').remove();
					if (name_changed)
						$('div.images ul li.wizard').remove();

					name_changed = false;
					key = $('input[name=name]').val();

					$.ajax({
						url: "/app/agmin/ajax/get_info.php",
						data: {keyword: key, captcha: captcha_code},
						dataType: 'json',
						success: function(data) {

							captcha_code = '';
							$('#properties_wizard img').attr('src', old_prop_wizard_icon_src);

							// Если запрашивают капчу
							if (data.captcha) {
								captcha_form = $(
									"<form id='captcha_form'><img src='data:image/png;base64," +
									data.captcha +
									"' align='absmiddle'><input id='captcha_input' type=text><input type=submit value='Ok'></form>"
								);
								$("#properties_wizard").parent().append(captcha_form);
								$('#captcha_input').focus();
								captcha_form.submit(function() {
									captcha_code = $('#captcha_input').val();
									$(this).remove();
									$('#properties_wizard').click();
									return false;
								});
							} else if (data.product) {
								$('li#new_feature').remove();
								for (i = 0; i < data.product.options.length; i++) {
									option_name = data.product.options[i].name;
									option_value = data.product.options[i].value;

									// Добавление нового свойства товара
									exists = false;

									if (!$('label.property:visible').filter(function() {
											return $(this)
												.text().toLowerCase() === option_name
												.toLowerCase();
										}).closest('li').find('input[name*=options]').val(option_value)
										.length) {
										f = $(new_feature).clone(true);
										f.find('input[name*=new_features_names]').val(option_name);
										f.find('input[name*=new_features_values]').val(option_value);
										f.appendTo('ul.new_features').fadeIn('slow').find(
											"input[name*=new_feature_name]");
									}
								}
							}
						},
						error: function(xhr, textStatus, errorThrown) {
							alert("Error: " + textStatus);
						}
					});
					return false;
				});


			// Автозаполнение мета-тегов
			let meta_title_touched = true;
			let url_touched = true;
			let meta_description_touched = true;
			let name_changed = false;

			$("input[name=name]").change(function() {
				name_changed = true;
			});

			if ($('input[name="meta_title"]').val() == generate_meta_title() || $('input[name="meta_title"]').val() ==
				'')
				meta_title_touched = false;

			if ($('textarea[name="meta_description"]').val() == generate_meta_description() || $(
					'textarea[name="meta_description"]').val() == '')
				meta_description_touched = false;

			if ($('input[name="url"]').val() == '')
				url_touched = false;

			$('input[name="meta_title"]').change(function() { meta_title_touched = true; });
			$(
				'textarea[name="meta_description"]').change(function() { meta_description_touched = true; });
			$(
				'input[name="url"]').change(function() { url_touched = true; });
			$('input[name="name"]').keyup(
				function() { set_meta(); });


			function set_meta() {
				if (!meta_title_touched)
					$('input[name="meta_title"]').val(generate_meta_title());

				if (!url_touched)
					$('input[name="url"]').val(generate_url());
			}



			function generate_meta_description() {
				return $('textarea[name=annotation]').val().replace(/(<([^>]+)>)/ig, " ").replace(/(\&nbsp;)/ig,
					" ").replace(
					/^\s+|\s+$/g, '').substr(0, 512);
			}
		});
	{/literal}
</script>