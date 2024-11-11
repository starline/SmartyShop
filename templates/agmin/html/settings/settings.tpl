{include file='settings/settings_menu_part.tpl'}

{$meta_title = "Настройки сайта" scope=global}

{if $message_success}
	<!-- Системное сообщение -->
	<div class="message message_success">
		<span class="text">{if $message_success == 'saved'}Настройки сохранены{/if}</span>
	</div>
{elseif $message_error}
	<!-- Системное сообщение -->
	<div class="message message_error">
		<span class="text">{if $message_error == 'watermark_is_not_writable'}Установите права на запись для файла
			{$config->images_watermark_file}{/if}</span>
	</div>
{/if}

<!-- Основная форма -->
<form method="post" class="form_css" enctype="multipart/form-data">
	<input type="hidden" name="session_id" value="{$smarty.session.id}">

	<div class="columns">


		<div class="block_flex layer">
			<h2>Настройки сайта</h2>
			<ul class="property_block">
				<li>
					<label class="property_name" for=site_name>URL сайта</label>
					<input name="site_name" id=site_name type="text" value="{$settings->site_name|escape}" />
				</li>
				<li>
					<label class="property_name" for=company_name>Имя компании</label>
					<input name="company_name" id=company_name type="text" value="{$settings->company_name|escape}" />
				</li>
				<li>
					<label class="property_name" for=company_description>Описание компании</label>
					<input name="company_description" id=company_description type="text"
						value="{$settings->company_description|escape}" />
				</li>
				<li>
					<label class="property_name" for=date_format>Формат даты</label>
					<input name="date_format" id=date_format type="text" value="{$settings->date_format|escape}" />
				</li>
			</ul>
		</div>


		<div class="block_flex layer">
			<h2>Формат цены</h2>
			<ul class="property_block">
				<li>
					<label class="property_name" for=decimals_point>Разделитель копеек</label>
					<select name="decimals_point" id=decimals_point>
						<option value='.' {if $settings->decimals_point == '.'}selected{/if}>
							точка: 12.45 {$currency->sign}
						</option>
						<option value=',' {if $settings->decimals_point == ','}selected{/if}>
							запятая: 12,45 {$currency->sign}
						</option>
					</select>
				</li>
				<li>
					<label class="property_name" for=thousands_separator>Разделитель тысяч</label>
					<select name="thousands_separator" id=thousands_separator>
						<option value='' {if $settings->thousands_separator == ''}selected{/if}>
							без разделителя: 1245678 {$currency->sign}
						</option>
						<option value=' ' {if $settings->thousands_separator == ' '}selected{/if}>
							пробел: 1 245 678 {$currency->sign}
						</option>
						<option value=',' {if $settings->thousands_separator == ','}selected{/if}>
							запятая: 1,245,678 {$currency->sign}
						</option>
					</select>
				</li>
			</ul>
		</div>


		<div class="block_flex layer">
			<h2>Настройки каталога</h2>
			<ul class="property_block">
				<li>
					<label class="property_name" for=products_num>Товаров на странице сайта</label>
					<input class=small_inp name="products_num" id=products_num type="text"
						value="{$settings->products_num|escape}" />
				</li>
				<li>
					<label class="property_name" for=products_num_admin>Товаров на странице админки</label>
					<input class=small_inp name="products_num_admin" id=products_num_admin type="text"
						value="{$settings->products_num_admin|escape}" />
				</li>
				<li>
					<label class="property_name" for=units>Единицы измерения товаров</label>
					<input class=small_inp name="units" id=units type="text" value="{$settings->units|escape}" />
				</li>
				<li>
					<label class="property_name" for=weight_units>Единицы измерения веса</label>
					<input class=small_inp name="weight_units" id=weight_units type="text"
						value="{$settings->weight_units|escape}" />
				</li>
				<li>
					<label class="property_name" for=rel_products_num>Рекомендуемых товаров</label>
					<input class=small_inp name="rel_products_num" id=rel_products_num type="text"
						value="{$settings->rel_products_num|escape}" />
				</li>
			</ul>
		</div>


		<div class="block_flex layer">
			<h2>Конфигурация водяного знака</h2>
			<div class="image_item">
				<label class="property_name" for="watermark_file">Водяной знак</label>
				<input name="watermark_file" id="watermark_file" type="file">
				<img src="{$config->root_url}/{$config->images_watermark_file}?{math equation='rand(10,10000)'}" />
			</div>

			<div>
				<ul class="property_block">
					<li>
						<label for="watermark_offset_x" class="property_name">Горизонтальное положение водяного
							знака</label>
						<div class="whith_unit">
							<input name="watermark_offset_x" id="watermark_offset_x" class="small_inp" type="text"
								value="{$settings->watermark_offset_x|escape}" />
							<span class="label_unit">%</span>
						</div>
					</li>
					<li>
						<label for=watermark_offset_y class="property_name">Вертикальное положение водяного
							знака</label>
						<div class=whith_unit>
							<input name="watermark_offset_y" class="small_inp" id=watermark_offset_y type="text"
								value="{$settings->watermark_offset_y|escape}" />
							<span class="label_unit">%</span>
						</div>
					</li>
					<li>
						<label for=watermark_transparency class="property_name">Непрозрачность знака (меньше &mdash;
							прозрачней)</label>
						<div class=whith_unit>
							<input name=watermark_transparency class="small_inp" id=watermark_transparency type=text
								value="{$settings->watermark_transparency|escape}" />
							<span class="label_unit">%</span>
						</div>
					</li>

					{if ($imagick)}
						<li>
							<label for=images_sharpen class="property_name">Резкость изображений (рекомендуется 20%)</label>
							<div class=whith_unit>
								<input name=images_sharpen class="small_inp" id=images_sharpen type=text
									value="{$settings->images_sharpen|escape}" />
								<span class="label_unit">%</span>
							</div>
						</li>
					{/if}
				</ul>
			</div>
		</div>


		<div class="block_flex layer">
			<h2>SEO настройки</h2>
			<ul class="property_block">
				<li>
					<label for="meta_description" class="property_name">Товары (MetaDescription)</label>
					<textarea id="meta_description"
						name="product_meta_description">{$settings->product_meta_description|escape}</textarea>
				</li>
				<li>
					<label for="emojis" class="property_name">Значки (Emojis)</label>
					<input id="emojis" name="emojis" type="text" value="{$settings->emojis|escape}" />
				</li>
			</ul>
		</div>


		<div class="block_flex layer">
			<h2>Настройки заказов</h2>
			<ul class="property_block">
				<li>
					<label class="property_name" for="max_order_amount">Максимум товаров в заказе</label>
					<input class="small_inp" name="max_order_amount" id="max_order_amount" type="text"
						value="{$settings->max_order_amount|escape}" />
				</li>
				<li>
					<label class="property_name" for="income_finance_category_id">Категория доходов</label>
					<select name="income_finance_category_id" id="income_finance_category_id">
						<option value="">Не выбрана</option>
						{foreach $income_finance_categories as $cat}
							<option value="{$cat->id}" {if $settings->income_finance_category_id == $cat->id}selected{/if}>
								{$cat->name}
							</option>
						{/foreach}
					</select>
				</li>
				<li>
					<label class="property_name" for="expense_finance_category_id">Категория расходов</label>
					<select name="expense_finance_category_id" id="expense_finance_category_id">
						<option value="">Не выбрана</option>
						{foreach $expense_finance_categories as $cat}
							<option value="{$cat->id}" {if $settings->expense_finance_category_id == $cat->id}selected{/if}>
								{$cat->name}
							</option>
						{/foreach}
					</select>
				</li>
			</ul>
		</div>


		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="save" value="Сохранить" />
		</div>
	</div>

</form>